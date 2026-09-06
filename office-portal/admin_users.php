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

if(isset($_GET['delete'])){ 
    $delId=$_GET['delete']; 

    // never allow deleting an admin account (including your own) through this link -
    // the UI already hides the delete link for admins, this backs that up server-side
    $check="SELECT role FROM `users` WHERE id=?";
    $check_query=$connection->prepare($check);
    $check_query->bind_param("i",$delId);
    $check_query->execute();
    $target=$check_query->get_result()->fetch_assoc();

    if($target && $target['role']!="admin"){
        $delete="DELETE FROM `users` WHERE id=?";
        $get_user=$connection->prepare($delete);
        $get_user->bind_param("i",$delId);
        $get_user->execute();
    }

    header("location:admin_users.php"); 
    exit(); 
} 

if($_SERVER["REQUEST_METHOD"]=="POST"){ 
    $user_id=$_POST['user_id']; 
    $role=$_POST['role']; 
    $department_id=$_POST['department_id']; 

    if($department_id==""){ 
        $department_id=null; 
    } 

    $update="UPDATE `users` SET role=?,department_id=? WHERE id=?";
    $query=$connection->prepare($update);
    $query->bind_param("sii",$role,$department_id,$user_id);
    $query->execute();

    header("location:admin_users.php"); 
    exit(); 
} 


$department="SELECT * FROM `departments` ORDER BY name";
$department_query=$connection->prepare($department);
$department_query->execute();
$departments=$department_query->get_result();

$deptList=[];

while($d=$departments->fetch_assoc()){
    $deptList[]=$d;
}


$user="SELECT users.*,departments.name AS department_name
FROM `users`
LEFT JOIN `departments` ON users.department_id=departments.id
ORDER BY users.created_at DESC";

$userdata=$connection->prepare($user);
$userdata->execute();
$users=$userdata->get_result();

?>


<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Users</title> 
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
                    <a href="admin_users.php" class="nav-item active"><span class="nav-icon">👥</span> Users</a> 
                    <a href="admin_departments.php" class="nav-item"><span class="nav-icon">🏢</span> Departments</a> 
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
                    <h1 class="topbar-title">Users</h1> 
                    <div class="topbar-right"> 
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div> 
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a> 
                    </div> 
                </header> 

                <div class="panel"> 
                    <div class="panel-notch"></div> 
                    <div class="table-header"> 
                        <h2 class="panel-title">Registered Users</h2> 
                    </div> 

                    <div class="table-scroll"> 
                        <table class="users-table"> 
                            <thead> 
                                <tr> 
                                    <th>Name</th> 
                                    <th>Email</th> 
                                    <th>Department</th> 
                                    <th>Role</th> 
                                    <th>Actions</th> 
                                </tr> 
                            </thead> 

                            <tbody> 

                                <?php while($row=$users->fetch_assoc()){ ?> 

                                <tr> 
                                    <td><?php echo htmlspecialchars($row['name']); ?></td> 
                                    <td><?php echo htmlspecialchars($row['email']); ?></td> 

                                    <td>
                                        <?php 
                                        echo $row['department_name'] 
                                        ? htmlspecialchars($row['department_name']) 
                                        : "—"; 
                                        ?>
                                    </td> 

                                    <td> 
                                        <span class="role-badge role-<?php echo htmlspecialchars($row['role']); ?>"> 
                                            <?php echo htmlspecialchars($row['role']); ?> 
                                        </span> 
                                    </td> 

                                    <td> 
                                        <a href="#" class="table-action edit-toggle" data-id="<?php echo $row['id']; ?>">
                                            Edit
                                        </a> 

                                        <?php if($row['role']!="admin"){ ?>

                                            <a href="admin_users.php?delete=<?php echo $row['id']; ?>" class="table-action danger">
                                                Delete
                                            </a>

                                        <?php } ?>
                                    </td> 
                                </tr> 


                                <tr id="edit-row-<?php echo $row['id']; ?>" style="display:none;"> 
                                    <td colspan="5"> 

                                        <form method="POST" class="form-row" style="align-items:end; padding:.6rem 0;"> 

                                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>"> 

                                            <div class="field"> 
                                                <label>Role</label> 

                                                <select name="role"> 

                                                    <option value="employee" <?php echo $row['role']=="employee" ? "selected" : ""; ?>>
                                                        Employee
                                                    </option> 

                                                    <option value="admin" <?php echo $row['role']=="admin" ? "selected" : ""; ?>>
                                                        Admin
                                                    </option> 

                                                </select> 
                                            </div> 


                                            <div class="field"> 
                                                <label>Department</label> 

                                                <select name="department_id"> 

                                                    <option value="">— None —</option> 

                                                    <?php foreach($deptList as $d){ ?> 

                                                        <option value="<?php echo $d['id']; ?>" <?php echo $row['department_id']==$d['id'] ? "selected" : ""; ?>> 
                                                            <?php echo htmlspecialchars($d['name']); ?> 
                                                        </option> 

                                                    <?php } ?> 

                                                </select> 
                                            </div> 


                                            <button type="submit" class="btn-primary btn-sm">
                                                Save
                                            </button> 

                                        </form> 

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


    <script> 

    document.querySelectorAll('.edit-toggle').forEach(btn => { 

        btn.addEventListener('click', function(e){ 

            e.preventDefault(); 

            const row=document.getElementById('edit-row-'+this.dataset.id); 

            if(row.style.display=="none"){ 
                row.style.display="table-row"; 
            } 
            else{ 
                row.style.display="none"; 
            } 

        }); 

    }); 

    </script> 

    <script src="theme.js"></script>
</body> 
</html>