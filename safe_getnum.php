<?php
require 'config.php';

// Проверяем, передан ли параметр id
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID объекта не указан']);
    exit;
}

$id = (int)$_GET['id'];

// Подготавливаем SQL-запрос для получения данных сейфа по его id
$sql = "SELECT * FROM safes WHERE number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Проверяем, есть ли данные
if ($result->num_rows > 0) {
    $safe = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $safe]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Объект с указанным ID не найден']);
}

$stmt->close();
$conn->close();
?>