<?php
$host     = "localhost";
$username = "root";       // default XAMPP username
$password = "";           // default XAMPP password is empty
$database = "barbershop";  // the name you chose

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>