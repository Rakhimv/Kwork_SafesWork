<?php

require_once "config.php";

$data = json_decode(file_get_contents('php://input'), true);

$login = $data['login'];
$password = $data['password'];

$sql = "SELECT * FROM users WHERE login = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'role' => $user['role']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Неправильный логин или пароль!']);
}

$stmt->close();
$conn->close();
