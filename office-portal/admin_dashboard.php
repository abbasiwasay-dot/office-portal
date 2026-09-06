<?php 
session_start(); 
require "db.php"; 

if(isset($_SESSION['user_id'])){ 
    $user_id=$_SESSION['user_id']; 
    $data="SELECT * FROM `users` WHERE id=?"; 
    $details=$connection->prepare($data); 
    $details->bind_param("i",$user_id); 
    $result=$details->execute(); 
    $getresult=$details->get_result(); 
    $userdetails=$getresult->fetch_assoc(); 

    if($userdetails['role']=="admin"){ 
        
    } 
    else{ 
        header("location:dashboard.php"); 
        exit(); 
    } 
} 
else{ 
    header("location:login.php"); 
    exit(); 
} 

$adminname=$userdetails['name']; 

$user="SELECT * FROM `users`"; 
$userdata=$connection->prepare($user); 
$userdata->execute(); 
$data_result=$userdata->get_result(); 

if(isset($_GET['id'])){ 
    $del_id=$_GET['id']; 

    $check="SELECT role FROM `users` WHERE id=?"; 
    $check_query=$connection->prepare($check); 
    $check_query->bind_param("i",$del_id); 
    $check_query->execute(); 
    $target=$check_query->get_result()->fetch_assoc(); 

    if($target && $target['role']!="admin"){ 
        $delete="DELETE FROM `users` WHERE id=?"; 
        $get_user=$connection->prepare($delete); 
        $get_user->bind_param("i",$del_id); 
        $get_user->execute(); 
    }

    header("Location: admin_dashboard.php"); 
    exit(); 
} 

$count=1; 

$count_total="SELECT COUNT(*) AS total FROM `users`"; 
$count_query=$connection->prepare($count_total); 
$count_query->execute(); 
$count_result=$count_query->get_result(); 
$count_data=$count_result->fetch_assoc(); 
$total_users=$count_data['total'];


$count_dept = $connection->query("SELECT COUNT(*) AS total FROM `departments`");
$total_departments = $count_dept ? $count_dept->fetch_assoc()['total'] : 0;

$today = date("Y-m-d");
$count_present = $connection->prepare("SELECT COUNT(*) AS total FROM `attendance` WHERE work_date=? AND check_in IS NOT NULL");
$count_present->bind_param("s", $today);
$count_present->execute();
$present_today = $count_present->get_result()->fetch_assoc()['total'];

$count_leaves = $connection->query("SELECT COUNT(*) AS total FROM `leaves` WHERE status='pending'");
$pending_leaves = $count_leaves ? $count_leaves->fetch_assoc()['total'] : 0;

$count_tasks = $connection->query("SELECT COUNT(*) AS total FROM `tasks` WHERE status != 'completed'");
$open_tasks = $count_tasks ? $count_tasks->fetch_assoc()['total'] : 0;

$recentLeavesQuery = $connection->query(
    "SELECT leaves.*, users.name AS employee_name
     FROM leaves
     JOIN users ON leaves.user_id = users.id
     ORDER BY leaves.applied_at DESC
     LIMIT 5"
);

$recentAnnouncementsQuery = $connection->query(
    "SELECT announcements.*, users.name AS author_name
     FROM announcements
     JOIN users ON announcements.posted_by = users.id
     ORDER BY announcements.created_at DESC
     LIMIT 3"
);

$recentTasksQuery = $connection->query(
    "SELECT tasks.*, users.name AS employee_name
     FROM tasks
     LEFT JOIN users ON tasks.assigned_to = users.id
     ORDER BY tasks.created_at DESC
     LIMIT 5"
);

$upcomingEventsQuery = $connection->prepare(
    "SELECT * FROM `events` WHERE event_date >= ? ORDER BY event_date ASC LIMIT 3"
);
$upcomingEventsQuery->bind_param("s", $today);
$upcomingEventsQuery->execute();
$upcomingEvents = $upcomingEventsQuery->get_result();
 

