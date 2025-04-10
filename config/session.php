<?php 

session_start();

if (!isset($_SESSION['user_id'])){
    echo "<script>alert('You are not authorised to view this page.');</script>";
    header("Location: index.php");
};

?>