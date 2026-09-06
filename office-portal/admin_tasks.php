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


if($_SERVER["REQUEST_METHOD"]=="POST"){ 

    $title=$_POST['title']; 
    $description=$_POST['description']; 
    $assigned_to=$_POST['assigned_to']; 
    $due_date=$_POST['due_date']; 
    $assigned_by=$user_id; 
    $status="pending"; 

    $insert="INSERT INTO `tasks` 
    (`title`,`description`,`assigned_to`,`assigned_by`,`status`,`due_date`) 
    VALUES (?,?,?,?,?,?)";

    $task_query=$connection->prepare($insert);
    $task_query->bind_param("ssiiss",$title,$description,$assigned_to,$assigned_by,$status,$due_date);
    $task_query->execute();

    header("location:admin_tasks.php"); 
    exit(); 
} 


if(isset($_GET['delete'])){ 

    $del_id=$_GET['delete']; 

    $delete="DELETE FROM `tasks` WHERE id=?";
    $delete_query=$connection->prepare($delete);
    $delete_query->bind_param("i",$del_id);
    $delete_query->execute();

    header("location:admin_tasks.php"); 
    exit(); 
} 


$employee="SELECT * FROM `users` WHERE role='employee' ORDER BY name";
$employee_query=$connection->prepare($employee);
$employee_query->execute();
$employees=$employee_query->get_result();


$task="SELECT tasks.*,users.name AS employee_name
FROM `tasks`
LEFT JOIN `users` ON tasks.assigned_to=users.id
ORDER BY tasks.created_at DESC";

$task_query=$connection->prepare($task);
$task_query->execute();
$tasks=$task_query->get_result();

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
                    <a href="admin_tasks.php" class="nav-item active"><span class="nav-icon">✅</span> Tasks</a> 
                    <a href="admin_announcements.php" class="nav-item"><span class="nav-icon">📢</span> Announcements</a> 
                    <a href="admin_documents.php" class="nav-item"><span class="nav-icon">📁</span> Documents</a> 
                    <a href="admin_events.php" class="nav-item"><span class="nav-icon">📅</span> Events</a> 
                    <a href="admin_settings.php" class="nav-item"><span class="nav-icon">⚙️</span> Settings</a> 
                    <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a> 
                </nav> 
            </aside> 
 
            <main class="admin-main"> 
                <header class="admin-topbar"> 
                    <h1 class="topbar-title">Tasks</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip">
                            <span class="avatar-circle small">A</span> Admin
                        </div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title">Assign New Task</h2> 

                    <form class="form-panel" style="margin-top:0;" method="POST"> 

                        <div class="field"> 
                            <label>Title</label> 
                            <input type="text" name="title" placeholder="Task title" required> 
                        </div> 

                        <div class="field"> 
                            <label>Description</label> 
                            <textarea name="description" placeholder="Task details" required></textarea> 
                        </div> 

                        <div class="form-row"> 

                            <div class="field"> 
                                <label>Assign To</label> 

                                <select name="assigned_to" required> 

                                    <option value="">Select Employee</option>

                                    <?php while($employee_data=$employees->fetch_assoc()){ ?>

                                        <option value="<?php echo $employee_data['id']; ?>">
                                            <?php echo htmlspecialchars($employee_data['name']); ?>
                                        </option>

                                    <?php } ?>

                                </select> 
                            </div> 


                            <div class="field"> 
                                <label>Due Date</label> 
                                <input type="date" name="due_date" required> 
                            </div> 

                        </div> 

                        <button type="submit" class="btn-primary">
                            Assign Task
                        </button> 

                    </form> 
                </div> 
 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title">All Tasks</h2> 

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
                                Assigned to <?php echo htmlspecialchars($row['employee_name']); ?> 
                                · Due <?php echo date("d M Y",strtotime($row['due_date'])); ?>
                            </p> 

                            <div class="list-item-actions"> 

                                <a href="admin_tasks.php?delete=<?php echo $row['id']; ?>" class="table-action danger">
                                    Delete
                                </a> 

                            </div> 

                        </div> 

                        <?php } ?>

                    </div> 
                </div> 

            </main> 

        </div> 
    </div> 
    <script src="theme.js"></script>
</body> 
</html>