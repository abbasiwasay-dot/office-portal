<?php 
session_start(); 
require "db.php"; 
 
if(isset($_SESSION['user_id'])){ 
    $user_id=$_SESSION['user_id']; 
 
    $data="SELECT * FROM `users` WHERE id=?"; 
    $details=$connection->prepare($data); 
    $details->bind_param("i",$user_id); 
    $details->execute(); 
    $getresult=$details->get_result(); 
    $userdetails=$getresult->fetch_assoc(); 
 
    if($userdetails['role']!="employee"){ 
        header("location:admin_dashboard.php"); 
        exit(); 
    } 
} 
else{ 
    header("location:login.php"); 
    exit(); 
} 
 
if($_SERVER['REQUEST_METHOD']=="POST"){ 
    $action=$_POST['action']; 
    $today=date("Y-m-d"); 
    $current_time=date("H:i:s"); 
 
    if($action=="check_in"){ 
        $setting="SELECT * FROM `settings` WHERE id=1"; 
        $setting_query=$connection->prepare($setting); 
        $setting_query->execute(); 
        $setting_result=$setting_query->get_result(); 
        $settings=$setting_result->fetch_assoc(); 
 
        $office_start_time=$settings['office_start_time']; 
 
        if($current_time>$office_start_time){ 
            $status="late"; 
        } 
        else{ 
            $status="present"; 
        } 
 
        $insert="INSERT INTO `attendance` 
        (`user_id`,`work_date`,`status`,`check_in`,`check_out`) 
        VALUES (?,?,?,?,NULL)"; 
 
        $insert_query=$connection->prepare($insert); 
        $insert_query->bind_param("isss",$user_id,$today,$status,$current_time); 
        $insert_query->execute(); 
    } 
 
    if($action=="check_out"){ 
        $update="UPDATE `attendance` 
        SET check_out=? 
        WHERE user_id=? AND work_date=?"; 
 
        $update_query=$connection->prepare($update); 
        $update_query->bind_param("sis",$current_time,$user_id,$today); 
        $update_query->execute(); 
    } 
 
    header("location:attendance.php"); 
    exit(); 
} 
 
$today=date("Y-m-d"); 
 
$today_attendance="SELECT * FROM `attendance` 
WHERE user_id=? AND work_date=?"; 
 
$today_query=$connection->prepare($today_attendance); 
$today_query->bind_param("is",$user_id,$today); 
$today_query->execute(); 
$today_result=$today_query->get_result(); 
$today_data=$today_result->fetch_assoc(); 
 
$history="SELECT * FROM `attendance` 
WHERE user_id=? 
ORDER BY work_date DESC"; 
 
$history_query=$connection->prepare($history); 
$history_query->bind_param("i",$user_id); 
$history_query->execute(); 
$history_result=$history_query->get_result(); 
 
$display_date=date("l, d M Y"); 
 
if($today_data){ 
    $check_in=$today_data['check_in']; 
    $check_out=$today_data['check_out']; 
} 
else{ 
    $check_in=null; 
    $check_out=null; 
} 
 
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Attendance</title> 
    <link rel="stylesheet" href="style.css"> 
</head> 
<body> 
    <div class="scene"> 
        <div class="page-shell"> 
 
            <div class="dash-header"> 
                <div class="dash-logo"><span class="dash-logo-mark"></span> Office Portal</div> 
                <div class="dash-header-welcome">Welcome, <span><?php echo htmlspecialchars($userdetails['name']); ?></span></div> 
                <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Log out</a> 
            </div> 
 
            <div class="emp-nav"> 
                <a href="dashboard.php" class="emp-nav-item">🏠 Dashboard</a> 
                <a href="attendance.php" class="emp-nav-item active">🕒 Attendance</a> 
                <a href="leaves.php" class="emp-nav-item">🗓️ Leaves</a> 
                <a href="tasks.php" class="emp-nav-item">✅ Tasks</a> 
                <a href="announcements.php" class="emp-nav-item">📢 Announcements</a> 
                <a href="documents.php" class="emp-nav-item">📁 Documents</a> 
                <a href="events.php" class="emp-nav-item">📅 Events</a> 
            </div> 
 
            <div class="panel"> 
                <div class="panel-notch"></div> 
                <h2 class="panel-title">Today — <?php echo $display_date; ?></h2> 
 
                <div class="clock-widget"> 
 
                    <div> 
                        <div class="clock-time"> 
                            <?php 
                            if($check_in){ 
                                echo date("h:i A",strtotime($check_in)); 
                            } 
                            else{ 
                                echo "--:--"; 
                            } 
                            ?> 
                        </div> 
                        <span class="stat-label">Check In</span> 
                    </div> 
 
                    <div> 
                        <div class="clock-time"> 
                            <?php 
                            if($check_out){ 
                                echo date("h:i A",strtotime($check_out)); 
                            } 
                            else{ 
                                echo "--:--"; 
                            } 
                            ?> 
                        </div> 
                        <span class="stat-label">Check Out</span> 
                    </div> 
 
                    <?php if(!$today_data){ ?> 
 
                        <form method="POST"> 
                            <input type="hidden" name="action" value="check_in"> 
                            <button type="submit" class="btn-primary">Check In</button> 
                        </form> 
 
                    <?php } elseif(!$check_out){ ?> 
 
                        <form method="POST"> 
                            <input type="hidden" name="action" value="check_out"> 
                            <button type="submit" class="btn-primary">Check Out</button> 
                        </form> 
 
                    <?php } ?> 
 
                </div> 
            </div> 
 
            <div class="panel"> 
                <div class="panel-notch"></div> 
                <h2 class="panel-title">Attendance History</h2> 
 
                <div class="table-scroll"> 
                    <table class="users-table"> 
                        <thead> 
                            <tr> 
                                <th>Date</th> 
                                <th>Check In</th> 
                                <th>Check Out</th> 
                                <th>Status</th> 
                            </tr> 
                        </thead> 
 
                        <tbody> 
 
                            <?php while($row=$history_result->fetch_assoc()){ ?> 
 
                                <tr> 
 
                                    <td> 
                                        <?php echo date("d M Y",strtotime($row['work_date'])); ?> 
                                    </td> 
 
                                    <td> 
                                        <?php 
                                        if($row['check_in']){ 
                                            echo date("h:i A",strtotime($row['check_in'])); 
                                        } 
                                        else{ 
                                            echo "—"; 
                                        } 
                                        ?> 
                                    </td> 
 
                                    <td> 
                                        <?php 
                                        if($row['check_out']){ 
                                            echo date("h:i A",strtotime($row['check_out'])); 
                                        } 
                                        else{ 
                                            echo "—"; 
                                        } 
                                        ?> 
                                    </td> 
 
                                    <td> 
                                        <?php if($row['status']=="present"){ ?> 
 
                                            <span class="status-badge status-present">Present</span> 
 
                                        <?php } elseif($row['status']=="late"){ ?> 
 
                                            <span class="status-badge status-late">Late</span> 
 
                                        <?php } elseif($row['status']=="half_day"){ ?> 
 
                                            <span class="status-badge status-half_day">Half Day</span> 
 
                                        <?php } ?> 
                                    </td> 
 
                                </tr> 
 
                            <?php } ?> 
 
                        </tbody> 
                    </table> 
                </div> 
            </div> 
 
        </div> 
    </div> 
    <script src="theme.js"></script>
</body> 
</html>