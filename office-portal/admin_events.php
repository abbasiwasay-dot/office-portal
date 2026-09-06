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

if(isset($_POST['create_event'])){

    $title=$_POST['title'];
    $description=$_POST['description'];
    $event_date=$_POST['event_date'];
    $created_by=$_SESSION['user_id'];

    if($title=='' || $description=='' || $event_date==''){

    }
    else{

        $insert="INSERT INTO `events` (`title`,`description`,`event_date`,`created_by`)
        VALUES (?,?,?,?)";

        $query=$connection->prepare($insert);
        $query->bind_param("sssi",$title,$description,$event_date,$created_by);

        if($query->execute()){
            header("Location: admin_events.php");
            exit;
        }
    }
}


if(isset($_GET['delete'])){

    $id=$_GET['delete'];

    $delete="DELETE FROM `events` WHERE id=?";

    $query=$connection->prepare($delete);
    $query->bind_param("i",$id);

    if($query->execute()){
        header("Location: admin_events.php");
        exit;
    }
}


$select="SELECT * FROM `events` ORDER BY event_date ASC";
$result=$connection->query($select);

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
                    <a href="admin_events.php" class="nav-item active"><span class="nav-icon">📅</span> Events</a>
                    <a href="admin_settings.php" class="nav-item"><span class="nav-icon">⚙️</span> Settings</a>
                    <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a>
                </nav>
            </aside>

            <main class="admin-main">
                <header class="admin-topbar">
                    <h1 class="topbar-title">Events</h1>
                    <div class="topbar-right">
                        <div class="admin-chip"><span class="avatar-circle small">A</span> Admin</div>
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a>
                    </div>
                </header>

                <div class="panel">
                    <div class="panel-notch"></div>
                    <h2 class="panel-title">Create Event</h2>

                    <form class="form-panel" style="margin-top:0;" method="POST">

                        <div class="form-row">
                            <div class="field">
                                <label>Title</label>
                                <input type="text" name="title" placeholder="Event title">
                            </div>

                            <div class="field">
                                <label>Date</label>
                                <input type="date" name="event_date">
                            </div>
                        </div>

                        <div class="field">
                            <label>Description</label>
                            <textarea name="description" placeholder="Event details"></textarea>
                        </div>

                        <button type="submit" name="create_event" class="btn-primary">
                            Create Event
                        </button>

                    </form>
                </div>

                <div class="panel">
                    <div class="panel-notch"></div>
                    <h2 class="panel-title">All Events</h2>

                    <div class="item-list">

                        <?php while($row=$result->fetch_assoc()){ ?>

                        <div class="list-item">
                            <div class="event-row">

                                <div class="date-chip">
                                    <span class="d"><?php echo date("d",strtotime($row['event_date'])); ?></span>
                                    <span class="m"><?php echo date("M",strtotime($row['event_date'])); ?></span>
                                </div>

                                <div style="flex:1;">
                                    <h3 class="list-item-title"><?php echo htmlspecialchars($row['title']); ?></h3>

                                    <p class="list-item-body"><?php echo htmlspecialchars($row['description']); ?></p>

                                    <div class="list-item-actions">
                                        <a href="admin_events.php?delete=<?php echo $row['id']; ?>" class="table-action danger">Delete</a>
                                    </div>
                                </div>

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