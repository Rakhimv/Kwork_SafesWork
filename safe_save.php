<?php
require 'config.php';
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    // Функция для проверки существования объекта с таким же номером
    // При обновлении исключаем текущую запись (если id уже есть)
    function isNumberExists($conn, $number, $currentId = 0) {
        if ($currentId > 0) {
            $stmt = $conn->prepare("SELECT id FROM safes WHERE number = ? AND id != ?");
            $stmt->bind_param("si", $number, $currentId);
        } else {
            $stmt = $conn->prepare("SELECT id FROM safes WHERE number = ?");
            $stmt->bind_param("s", $number);
        }
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    if (isset($data['id']) && $data['id'] != 0) {
        // Если id существует и не равно 0, обновляем данные
        $stmt = $conn->prepare("SELECT * FROM safes WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Если объект найден, получаем текущие значения
            $stmt->bind_result($id, $image, $number, $name, $description, $workshop, $year, $place, $material, $author, $size, $casting);
            $stmt->fetch();
            $stmt->close();

            // Перед обновлением проверяем, существует ли другой объект с таким же номером
            if (isset($data['number']) && isNumberExists($conn, $data['number'], $data['id'])) {
                echo json_encode(['status' => 'error', 'message' => 'Экспонат с таким номером уже существует']);
                exit;
            }

            // Если фото приходит как URL, содержащий 'object', не меняем фото
            if (isset($data['image']) && strpos($data['image'], 'object') === false && !file_exists('./objects/' . $data['image'])) {
                // Если фото пришло как image (base64), сохраняем новое изображение
                $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $data['image']));
                $imageName = uniqid() . '.png'; // Генерация уникального имени файла
                $imagePath = './objects/' . $imageName;

                if (file_put_contents($imagePath, $imageData) !== false) {
                    $image = $imageName; // Обновляем имя файла изображения
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Ошибка при сохранении изображения']);
                    exit;
                }
            }

            // Обновляем все поля, которые не равны null
            $number = isset($data['number']) ? $data['number'] : $number;
            $name = isset($data['name']) ? $data['name'] : $name;
            $description = isset($data['description']) ? $data['description'] : $description;
            $workshop = isset($data['dop']['workshop']) ? $data['dop']['workshop'] : $workshop;
            $year = isset($data['dop']['year']) ? $data['dop']['year'] : $year;
            $place = isset($data['dop']['place']) ? $data['dop']['place'] : $place;
            $material = isset($data['dop']['material']) ? $data['dop']['material'] : $material;
            $author = isset($data['dop']['author']) ? $data['dop']['author'] : $author;
            $size = isset($data['dop']['size']) ? $data['dop']['size'] : $size;
            $casting = isset($data['dop']['casting']) ? $data['dop']['casting'] : $casting;

            // Обновление данных в базе
            $updateStmt = $conn->prepare("UPDATE safes SET image = ?, number = ?, name = ?, description = ?, workshop = ?, year = ?, place = ?, material = ?, author = ?, size = ?, casting = ? WHERE id = ?");
            $updateStmt->bind_param(
                "sssssssssssi",
                $image, // Используем обновленное имя изображения
                $number,
                $name,
                $description,
                $workshop,
                $year,
                $place,
                $material,
                $author,
                $size,
                $casting,
                $data['id']
            );

            if ($updateStmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Экспонат успешно обновлен']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $updateStmt->error]);
            }

            $updateStmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Экспонат с таким ID не найден']);
        }
    } else {
        // Если id = 0, то это новый экспонат

        // Проверка на существование объекта с таким же номером
        if (isset($data['number']) && isNumberExists($conn, $data['number'])) {
            echo json_encode(['status' => 'error', 'message' => 'Экспонат с таким номером уже существует']);
            exit;
        }

        // Генерация уникального имени файла
        if (isset($data['image']) && strpos($data['image'], 'object') === false) {
            // Если фото пришло как image, сохраняем изображение
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $data['image']));
            $imageName = uniqid() . '.png'; // Генерация уникального имени файла
            $imagePath = './objects/' . $imageName;

            if (file_put_contents($imagePath, $imageData) === false) {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка при сохранении изображения']);
                exit;
            }
        } else {
            // Если фото не пришло или пришел URL с "object", ставим старое значение фото или null
            $imageName = null; // Это можно заменить на значение по умолчанию, если нужно
        }

        // Вставка нового экспоната
        $stmt = $conn->prepare("INSERT INTO safes (image, number, name, description, workshop, year, place, material, author, size, casting) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param(
                "sssssssssss",
                $imageName, // Сохраняем только имя файла
                $data['number'],
                $data['name'],
                $data['description'],
                $data['dop']['workshop'],
                $data['dop']['year'],
                $data['dop']['place'],
                $data['dop']['material'],
                $data['dop']['author'],
                $data['dop']['size'],
                $data['dop']['casting']
            );

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Экспонат успешно сохранен']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Ошибка базы данных: ' . $stmt->error]);
            }

            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Ошибка при подготовке запроса']);
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Некорректные данные']);
}

$conn->close();
?>
