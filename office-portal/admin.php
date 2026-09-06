<?php
// admin_dashboard.php is the maintained admin dashboard (this file used to
// require includes/auth.php and includes/admin_sidebar.php, which were
// removed - see README.txt - and no page links here anymore, so it just
// forwards to the real dashboard instead of throwing a fatal error).
header("location:admin_dashboard.php");
exit();
