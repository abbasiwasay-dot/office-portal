
<?php
session_start();
require "db.php";

// already logged in? send them straight to the right dashboard instead of showing the form again
if(isset($_SESSION['user_id'])){
    header("location:dashboard.php");
    exit();
}

$loginError="";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $mail=$_POST['email'];
    $pass=$_POST['password'];

    $sel="SELECT * FROM `users` WHERE email=?";
    $querylogin=$connection->prepare($sel);
    $querylogin->bind_param("s",$mail);
    $querylogin->execute();
    $result=$querylogin->get_result();
    $user=$result->fetch_assoc();

    if($user){

        $pass_check=password_verify($pass,$user['password']);
        if($pass_check){

            $update="UPDATE `users` SET last_login=NOW() WHERE id=?";
            $loginupdate=$connection->prepare($update);
            $loginupdate->bind_param("i",$user['id']);
            $loginupdate->execute();
            $_SESSION['user_id']=$user['id'];

            if($user['role']=="admin"){
                header("location:admin_dashboard.php");
                exit();
            }
            else{
                header("location:dashboard.php");
                exit();
            }
        }
        else{
            $loginError="Incorrect password. Please try again.";
        }
    }
    else{
        $loginError="No account found with that email.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="scene">
    <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
        <div class="auth-card">
 
            <span class="eyebrow">Access Portal</span>
            <h1 class="card-title">Welcome Back</h1>
            <p class="card-sub">Sign in with your badge, or register a new one.</p>
 
            <div class="tab-bar" role="tablist">
                <button type="button" class="tab-btn" data-tab="register">Register</button>
                <button type="button" class="tab-btn active" data-tab="login">Sign in</button>
                <span class="tab-indicator"></span>
            </div>
 
            <form class="form-panel hidden" data-panel="register" action="register.php" method="POST">
                <div class="field">
                    <label for="reg-name">Name</label>
                    <input id="reg-name" type="text" name="name" placeholder="Your full name" required>
                </div>
                <div class="field">
                    <label for="reg-email">Email</label>
                    <input id="reg-email" type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="field">
                    <label for="reg-password">Password</label>
                    <input id="reg-password" type="password" name="password" placeholder="Create a password" required>
                </div>
                <button type="submit" class="btn-primary">Create account</button>
            </form>
 
            <form class="form-panel" data-panel="login" method="POST">
                <?php if($loginError !== ""){ ?>
                    <p class="form-note"><?php echo htmlspecialchars($loginError); ?></p>
                <?php } ?>
                <div class="field">
                    <label for="login-email">Email</label>
                    <input id="login-email" type="email" name="email" placeholder="Enter email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>
                <div class="field">
                    <label for="login-password">Password</label>
                    <input id="login-password" type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
        </div>
    </div>
 
    <script>
    (function(){
        const tabs = document.querySelectorAll('.tab-btn');
        const indicator = document.querySelector('.tab-indicator');
        const panels = document.querySelectorAll('.form-panel');
 
        function activate(index){
            tabs.forEach((t,i)=>t.classList.toggle('active', i===index));
            indicator.style.transform = `translateX(${index*100}%)`;
            panels.forEach(p=>{
                p.classList.toggle('hidden', p.dataset.panel !== tabs[index].dataset.tab);
            });
        }
 
        tabs.forEach((tab,i)=>tab.addEventListener('click', ()=>activate(i)));
 
        const initial = [...tabs].findIndex(t=>t.classList.contains('active'));
        activate(initial === -1 ? 0 : initial);
    })();
    </script>
    <script src="theme.js"></script>
</body>
</html>