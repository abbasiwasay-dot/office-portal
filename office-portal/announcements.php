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

if($userdetails['role']!="employee"){
    header("location:admin_dashboard.php");
    exit();
}

$select = "SELECT announcements.*, users.name AS poster_name
           FROM announcements
           JOIN users ON announcements.posted_by = users.id
           ORDER BY announcements.created_at DESC";

$query = $connection->prepare($select);
$query->execute();
$result = $query->get_result();
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

    <div class="page-shell">

        <div class="dash-header">
            <div class="dash-logo">
                <span class="dash-logo-mark"></span> Office Portal
            </div>

            <div class="dash-header-welcome">
                Welcome, <span><?php echo htmlspecialchars($userdetails['name']); ?></span>
            </div>

            <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Log out</a>
        </div>


        <div class="emp-nav">
            <a href="dashboard.php" class="emp-nav-item">🏠 Dashboard</a>
            <a href="attendance.php" class="emp-nav-item">🕒 Attendance</a>
            <a href="leaves.php" class="emp-nav-item">🗓️ Leaves</a>
            <a href="tasks.php" class="emp-nav-item">✅ Tasks</a>
            <a href="announcements.php" class="emp-nav-item active">📢 Announcements</a>
            <a href="documents.php" class="emp-nav-item">📁 Documents</a>
            <a href="events.php" class="emp-nav-item">📅 Events</a>
        </div>


        <div class="panel">

            <div class="panel-notch"></div>

            <h2 class="panel-title">Announcements</h2>

            <div class="item-list">

                <?php while($row = $result->fetch_assoc()): ?>

                    <div class="list-item">

                        <div class="list-item-header">

                            <h3 class="list-item-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>

                            <span class="list-item-meta">
                                <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                            </span>

                        </div>


                        <p class="list-item-body">
                            <?php echo htmlspecialchars($row['content']); ?>
                        </p>


                        <p class="list-item-meta"
                           style="margin-top:.5rem; display:block;">

                            Posted by
                            <?php echo htmlspecialchars($row['poster_name']); ?>

                        </p>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>

    </div>

</div>

    <script src="theme.js"></script>
</body>
</html>