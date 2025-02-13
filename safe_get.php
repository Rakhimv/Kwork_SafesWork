<?php
require 'config.php';

$block = isset($_GET['block']) ? (int)$_GET['block'] : 1;
$offset = ($block - 1) * 6;
$search = isset($_GET['search']) ? $_GET['search'] : ''; 

if ($search) {
    $sqlCount = "SELECT COUNT(*) as total FROM safes WHERE number LIKE ?";
    $stmtCount = $conn->prepare($sqlCount);
    $searchParam = $search . "%";
    $stmtCount->bind_param("s", $searchParam);
    $stmtCount->execute();
    $resultCount = $stmtCount->get_result();
    $total = $resultCount->fetch_assoc()['total'];

    $sql = "SELECT id, number, image FROM safes WHERE number LIKE ? ORDER BY id DESC LIMIT 6 OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $searchParam, $offset);
} else {
    $sqlCount = "SELECT COUNT(*) as total FROM safes";
    $resultCount = $conn->query($sqlCount);
    $total = $resultCount->fetch_assoc()['total'];

    $sql = "SELECT id, number, image FROM safes ORDER BY id DESC LIMIT 6 OFFSET $offset";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $safes = [];

    while ($row = $result->fetch_assoc()) {
        $safes[] = ['id' => $row['id'], 'number' => $row['number'], 'image' => $row['image']];
    }

    echo json_encode(['status' => 'success', 'data' => $safes, 'total' => $total]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Нет данных']);
}

$stmt->close();
$conn->close();
?>
