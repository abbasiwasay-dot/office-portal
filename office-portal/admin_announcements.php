<?php
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])){
    header("location:login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

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


if(isset($_POST['submit'])){
    $title   = trim($_POST['title'] ?? '');
    $message = trim($_POST['content'] ?? '');

    if($title === '' || $message === ''){
    
}
else{
        $insertion="INSERT INTO `announcements` (`title`, `content`, `posted_by`)
         VALUES (?,?,?)";
        $prep=$connection->prepare($insertion);
        $prep->bind_param("ssi",$title,$message,$user_id);
        $prep->execute();
          header("Location: admin_announcements.php");
        exit;
}
    }
$select="SELECT * FROM announcements ORDER BY created_at DESC";
$query=$connection->prepare($select);
$query->execute();
$result=$query->get_result();


if(isset($_GET['delete'])){
    $id = $_GET['delete'];

    $delete = "DELETE FROM announcements WHERE id=?";
    $query = $connection->prepare($delete);
    $query->bind_param("i", $id);
    $query->execute();

    header("Location: admin_announcements.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Announcements</title>
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
                    <a href="admin_announcements.php" class="nav-item active"><span class="nav-icon">📢</span> Announcements</a>
                    <a href="admin_documents.php" class="nav-item"><span class="nav-icon">📁</span> Documents</a>
                    <a href="admin_events.php" class="nav-item"><span class="nav-icon">📅</span> Events</a>
                    <a href="admin_settings.php" class="nav-item"><span class="nav-icon">⚙️</span> Settings</a>
                    <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a>
                </nav>
            </aside>

            <main class="admin-main">
                <header class="admin-topbar">
                    <h1 class="topbar-title">Announcements</h1>
                    <div class="topbar-right">
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div>
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a>
                    </div>
                </header>

                <div class="panel">
                    <div class="panel-notch"></div>
                    <h2 class="panel-title">Post Announcement</h2>
                    <form class="form-panel" style="margin-top:0;" method="POST" action="admin_announcements.php">
                        <div class="field">
                            <label>Title</label>
                            <input type="text" placeholder="Announcement title" name="title">
                        </div>
                        <div class="field">
                            <label>Content</label>
                            <textarea placeholder="Write your announcement..." name="content"></textarea>
                        </div>
                        <button type="submit" class="btn-primary" name="submit">Post Announcement</button>
                    </form>
                </div>
<div class="panel">
    <div class="panel-notch"></div>
    <h2 class="panel-title">All Announcements</h2>

    <div class="item-list">

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="list-item">
                <div class="list-item-header">
                    <h3 class="list-item-title">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </h3>

                    <span class="list-item-meta">  <?php echo date('Y-m-d', strtotime($row['created_at'])); ?></span>
                </div>

                <p class="list-item-body">
                    <?php echo htmlspecialchars($row['content']); ?>
                </p>

                <div class="list-item-actions">
                    <a href="admin_announcements.php?delete=<?php echo $row['id']; ?>" class="table-action danger">Delete</a>
                </div>
            </div>

        <?php endwhile; ?>

    </div>
</div>

            </main>
        </div>
    </div>
    <script src="theme.js"></script>
</body>
</html>
