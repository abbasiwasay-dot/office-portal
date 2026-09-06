<?php 
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])){
    header("location:login.php");
    exit();
}
$current_user_id=$_SESSION['user_id'];

$data="SELECT * FROM `users` WHERE id=?";
$details=$connection->prepare($data);
$details->bind_param("i",$current_user_id);
$details->execute();
$getresult=$details->get_result();
$userdetails=$getresult->fetch_assoc();

if($userdetails['role']!="admin"){
    header("location:dashboard.php");
    exit();
}

if(!isset($_GET['id']) || $_GET['id']===""){
    header("location:admin_departments.php");
    exit();
}
$department_id=$_GET['id'];

$select="SELECT * FROM `departments` WHERE id=?";
$query=$connection->prepare($select);
$query->bind_param("i",$department_id);
$query->execute();
$result=$query->get_result();

$department=$result->fetch_assoc();

if(!$department){
    header("location:admin_departments.php");
    exit();
}

$selectmembers="SELECT * FROM `users` WHERE department_id=?";
$querymembers=$connection->prepare($selectmembers);
$querymembers->bind_param("i",$department_id);
$querymembers->execute();
$members=$querymembers->get_result();
?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Department Members</title> 
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
                    <a href="admin_departments.php" class="nav-item active"><span class="nav-icon">🏢</span> Departments</a> 
                    <a href="admin_attendance.php" class="nav-item"><span class="nav-icon">🕒</span> Attendance</a> 
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
                    <h1 class="topbar-title"><?php echo htmlspecialchars($department['name']); ?> Members</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title"><?php echo htmlspecialchars($department['name']); ?></h2> 
                    <div class="table-scroll"> 
                        <table class="users-table"> 
                            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr></thead> 
                            <tbody> 
                            <?php if($members->num_rows === 0){ ?>
                                <tr><td colspan="4" style="text-align:center; color:var(--violet);">No members in this department yet.</td></tr>
                            <?php } else { while($row=$members->fetch_assoc()){ ?>
                                <tr>
                                    <td><?php echo (int)$row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                </tr>
                            <?php } } ?>
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