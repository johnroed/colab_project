<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "colab_project";

// connect to the database
$conn = new mysqli($host, $username, $password, $database);

// check if connection works
if ($conn->connect_error) {
    die("Sorry, can't connect to the database: " . $conn->connect_error);
}
?>
