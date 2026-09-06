<?php 

session_start();
require "db.php";

if(!isset($_SESSION["user_id"])){
    header("location:login.php");
    exit();
}
$userid=$_SESSION['user_id'];
$find="SELECT * FROM `users` WHERE id=?";
$finduser=$connection->prepare($find);
$finduser->bind_param("i",$userid);
$finduser->execute();
$result=$finduser->get_result();
$user = $result->fetch_assoc();
$displayName=$user["name"];
$displayEmail=$user["email"];
$memberSince=$user["created_at"];
$lastLogin=$user["last_login"];

// --- Premium dashboard: extra summary + recent activity queries (added, nothing above touched) ---
$today = date("Y-m-d");

$att_stmt = $connection->prepare("SELECT * FROM `attendance` WHERE user_id=? AND work_date=?");
$att_stmt->bind_param("is", $userid, $today);
$att_stmt->execute();
$todayAttendance = $att_stmt->get_result()->fetch_assoc();

$leave_stmt = $connection->prepare("SELECT COUNT(*) AS total FROM `leaves` WHERE user_id=? AND status='pending'");
$leave_stmt->bind_param("i", $userid);
$leave_stmt->execute();
$myPendingLeaves = $leave_stmt->get_result()->fetch_assoc()['total'];

$task_count_stmt = $connection->prepare("SELECT COUNT(*) AS total FROM `tasks` WHERE assigned_to=? AND status != 'completed'");
$task_count_stmt->bind_param("i", $userid);
$task_count_stmt->execute();
$myOpenTasks = $task_count_stmt->get_result()->fetch_assoc()['total'];

$recentTasks_stmt = $connection->prepare(
    "SELECT tasks.*, users.name AS admin_name
     FROM tasks
     LEFT JOIN users ON tasks.assigned_by = users.id
     WHERE tasks.assigned_to=?
     ORDER BY tasks.created_at DESC
     LIMIT 3"
);
$recentTasks_stmt->bind_param("i", $userid);
$recentTasks_stmt->execute();
$myRecentTasks = $recentTasks_stmt->get_result();

$recentAnnouncements = $connection->query(
    "SELECT announcements.*, users.name AS author_name
     FROM announcements
     JOIN users ON announcements.posted_by = users.id
     ORDER BY announcements.created_at DESC
     LIMIT 3"
);

