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
    $recipientEmail = isset($_POST['recipient_email']) ? $_POST['recipient_email'] : '';
    $sendAmount = isset($_POST['send_amount']) ? $_POST['send_amount'] : 0;

    $query = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();

    if ($sendAmount > $balance) {
        http_response_code(400); // Bad Request
        echo json_encode(["message" => "Недостаточно средств."]);
        exit();
    }

    $recipientQuery = "SELECT id, balance FROM users WHERE email = ?";
    $recipientStmt = $conn->prepare($recipientQuery);
    $recipientStmt->bind_param("s", $recipientEmail);
    $recipientStmt->execute();
    $recipientStmt->bind_result($recipientId, $recipientBalance);
    $recipientStmt->fetch();
    $recipientStmt->close();

    if ($recipientId) {
        $newSenderBalance = $balance - $sendAmount;
        $newRecipientBalance = $recipientBalance + $sendAmount;

        $updateSenderQuery = "UPDATE users SET balance = ? WHERE id = ?";
        $updateSenderStmt = $conn->prepare($updateSenderQuery);
        $updateSenderStmt->bind_param("di", $newSenderBalance, $userId);
        $updateSenderStmt->execute();
        $updateSenderStmt->close();

        $updateRecipientQuery = "UPDATE users SET balance = ? WHERE id = ?";
        $updateRecipientStmt = $conn->prepare($updateRecipientQuery);
        $updateRecipientStmt->bind_param("di", $newRecipientBalance, $recipientId);
        $updateRecipientStmt->execute();
        $updateRecipientStmt->close();

        echo json_encode(["message" => "Сресдтва успешно отправлены!.", "new_sender_balance" => $newSenderBalance, "new_recipient_balance" => $newRecipientBalance]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(["message" => "Получатель не найден."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["message" => "Только ПОСТ запросы."]);
}

$conn->close();
?>
