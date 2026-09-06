<?php
$host="localhost";
$user="root";
$password="";
$db="login_system";

$connection=mysqli_connect($host,$user,$password,$db,4306);
if(!$connection){
    die("connection failed" . mysqli_connect_error());
}
?>