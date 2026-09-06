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

if(isset($_POST['approve'])){
    $approved=$_POST['approve'];
    $status="approved";
    $update="UPDATE `leaves` SET status=?,reviewed_by=? WHERE id=?";
    $prep=$connection->prepare($update);
    $prep->bind_param("sii",$status,$current_user_id,$approved);
    $leave_approved=$prep->execute();
    }
    else if(isset($_POST['reject'])){
        $rejected=$_POST['reject'];
        $status="rejected";
    $update="UPDATE `leaves` SET status=?,reviewed_by=? WHERE id=?";
$prep=$connection->prepare($update);
$prep->bind_param("sii",$status,$current_user_id,$rejected);
$leave_reject=$prep->execute();
}


$fetchleaves="SELECT leaves.*,users.name FROM `leaves` JOIN users ON leaves.user_id=users.id WHERE status='pending'";
$prepfetch = $connection->prepare($fetchleaves);
$prepfetch->execute();
$leaverecords = $prepfetch->get_result();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Leave Requests</title>
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
                    <a href="admin_leaves.php" class="nav-item active"><span class="nav-icon">🗓️</span> Leaves</a>
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
                    <h1 class="topbar-title">Leave Requests</h1>
                    <div class="topbar-right">
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div>
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a>
                    </div>
                </header>

                <div class="panel">
                    <div class="panel-notch"></div>
                    <h2 class="panel-title">All Requests</h2>
                    <div class="item-list">

                        <?php while($row=$leaverecords->fetch_assoc()){?>
                        <div class="list-item">
                            <div class="list-item-header">
                                <h3 class="list-item-title"><?php echo htmlspecialchars($row['name'])?> — <?php echo htmlspecialchars($row['leave_type'])?> Leave</h3>
                                <span class="status-badge status-pending"><?php echo htmlspecialchars($row['status'])?></span>
                            </div>
                            <p class="list-item-meta" style="display:block;"><?php echo htmlspecialchars($row['start_date'])?> – <?php echo htmlspecialchars($row['end_date'])?></p>
                            <p class="list-item-body" style="margin-top:.4rem;"><?php echo htmlspecialchars($row['reason'])?></p>
                            <form action="" method="POST">
                            <div class="list-item-actions">
                                <button type="submit" class="btn-secondary" style="padding:.5rem .9rem; font-size:.8rem; border-color:rgba(127,191,142,.4); color:var(--success);" name="approve" value="<?php echo $row['id']; ?>">Approve</button>
                                <button type="submit" class="btn-secondary" style="padding:.5rem .9rem; font-size:.8rem; border-color:rgba(214,107,99,.4); color:var(--rose);" name="reject"  value="<?php echo $row['id']; ?>">Reject</button>
                            </div>
                            </form>
                        </div>

                      <?php }?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="theme.js"></script>
</body>
</html>
