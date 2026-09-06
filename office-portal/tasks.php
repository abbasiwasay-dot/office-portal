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


if($_SERVER["REQUEST_METHOD"]=="POST"){ 

    $task_id=$_POST['task_id']; 
    $status=$_POST['status']; 

    $update="UPDATE `tasks` SET status=? WHERE id=? AND assigned_to=?";
    $query=$connection->prepare($update);
    $query->bind_param("sii",$status,$task_id,$user_id);
    $query->execute();

    header("location:tasks.php"); 
    exit(); 
} 


$task="SELECT tasks.*,users.name AS admin_name
FROM `tasks`
LEFT JOIN `users` ON tasks.assigned_by=users.id
WHERE tasks.assigned_to=?
ORDER BY tasks.created_at DESC";

$task_query=$connection->prepare($task);
$task_query->bind_param("i",$user_id);
$task_query->execute();
$tasks=$task_query->get_result();

$employeename=$userdetails['name'];

?>


<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Tasks</title> 
    <link rel="stylesheet" href="style.css"> 
</head> 
<body> 
    <div class="scene"> 
        <div class="page-shell"> 
 
            <div class="dash-header"> 
                <div class="dash-logo"><span class="dash-logo-mark"></span> Office Portal</div> 
                <div class="dash-header-welcome">
                    Welcome, <span><?php echo htmlspecialchars($employeename); ?></span>
                </div> 
                <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Log out</a> 
            </div> 
 
            <div class="emp-nav"> 
                <a href="dashboard.php" class="emp-nav-item">🏠 Dashboard</a> 
                <a href="attendance.php" class="emp-nav-item">🕒 Attendance</a> 
                <a href="leaves.php" class="emp-nav-item">🗓️ Leaves</a> 
                <a href="tasks.php" class="emp-nav-item active">✅ Tasks</a> 
                <a href="announcements.php" class="emp-nav-item">📢 Announcements</a> 
                <a href="documents.php" class="emp-nav-item">📁 Documents</a> 
                <a href="events.php" class="emp-nav-item">📅 Events</a> 
            </div> 
 
            <div class="panel"> 
                <div class="panel-notch"></div> 
                <h2 class="panel-title">My Tasks</h2> 

                <div class="item-list"> 

                    <?php while($row=$tasks->fetch_assoc()){ ?>

                    <div class="list-item"> 

                        <div class="list-item-header"> 

                            <h3 class="list-item-title">
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3> 

                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucwords(str_replace("_"," ",$row['status'])); ?>
                            </span> 

                        </div> 

                        <p class="list-item-body">
                            <?php echo htmlspecialchars($row['description']); ?>
                        </p> 

                        <p class="list-item-meta" style="margin-top:.5rem; display:block;">
                            Assigned by <?php echo htmlspecialchars($row['admin_name']); ?> 
                            · Due <?php echo date("d M Y",strtotime($row['due_date'])); ?>
                        </p> 

                        <div class="list-item-actions"> 

                            <?php if($row['status']!="completed"){ ?>

                            <form method="POST" style="display:flex; gap:.5rem; align-items:center;">

                                <input type="hidden" name="task_id" value="<?php echo $row['id']; ?>">

                                <select name="status" style="width:auto; padding:.5rem .7rem; background:var(--ink); border:1px solid rgba(255,255,255,.1); border-radius:8px; color:var(--paper); font-family:'Inter',sans-serif; font-size:.82rem;"> 

                                    <option value="pending" <?php echo $row['status']=="pending" ? "selected" : ""; ?>>
                                        Pending
                                    </option> 

                                    <option value="in_progress" <?php echo $row['status']=="in_progress" ? "selected" : ""; ?>>
                                        In Progress
                                    </option> 

                                    <option value="completed" <?php echo $row['status']=="completed" ? "selected" : ""; ?>>
                                        Completed
                                    </option> 

                                </select> 

                                <button type="submit" class="btn-secondary" style="padding:.5rem .9rem; font-size:.8rem;">
                                    Update
                                </button> 

                            </form>

                            <?php } ?>

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