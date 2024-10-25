<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: api_login.php");
    exit();
}

require_once 'config.php';

$userId = $_SESSION['user_id'];

function getCurrentBalance($conn, $userId) {
    $query = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
    return $balance;
}

$balance = getCurrentBalance($conn, $userId);

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Личный кабинет</title>
    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('show-modal');
        }

        function submitAddBalanceForm() {
            const form = document.getElementById('addBalanceForm');
            const formData = new FormData(form);

            fetch('add_balance.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.new_balance) {
                        document.querySelector('#balanceAmount').innerText = `${data.new_balance.toFixed(2)} €`;
                        toggleModal('addBalanceModal');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function submitSendMoneyForm() {
            const form = document.getElementById('sendMoneyForm');
            const formData = new FormData(form);

            fetch('send_money.php', {
                method: 'POST',
                body: formData,
                credentials: 'include'
            })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.new_sender_balance) {
                        document.querySelector('#balanceAmount').innerText = `${data.new_sender_balance.toFixed(2)} €`;
                        toggleModal('sendMoneyModal');
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
</head>
<body>
<div class="container">
    <header>
        <h2>Личный кабинет</h2>
    </header>

    <section class="balance-section">
        <p>Ваш текущий баланс: <strong id="balanceAmount"><?php echo number_format($balance, 2); ?> €</strong></p>
    </section>

    <section class="actions-section">
        <button onclick="toggleModal('addBalanceModal')">Пополнить баланс</button>
        <button onclick="toggleModal('sendMoneyModal')">Отправить деньги</button>
    </section>

    <div id="addBalanceModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="toggleModal('addBalanceModal')">&times;</span>
            <h3>Пополнить баланс</h3>
            <form id="addBalanceForm">
                <div class="input-group">
                    <label for="amount">Сумма для пополнения:</label>
                    <input type="number" name="amount" required>
                </div>
                <button type="button" onclick="submitAddBalanceForm()">Пополнить</button>
            </form>
        </div>
    </div>

    <div id="sendMoneyModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="toggleModal('sendMoneyModal')">&times;</span>
            <h3>Отправить деньги</h3>
            <form id="sendMoneyForm">
                <div class="input-group">
                    <label for="recipient_email">Почта получателя:</label>
                    <input type="email" name="recipient_email" required>
                </div>
                <div class="input-group">
                    <label for="send_amount">Сумма для отправки:</label>
                    <input type="number" name="send_amount" required>
                </div>
                <button type="button" onclick="submitSendMoneyForm()">Отправить</button>
            </form>
        </div>
    </div>

    <footer class="logout-section">
        <form action="logout.php" method="POST">
            <button type="submit">Выйти</button>
        </form>
    </footer>
</div>
</body>
</html>

