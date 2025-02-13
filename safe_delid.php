<?php
require 'config.php';

// Проверяем, передан ли параметр id
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID объекта не указан']);
    exit;
}

$id = (int)$_GET['id'];

// Подготавливаем SQL-запрос для удаления данных сейфа по его id
$sql = "DELETE FROM safes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

// Выполняем запрос
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Объект успешно удален']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Объект с указанным ID не найден']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка при удалении объекта']);
}

$stmt->close();
$conn->close();
?>
