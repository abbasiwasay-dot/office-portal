<?php 
session_start();
require "db.php"; 

if(!isset($_SESSION['user_id'])){
    header("location:login.php");
    exit();
}
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

if($_SERVER['REQUEST_METHOD']=='POST'){ 

    $company_name=$_POST['company_name'];
    $office_start_time=$_POST['office_start_time'];
    $office_end_time=$_POST['office_end_time'];

    $check="SELECT * FROM `settings` WHERE id=1";
    $check_query=$connection->prepare($check);
    $check_query->execute();
    $check_result=$check_query->get_result();

    if($check_result->num_rows>0){

        $update="UPDATE `settings` SET company_name=?,office_start_time=?,office_end_time=? WHERE id=1";
        $update_query=$connection->prepare($update);
        $update_query->bind_param("sss",$company_name,$office_start_time,$office_end_time);
        $update_query->execute();

    }
    else{

        $insert="INSERT INTO `settings` (id,company_name,office_start_time,office_end_time) VALUES (1,?,?,?)";
        $insert_query=$connection->prepare($insert);
        $insert_query->bind_param("sss",$company_name,$office_start_time,$office_end_time);
        $insert_query->execute();

    }

    header("location:admin_settings.php");
    exit();
}


$setting="SELECT * FROM `settings` WHERE id=1";
$setting_query=$connection->prepare($setting);
$setting_query->execute();
$setting_result=$setting_query->get_result();
$settings=$setting_result->fetch_assoc();

if($settings){

    $company_name=$settings['company_name'];
    $office_start_time=$settings['office_start_time'];
    $office_end_time=$settings['office_end_time'];

}
else{

    $company_name="My Company";
    $office_start_time="09:00";
    $office_end_time="18:00";

}

?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Settings</title> 
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
                    <a href="admin_attendance.php" class="nav-item"><span class="nav-icon">🕒</span> Attendance</a> 
                    <a href="admin_leaves.php" class="nav-item"><span class="nav-icon">🗓️</span> Leaves</a> 
                    <a href="admin_tasks.php" class="nav-item"><span class="nav-icon">✅</span> Tasks</a> 
                    <a href="admin_announcements.php" class="nav-item"><span class="nav-icon">📢</span> Announcements</a> 
                    <a href="admin_documents.php" class="nav-item"><span class="nav-icon">📁</span> Documents</a> 
                    <a href="admin_events.php" class="nav-item"><span class="nav-icon">📅</span> Events</a> 
                    <a href="admin_settings.php" class="nav-item active"><span class="nav-icon">⚙️</span> Settings</a> 
                    <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a> 
                </nav> 
            </aside> 
 
            <main class="admin-main"> 
                <header class="admin-topbar"> 
                    <h1 class="topbar-title">Settings</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title">Company Settings</h2> 

                    <form class="form-panel" style="margin-top:0;" method="POST"> 

                        <div class="field"> 
                            <label>Company Name</label> 
                            <input type="text" value="<?php echo $company_name; ?>" name="company_name"> 
                        </div> 

                        <div class="form-row"> 

                            <div class="field"> 
                                <label>Office Start Time</label> 
                                <input type="time" value="<?php echo $office_start_time; ?>" name="office_start_time"> 
                            </div> 

                            <div class="field"> 
                                <label>Office End Time</label> 
                                <input type="time" value="<?php echo $office_end_time; ?>" name="office_end_time"> 
                            </div> 

                        </div> 

                        <button type="submit" class="btn-primary">Save Settings</button> 

                    </form> 
                </div> 
            </main> 
        </div> 
    </div> 
    <script src="theme.js"></script>
</body> 
</html>