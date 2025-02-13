<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

$servername = "localhost";
$username = "----";
$password = "----";
$dbname = "----";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header("Access-Control-Allow-Origin: *"); 
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}
