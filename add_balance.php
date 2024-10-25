<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["message" => "Пользователь не вошел."]);
    exit();
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = isset($_POST['amount']) ? $_POST['amount'] : 0;

    if (is_numeric($amount) && $amount > 0) {
        $query = "SELECT balance FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($balance);
        $stmt->fetch();
        $stmt->close();

        $newBalance = $balance + $amount;

        $updateQuery = "UPDATE users SET balance = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("di", $newBalance, $userId);
        $updateStmt->execute();
        $updateStmt->close();

        echo json_encode(["message" => "Баланс обновлен!.", "new_balance" => $newBalance]);
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Неправильное колличество."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Только ПОСТ запросы."]);
}

$conn->close();
?>