?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="scene">
        <div class="admin-shell">

            <!-- Sidebar -->
            <aside class="admin-sidebar">
                <div class="sidebar-brand">
                    <span class="dash-logo-mark"></span>
                    <div>
                        <div class="brand-title">Office Portal</div>
                        <div class="brand-sub">Admin Panel</div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                    <a href="admin_dashboard.php" class="nav-item active">
                        <span class="nav-icon">🏠</span> Dashboard
                    </a>
                    <a href="admin_users.php" class="nav-item">
                        <span class="nav-icon">👥</span> Users
                    </a>
                    <a href="admin_departments.php" class="nav-item">
                        <span class="nav-icon">🏢</span> Departments
                    </a>
                    <a href="admin_attendance.php" class="nav-item">
                        <span class="nav-icon">🕒</span> Attendance
                    </a>
                    <a href="admin_leaves.php" class="nav-item">
                        <span class="nav-icon">🗓️</span> Leaves
                    </a>
                    <a href="admin_tasks.php" class="nav-item">
                        <span class="nav-icon">✅</span> Tasks
                    </a>
                    <a href="admin_announcements.php" class="nav-item">
                        <span class="nav-icon">📢</span> Announcements
                    </a>
                    <a href="admin_documents.php" class="nav-item">
                        <span class="nav-icon">📁</span> Documents
                    </a>
                    <a href="admin_events.php" class="nav-item">
                        <span class="nav-icon">📅</span> Events
                    </a>
                    <a href="admin_settings.php" class="nav-item">
                        <span class="nav-icon">⚙️</span> Settings
                    </a>
                    <a href="logout.php" class="nav-item nav-logout">
                        <span class="nav-icon">🚪</span> Logout
                    </a>
                </nav>
            </aside>

            <!-- Main area -->
            <main class="admin-main">

                <header class="admin-topbar">
                    <h1 class="topbar-title">Welcome, <?php echo htmlspecialchars($adminname); ?> <span>👋</span></h1>
                    <div class="topbar-right">
                        <div class="admin-chip">
                            <span class="avatar-circle small">A</span> Admin
                        </div>
                        <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Logout</a>
                    </div>
                </header>

                <section class="admin-view">

                    <!-- Hero -->
                    <div class="hero-panel">
                        <div>
                            <span class="hero-eyebrow">Command Center</span>
                            <h2 class="hero-title">Good to see you, <?php echo htmlspecialchars($adminname); ?>.</h2>
                            <p class="hero-sub">Here&rsquo;s what&rsquo;s happening across the office today.</p>
                        </div>
                        <div class="hero-time">
                            <span>Today</span>
                            <span class="hero-time-value"><?php echo date("d M Y"); ?></span>
                        </div>
                    </div>

                    <!-- Stat strip -->
                    <div class="stat-strip">
                        <div class="stat-chip" style="--chip-color: rgba(217,164,85,.18);">
                            <div class="stat-chip-icon">👥</div>
                            <span class="stat-chip-label">Total Users</span>
                            <span class="stat-chip-value"><?php echo $total_users; ?></span>
                        </div>
                        <div class="stat-chip" style="--chip-color: rgba(63,143,111,.2);">
                            <div class="stat-chip-icon">🏢</div>
                            <span class="stat-chip-label">Departments</span>
                            <span class="stat-chip-value"><?php echo $total_departments; ?></span>
                        </div>
                        <div class="stat-chip" style="--chip-color: rgba(111,179,126,.2);">
                            <div class="stat-chip-icon">🕒</div>
                            <span class="stat-chip-label">Present Today</span>
                            <span class="stat-chip-value"><?php echo $present_today; ?></span>
                        </div>
                        <div class="stat-chip" style="--chip-color: rgba(210,104,93,.18);">
                            <div class="stat-chip-icon">🗓️</div>
                            <span class="stat-chip-label">Pending Leaves</span>
                            <span class="stat-chip-value"><?php echo $pending_leaves; ?></span>
                        </div>
                        <div class="stat-chip" style="--chip-color: rgba(127,165,153,.22);">
                            <div class="stat-chip-icon">✅</div>
                            <span class="stat-chip-label">Open Tasks</span>
                            <span class="stat-chip-value"><?php echo $open_tasks; ?></span>
                        </div>
                    </div>

                    <!-- Recents -->
                    <div class="recent-grid">

                        <div class="panel">
                            <div class="panel-notch"></div>
                            <div class="table-header">
                                <h2 class="panel-title">Recent Leave Requests</h2>
                                <a href="admin_leaves.php" class="table-action">View all</a>
                            </div>

                            <?php if($recentLeavesQuery->num_rows === 0){ ?>
                                <p class="empty-state">No leave requests yet.</p>
                            <?php } else { ?>
                                <?php while($row = $recentLeavesQuery->fetch_assoc()){ ?>
                                    <div class="mini-item">
                                        <div>
                                            <p class="mini-item-title"><?php echo htmlspecialchars($row['employee_name']); ?> — <?php echo ucfirst($row['leave_type']); ?></p>
                                            <p class="mini-item-sub"><?php echo date("d M", strtotime($row['start_date'])); ?> – <?php echo date("d M Y", strtotime($row['end_date'])); ?></p>
                                        </div>
                                        <span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="panel">
                            <div class="panel-notch"></div>
                            <div class="table-header">
                                <h2 class="panel-title">Recent Tasks</h2>
                                <a href="admin_tasks.php" class="table-action">View all</a>
                            </div>

                            <?php if($recentTasksQuery->num_rows === 0){ ?>
                                <p class="empty-state">No tasks assigned yet.</p>
                            <?php } else { ?>
                                <?php while($row = $recentTasksQuery->fetch_assoc()){ ?>
                                    <div class="mini-item">
                                        <div>
                                            <p class="mini-item-title"><?php echo htmlspecialchars($row['title']); ?></p>
                                            <p class="mini-item-sub"><?php echo htmlspecialchars($row['employee_name'] ?? 'Unassigned'); ?> · Due <?php echo $row['due_date'] ? date("d M Y", strtotime($row['due_date'])) : '—'; ?></p>
                                        </div>
                                        <span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucwords(str_replace('_',' ',$row['status'])); ?></span>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="panel">
                            <div class="panel-notch"></div>
                            <div class="table-header">
                                <h2 class="panel-title">Recent Announcements</h2>
                                <a href="admin_announcements.php" class="table-action">Manage</a>
                            </div>

                            <?php if($recentAnnouncementsQuery->num_rows === 0){ ?>
                                <p class="empty-state">No announcements posted yet.</p>
                            <?php } else { ?>
                                <?php while($row = $recentAnnouncementsQuery->fetch_assoc()){ ?>
                                    <div class="mini-item">
                                        <div>
                                            <p class="mini-item-title"><?php echo htmlspecialchars($row['title']); ?></p>
                                            <p class="mini-item-sub">By <?php echo htmlspecialchars($row['author_name']); ?> · <?php echo date("d M Y", strtotime($row['created_at'])); ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                        <div class="panel">
                            <div class="panel-notch"></div>
                            <div class="table-header">
                                <h2 class="panel-title">Upcoming Events</h2>
                                <a href="admin_events.php" class="table-action">View all</a>
                            </div>

                            <?php if($upcomingEvents->num_rows === 0){ ?>
                                <p class="empty-state">No upcoming events.</p>
                            <?php } else { ?>
                                <?php while($row = $upcomingEvents->fetch_assoc()){ ?>
                                    <div class="mini-item">
                                        <div>
                                            <p class="mini-item-title"><?php echo htmlspecialchars($row['title']); ?></p>
                                            <p class="mini-item-sub"><?php echo date("d M Y", strtotime($row['event_date'])); ?></p>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>

                    </div>

                </section>

            </main>
        </div>
    </div>
    <script src="theme.js"></script>
</body>
</html>