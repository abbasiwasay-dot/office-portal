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

$select="SELECT * FROM `events` ORDER BY event_date ASC";
$query=$connection->prepare($select);
$query->execute();

$result=$query->get_result();
?>


<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Events</title> 
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
                <a href="attendance.php" class="emp-nav-item">🕒 Attendance</a> 
                <a href="leaves.php" class="emp-nav-item">🗓️ Leaves</a> 
                <a href="tasks.php" class="emp-nav-item">✅ Tasks</a> 
                <a href="announcements.php" class="emp-nav-item">📢 Announcements</a> 
                <a href="documents.php" class="emp-nav-item">📁 Documents</a> 
                <a href="events.php" class="emp-nav-item active">📅 Events</a> 
            </div> 
 
            <div class="panel"> 
                <div class="panel-notch"></div> 
                <h2 class="panel-title">Company Events</h2> 

                <div class="item-list"> 


                    <?php while($row=$result->fetch_assoc()){ ?>

                    <div class="event-row list-item"> 

                        <div class="date-chip">
                            <span class="d">
                                <?php echo date("d",strtotime($row['event_date'])); ?>
                            </span>

                            <span class="m">
                                <?php echo date("M",strtotime($row['event_date'])); ?>
                            </span>
                        </div> 

                        <div> 

                            <h3 class="list-item-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3> 

                            <p class="list-item-body">
                                <?php echo htmlspecialchars($row['description']); ?>
                            </p> 

                        </div> 

                    </div> 

                    <?php } ?>


                </div> 
            </div> 
 
        </div> 
    </div> 
    <script src="theme.js"></script>
</body> 
</html>