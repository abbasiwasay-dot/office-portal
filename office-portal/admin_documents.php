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

$uploadError="";

if(isset($_POST['upload'])){

    $title=trim($_POST['title'] ?? '');
    $file=$_FILES['document'] ?? null;

    if($title === '' || !$file || empty($file['name'])){
        $uploadError="Please provide a title and choose a file.";
    }
    else{

        // only allow common document types - never let an executable script land in uploads/
        $allowedExt=['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','csv','png','jpg','jpeg'];
        $originalName=basename($file['name']);
        $ext=strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if(!in_array($ext,$allowedExt)){
            $uploadError="That file type isn't allowed. Allowed types: ".implode(', ',$allowedExt).".";
        }
        else if($file['error'] !== UPLOAD_ERR_OK){
            $uploadError="The file failed to upload. Please try again.";
        }
        else{

            if(!is_dir('uploads')){
                mkdir('uploads', 0755, true);
            }

            // store under a unique name so uploads never collide or overwrite each other
            $filename=uniqid('doc_', true).'.'.$ext;
            $filepath="uploads/".$filename;

            if(move_uploaded_file($file['tmp_name'],$filepath)){

                $insertfile="INSERT INTO `documents` (title,filename,filepath,uploaded_by)
                VALUES(?,?,?,?)";

                $prep=$connection->prepare($insertfile);
                $prep->bind_param("sssi",$title,$originalName,$filepath,$user_id);
                $prep->execute();
            }
            else{
                $uploadError="Could not save the uploaded file.";
            }
        }
    }
}

if(isset($_GET['delete'])){

    $del_id=$_GET['delete'];

    $selectfile="SELECT filepath FROM `documents` WHERE id=?";
    $file_query=$connection->prepare($selectfile);
    $file_query->bind_param("i",$del_id);
    $file_query->execute();
    $file_result=$file_query->get_result();
    $file_data=$file_result->fetch_assoc();

    if($file_data){

        $filepath=$file_data['filepath'];

        if(file_exists($filepath)){
            unlink($filepath);
        }

        $delete="DELETE FROM `documents` WHERE id=?";
        $delete_query=$connection->prepare($delete);
        $delete_query->bind_param("i",$del_id);
        $delete_query->execute();
    }

    header("location:admin_documents.php");
    exit();
}

$select="SELECT documents.*,users.name AS uploader_name
FROM `documents`
LEFT JOIN `users` ON documents.uploaded_by=users.id
ORDER BY documents.created_at DESC";

$prep=$connection->prepare($select);
$prep->execute();
$get_result=$prep->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Documents</title>
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
                <a href="admin_documents.php" class="nav-item active"><span class="nav-icon">📁</span> Documents</a>
                <a href="admin_events.php" class="nav-item"><span class="nav-icon">📅</span> Events</a>
                <a href="admin_settings.php" class="nav-item"><span class="nav-icon">⚙️</span> Settings</a>
                <a href="logout.php" class="nav-item nav-logout"><span class="nav-icon">🚪</span> Logout</a>
            </nav>

        </aside>

        <main class="admin-main">

            <header class="admin-topbar">
                <h1 class="topbar-title">Documents</h1>

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

                <h2 class="panel-title">Upload Document</h2>
                <?php if($uploadError !== ""){ ?>
                    <p class="form-note" style="margin-bottom:1rem;"><?php echo htmlspecialchars($uploadError); ?></p>
                <?php } ?>

                <form class="form-panel" style="margin-top:0;" method="POST" enctype="multipart/form-data">

                    <div class="field">
                        <label>Title</label>
                        <input type="text" placeholder="e.g. Employee Handbook" name="title">
                    </div>

                    <div class="field">
                        <label>File</label>
                        <input type="file" name="document">
                    </div>

                    <button type="submit" class="btn-primary" name="upload">
                        Upload
                    </button>

                </form>

            </div>

            <div class="panel">

                <div class="panel-notch"></div>

                <h2 class="panel-title">All Documents</h2>

                <div class="item-list">

                    <?php
                    while($row=$get_result->fetch_assoc()){
                    ?>

                    <div class="list-item">

                        <div class="list-item-header">

                            <h3 class="list-item-title">
                                <span class="file-icon">📄</span>
                                <?php echo htmlspecialchars($row['title']); ?>
                            </h3>

                            <span class="list-item-meta">
                                <?php echo date("d M Y",strtotime($row['created_at'])); ?>
                            </span>

                        </div>

                        <p class="list-item-meta" style="display:block; margin-bottom:.5rem;">
                            Uploaded by <?php echo htmlspecialchars($row['uploader_name']); ?>
                        </p>

                        <div class="list-item-actions">

                            <a href="?delete=<?php echo $row['id']; ?>" class="table-action danger">
                                Delete
                            </a>

                        </div>

                    </div>

                    <?php
                    }
                    ?>

                </div>

            </div>

        </main>

    </div>
</div>

    <script src="theme.js"></script>
</body>
</html>