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

if(isset($_POST['add_department'])){ 
    $name=$_POST['department_name'];

    if($name==""){ 

    } 
    else{ 

        $sql="INSERT INTO `departments` (`name`) VALUES (?)";
        $query=$connection->prepare($sql);
        $query->bind_param("s",$name);
        $query->execute();
    }
}


if(isset($_GET['delete'])){

    $id=$_GET['delete'];

    $sql="DELETE FROM `departments` WHERE id=?";
    $query=$connection->prepare($sql);
    $query->bind_param("i",$id);
    $query->execute();

    header("location:admin_departments.php");
    exit();
}


$sql="SELECT departments.id,departments.name,COUNT(users.id) AS members
      FROM departments
      LEFT JOIN users ON departments.id=users.department_id
      GROUP BY departments.id";

$query=$connection->prepare($sql);
$query->execute();
$result=$query->get_result();

?>

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Departments</title> 
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
                    <h1 class="topbar-title">Departments</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title">Add Department</h2> 
                    <form class="form-row" style="align-items:end;" method="POST"> 
                        <div class="field"> 
                            <label>Department Name</label> 
                            <input type="text" name="department_name" placeholder="e.g. Engineering" required> 
                        </div> 
                        <button type="submit" name="add_department" class="btn-primary">Add</button> 
                    </form> 
                </div> 
 
                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <h2 class="panel-title">All Departments</h2> 
                    <div class="table-scroll"> 
                        <table class="users-table"> 
                            <thead><tr><th>Name</th><th>Members</th><th>Actions</th></tr></thead> 
                            <tbody> 

                            <?php while($row=$result->fetch_assoc()){ ?>

                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['members']; ?></td>
                                <td>
                                    <a href="admin_department_members.php?id=<?php echo $row['id']; ?>" class="table-action">View Members</a>
                                    <a href="admin_departments.php?delete=<?php echo $row['id']; ?>" class="table-action danger">Delete</a>
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