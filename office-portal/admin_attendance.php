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

    if($userdetails['role']!="admin"){ 
        header("location:dashboard.php"); 
        exit(); 
    } 
} 
else{ 
    header("location:login.php"); 
    exit(); 
} 

if(isset($_GET['date'])){ 
    $selected_date=$_GET['date']; 
} 
else{ 
    $selected_date=date("Y-m-d"); 
} 

$attendance="SELECT users.name,attendance.check_in,attendance.check_out,attendance.status
FROM `users`
LEFT JOIN `attendance` 
ON users.id=attendance.user_id AND attendance.work_date=?
WHERE users.role='employee'
ORDER BY users.name"; 

$attendance_query=$connection->prepare($attendance); 
$attendance_query->bind_param("s",$selected_date); 
$attendance_query->execute(); 
$attendance_result=$attendance_query->get_result(); 

?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Attendance Records</title> 
    <link rel="stylesheet" href="style.css"> 
</head> 
<body> 
    <div class="scene"> 
        <div class="admin-shell"> 

            <aside class="admin-sidebar"> 
                <div class="sidebar-brand"> 
                    <span class="dash-logo-mark"></span> 
                    <div> 
                        <div class="brand-title">Office Portal</div> 
                        <div class="brand-sub">Admin Panel</div> 
                    </div> 
                </div> 
                <nav class="sidebar-nav"> 
                    <a href="admin_dashboard.php" class="nav-item"><span class="nav-icon">🏠</span> Dashboard</a> 
                    <a href="admin_users.php" class="nav-item"><span class="nav-icon">👥</span> Users</a> 
                    <a href="admin_departments.php" class="nav-item"><span class="nav-icon">🏢</span> Departments</a> 
                    <a href="admin_attendance.php" class="nav-item active"><span class="nav-icon">🕒</span> Attendance</a> 
                    <a href="admin_leaves.php" class="nav-item"><span class="nav-icon">🗓️</span> Leaves</a> 
                    <a href="admin_tasks.php" class="nav-item"><span class="nav-icon">✅</span> Tasks</a> 
                    <a href="admin_announcements.php" class="nav-item"><span class="nav-icon">📢</span> Announcements</a> 
                    <a href="admin_documents.php" class="nav-item"><span class="nav-icon">📁</span> Documents</a> 
                    <a href="admin_events.php" class="nav-item"><span class="nav-icon">📅</span> Events</a> 
                    <a href="admin_settings.php" class="nav-item"><span class="nav-icon">⚙️</span> Settings</a> 
                    <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a> 
                </nav> 
            </aside> 

            <main class="admin-main"> 
                <header class="admin-topbar"> 
                    <h1 class="topbar-title">Attendance</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 

                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <div class="table-header"> 
                        <h2 class="panel-title">Daily Attendance</h2> 

                        <form method="GET" style="display:flex; gap:.6rem; align-items:center;"> 
                            <input type="date" name="date" value="<?php echo $selected_date; ?>" style="background:var(--ink-3); border:1px solid rgba(255,255,255,.08); border-radius:8px; padding:.55rem .8rem; color:var(--paper); font-family:'Inter',sans-serif;"> 
                            <button type="submit" class="btn-secondary" style="padding:.55rem .9rem; font-size:.82rem;">Filter</button> 
                        </form> 
                    </div> 

                    <div class="table-scroll"> 
                        <table class="users-table"> 
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead> 

                            <tbody> 

                                <?php while($row=$attendance_result->fetch_assoc()){ ?> 

                                    <tr> 

                                        <td><?php echo htmlspecialchars($row['name']); ?></td> 

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

                                            <?php } else{ ?> 

                                                <span class="status-badge status-rejected">Absent</span> 

                                            <?php } ?> 

                                        </td> 

                                    </tr> 

                                <?php } ?> 

                            </tbody> 
                        </table> 
                    </div> 
                </div> 
            </main> 
        </div> 
    </div> 
    <script src="theme.js"></script>
</body> 
</html>