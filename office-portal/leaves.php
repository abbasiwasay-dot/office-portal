
<?php
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])){
    header("location:login.php");
    exit();
}
$user_session=$_SESSION['user_id'];

$data="SELECT * FROM `users` WHERE id=?";
$details=$connection->prepare($data);
$details->bind_param("i",$user_session);
$details->execute();
$getresult=$details->get_result();
$userdetails=$getresult->fetch_assoc();

if($userdetails['role']!="employee"){
    header("location:admin_dashboard.php");
    exit();
}

$leaveError="";

if(isset($_POST['apply'])){
    $leave=$_POST['leave_type'];
    $startdate=$_POST['start_date'];
    $enddate=$_POST['end_date'];
    $reason=$_POST['reason'];
    $status="pending";
    if($reason!==""){

        $insertleave="INSERT INTO `leaves`(user_id,`leave_type`,`start_date`,`end_date`,`reason`,`status`) VALUES(?,?,?,?,?,?)";
        $prepareleave=$connection->prepare($insertleave);
        $prepareleave->bind_param("isssss",$user_session,$leave,$startdate,$enddate,$reason,$status);
        $result=$prepareleave->execute();
        if($result){
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        else{
            $leaveError="Something went wrong submitting your request. Please try again.";
        }
    }
    else{
        $leaveError="Please add a reason for your leave request.";
    }
}
    $fetchleaves = "SELECT * FROM `leaves` WHERE user_id=? ORDER BY applied_at DESC";
 $prepfetch = $connection->prepare($fetchleaves);
 $prepfetch->bind_param("i", $user_session);
 $prepfetch->execute();
 $leaverecords = $prepfetch->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Leaves</title>
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
                <a href="leaves.php" class="emp-nav-item active">🗓️ Leaves</a>
                <a href="tasks.php" class="emp-nav-item">✅ Tasks</a>
                <a href="announcements.php" class="emp-nav-item">📢 Announcements</a>
                <a href="documents.php" class="emp-nav-item">📁 Documents</a>
                <a href="events.php" class="emp-nav-item">📅 Events</a>
            </div>

            <div class="panel">
                <div class="panel-notch"></div>
                <h2 class="panel-title">Apply for Leave</h2>
                <?php if($leaveError !== ""){ ?>
                    <p class="form-note" style="margin-bottom:1rem;"><?php echo htmlspecialchars($leaveError); ?></p>
                <?php } ?>
                <form class="form-panel" style="margin-top:0;" method="post">
                    <div class="form-row">
                        <div class="field">
                            <label>Leave Type</label>
                            <select name="leave_type">
                                <option>Casual</option>
                                <option>Sick</option>
                                <option>Annual</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="field"></div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label>Start Date</label>
                            <input type="date" name="start_date">
                        </div>
                        <div class="field">
                            <label>End Date</label>
                            <input type="date" name="end_date">
                        </div>
                    </div>
                    <div class="field">
                        <label>Reason</label>
                        <textarea placeholder="Briefly explain why you're requesting leave" name="reason"></textarea>
                    </div>
                    <button type="submit" class="btn-primary" name="apply">Submit Request</button>
                </form>
            </div>
            <div class="panel">
                <div class="panel-notch"></div>
                <h2 class="panel-title">My Leave Requests</h2>
                <?php if($leaverecords->num_rows > 0) { ?>
                <div class="table-scroll">
                    <table class="users-table">
                        <thead><tr><th>Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th></tr></thead>
                       <tbody>
                        <?php while($row = $leaverecords->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td><?= htmlspecialchars($row['reason']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= htmlspecialchars($row['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                    <p class="empty-state">You haven't submitted any leave requests yet.</p>
                <?php } ?>
            </div>

        </div>
    </div>
    <script src="theme.js"></script>
</body>
</html>
