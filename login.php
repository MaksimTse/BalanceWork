<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: cabinet.php');
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Вход</h1>

    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Почта:</label>
        <input type="email" name="email" id="email" required>
        <br>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>
        <br>

        <button type="submit">Логин</button>
    </form>

    <p>Нет аккаунта? <a href="register.php">Зарегистрируйся</a></p>
</div>
</body>
</html>
