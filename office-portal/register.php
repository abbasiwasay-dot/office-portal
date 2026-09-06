<?php
session_start();
require "db.php";

if(isset($_SESSION['user_id'])){
    header("location:dashboard.php");
    exit();
}

$registerError="";
$name=$mail="";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $name=trim($_POST['name'] ?? '');
    $mail=trim($_POST['email'] ?? '');
    $pass=$_POST['password'] ?? '';

    if($name==="" || $mail==="" || $pass===""){
        $registerError="Please fill in every field.";
    }
    else{

        $hashed=password_hash($pass,PASSWORD_DEFAULT);

        $sql="INSERT INTO `users`(`name`,`email`,`password`) VALUES(?,?,?)";
        $query=$connection->prepare($sql);
        $query->bind_param('sss',$name,$mail,$hashed);

        if($query->execute()){

            $_SESSION["user_id"]=$connection->insert_id;
            header("location:dashboard.php");
            exit();

        }
        else if($connection->errno==1062){
            $registerError="That email is already registered. Try signing in instead.";
        }
        else{
            $registerError="Something went wrong while creating your account. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>(function(){if(localStorage.getItem('op-theme')==='light'){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="scene">
    <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Toggle light/dark mode" title="Toggle theme">🌙</button>
        <div class="auth-card">
 
            <span class="eyebrow">Access Portal · 001</span>
            <h1 class="card-title">Member Access</h1>
            <p class="card-sub">Create a new badge, or sign in with one you already have.</p>
 
            <div class="tab-bar" role="tablist">
                <button type="button" class="tab-btn active" data-tab="register">Register</button>
                <button type="button" class="tab-btn" data-tab="login">Sign in</button>
                <span class="tab-indicator"></span>
            </div>
 
            <form class="form-panel" data-panel="register" action="register.php" method="POST">

                <?php if($registerError !== ""){ ?>
                    <p class="form-note"><?php echo htmlspecialchars($registerError); ?></p>
                <?php } ?>

                <div class="field">
                    <label for="reg-name">Name</label>
                    <input id="reg-name" type="text" name="name" placeholder="Your full name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>

                <div class="field">
                    <label for="reg-email">Email</label>
                    <input id="reg-email" type="email" name="email" placeholder="you@example.com" value="<?php echo htmlspecialchars($mail); ?>" required>
                </div>

                <div class="field">
                    <label for="reg-password">Password</label>
                    <input id="reg-password" type="password" name="password" placeholder="Create a password" required>
                </div>


                <button type="submit" class="btn-primary">Create account</button>

            </form>
 
            <form class="form-panel hidden" data-panel="login" action="login.php" method="POST">

                <div class="field">
                    <label for="login-email">Email</label>
                    <input id="login-email" type="email" name="email" placeholder="Enter email" required>
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