<?php
require_once 'config.php';
header("Content-Type: application/json");

$request_method = $_SERVER['REQUEST_METHOD'];

function registerUser($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = htmlspecialchars($data['username']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["error" => "Имя пользователя уже существует."]);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, balance) VALUES (?, ?, ?)");
    $balance = 0;
    $stmt->bind_param("ssi", $username, $password, $balance);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Пользователь успешно зарегистрирован.", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["error" => "Ошибка регистрации пользователя."]);
    }

    $stmt->close();
}

function loginUser($conn) {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = htmlspecialchars($data['username']);
    $password = $data['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["error" => "Имя пользователя или пароль неверны."]);
        return;
    }

    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(["message" => "Вход успешный."]);
    } else {
        echo json_encode(["error" => "Имя пользователя или пароль неверны."]);
    }

    $stmt->close();
}

switch ($request_method) {
    case 'POST':
        if (isset($_GET['action']) && $_GET['action'] == 'register') {
            registerUser($conn);
        } elseif (isset($_GET['action']) && $_GET['action'] == 'login') {
            loginUser($conn);
        } else {
            echo json_encode(["error" => "Недопустимое действие."]);
        }
        break;
    default:
        echo json_encode(["error" => "Неверный метод запроса."]);
        break;
}

$conn->close();
?>