$upcomingEvents_stmt = $connection->prepare("SELECT * FROM `events` WHERE event_date >= ? ORDER BY event_date ASC LIMIT 3");
$upcomingEvents_stmt->bind_param("s", $today);
$upcomingEvents_stmt->execute();
$upcomingEvents = $upcomingEvents_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="scene">
        <div class="page-shell">

            <!-- Header -->
            <div class="dash-header">
                <div class="dash-logo">
                    <span class="dash-logo-mark"></span>
                    Office Portal
                </div>
                <div class="dash-header-welcome">Welcome, <span><?php echo htmlspecialchars($displayName); ?></span></div>
                <button type="button" id="themeToggle" class="theme-toggle" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
                        <a href="logout.php" class="btn-logout-sm">Log out</a>
            </div>

            <!-- Module Navigation -->
            <div class="emp-nav">
                <a href="dashboard.php" class="emp-nav-item active">🏠 Dashboard</a>
                <a href="attendance.php" class="emp-nav-item">🕒 Attendance</a>
                <a href="leaves.php" class="emp-nav-item">🗓️ Leaves</a>
                <a href="tasks.php" class="emp-nav-item">✅ Tasks</a>
                <a href="announcements.php" class="emp-nav-item">📢 Announcements</a>
                <a href="documents.php" class="emp-nav-item">📁 Documents</a>
                <a href="events.php" class="emp-nav-item">📅 Events</a>
            </div>

            <!-- Hero -->
            <div class="hero-panel">
                <div>
                    <span class="hero-eyebrow">Session Active</span>
                    <h2 class="hero-title">Good to see you, <?php echo htmlspecialchars($displayName); ?>.</h2>
                    <p class="hero-sub">Your badge has been scanned and verified.</p>
                </div>
                <div class="hero-time">
                    <span>Today</span>
                    <span class="hero-time-value"><?php echo date("d M Y"); ?></span>
                </div>
            </div>

            <!-- Stat strip -->
            <div class="stat-strip stat-strip-4">
                <div class="stat-chip" style="--chip-color: rgba(111,179,126,.2);">
                    <div class="stat-chip-icon">🕒</div>
                    <span class="stat-chip-label">Today&rsquo;s Check-in</span>
                    <span class="stat-chip-value" style="font-size:1.2rem;">
                        <?php echo ($todayAttendance && $todayAttendance['check_in']) ? date("h:i A", strtotime($todayAttendance['check_in'])) : '--:--'; ?>
                    </span>
                </div>
                <div class="stat-chip" style="--chip-color: rgba(217,164,85,.18);">
                    <div class="stat-chip-icon">🗓️</div>
                    <span class="stat-chip-label">Pending Leaves</span>
                    <span class="stat-chip-value"><?php echo $myPendingLeaves; ?></span>
                </div>
                <div class="stat-chip" style="--chip-color: rgba(127,165,153,.22);">
                    <div class="stat-chip-icon">✅</div>
                    <span class="stat-chip-label">Open Tasks</span>
                    <span class="stat-chip-value"><?php echo $myOpenTasks; ?></span>
                </div>
                <div class="stat-chip" style="--chip-color: rgba(210,104,93,.18);">
                    <div class="stat-chip-icon">📅</div>
                    <span class="stat-chip-label">Member Since</span>
                    <span class="stat-chip-value" style="font-size:1.1rem;"><?php echo date("d M Y", strtotime($memberSince)); ?></span>
                </div>
            </div>

            <!-- Recents -->
            <div class="recent-grid">

                <div class="panel">
                    <div class="panel-notch"></div>
                    <div class="table-header">
                        <h2 class="panel-title">My Recent Tasks</h2>
                        <a href="tasks.php" class="table-action">View all</a>
                    </div>

                    <?php if($myRecentTasks->num_rows === 0){ ?>
                        <p class="empty-state">No tasks assigned to you yet.</p>
                    <?php } else { ?>
                        <?php while($row = $myRecentTasks->fetch_assoc()){ ?>
                            <div class="mini-item">
                                <div>
                                    <p class="mini-item-title"><?php echo htmlspecialchars($row['title']); ?></p>
                                    <p class="mini-item-sub">By <?php echo htmlspecialchars($row['admin_name'] ?? 'Admin'); ?> · Due <?php echo $row['due_date'] ? date("d M Y", strtotime($row['due_date'])) : '—'; ?></p>
                                </div>
                                <span class="status-badge status-<?php echo $row['status']; ?>"><?php echo ucwords(str_replace('_',' ',$row['status'])); ?></span>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>

                <div class="panel">
                    <div class="panel-notch"></div>
                    <div class="table-header">
                        <h2 class="panel-title">Latest Announcements</h2>
                        <a href="announcements.php" class="table-action">View all</a>
                    </div>

                    <?php if($recentAnnouncements->num_rows === 0){ ?>
                        <p class="empty-state">No announcements yet.</p>
                    <?php } else { ?>
                        <?php while($row = $recentAnnouncements->fetch_assoc()){ ?>
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
                        <a href="events.php" class="table-action">View all</a>
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

                <div class="panel">
                    <div class="panel-notch"></div>
                    <h2 class="panel-title">Profile</h2>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($displayName); ?></span>
                        <span class="profile-email"><?php echo htmlspecialchars($displayEmail); ?></span>
                    </div>
                    <p class="mini-item-sub" style="margin-top:.9rem;">Last login: <?php echo $lastLogin ? date("d M Y, h:i A", strtotime($lastLogin)) : '—'; ?></p>
                </div>

            </div>

        </div>
    </div>
    <script src="theme.js"></script>
</body>
</html>