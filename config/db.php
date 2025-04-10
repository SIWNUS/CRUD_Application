<?php 

ini_set('display_errors', 1);
error_reporting(E_ALL);


$host = "localhost";
$dbname = "registerdb";
$user = "root";
$pass = "";
$port = 3306;

try {
    $dsn = "mysql:host=$host; port=$port; dbname=$dbname";
    $conn = new PDO($dsn, $user, $pass);
    $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection established successfully!";
} catch (PDOException $e) {
    echo "Connection Failed: ". $e->getMessage();
    exit;
};

?>