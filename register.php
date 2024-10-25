<?php
require_once 'config.php';

$username = "";
$email = "";
$password = "";
$balance = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, balance) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $username, $email, $hashed_password, $balance);

        if ($stmt->execute()) {
            echo '<div class="registration-success">
                        <h2>Регистрация успешна!</h2>
                        <p>Теперь ты <a href="login.php">Войти</a> и использовать аккаунт.</p>
                    </div>';
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Регистрация</title>
</head>
<body>
<div class="container">
    <h2>Регистрация</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label for="username">Имя:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        <br>
        <label for="email">Почта:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        <br>
        <label for="password">Пароль:</label>
        <input type="password" name="password" required>
        <br>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войди здесь</a>.</p>
</div>
</body>
</html>
