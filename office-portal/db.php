<?php
require 'config.php';
$connection=mysqli_connect($host,$user,$password,$db,4306);
if(!$connection){
    die("connection failed" . mysqli_connect_error());
}
?>