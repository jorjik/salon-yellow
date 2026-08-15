<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$rate_limit_file = __DIR__ . '/../data/rate_limit.json';
$attempts = file_exists($rate_limit_file) ? json_decode(file_get_contents($rate_limit_file), true) : [];
$attempts = is_array($attempts) ? $attempts : [];

$now = time();
foreach ($attempts as $a_ip => $data) {
    if ($now - $data['time'] > 900) { // 15 минут
        unset($attempts[$a_ip]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $current_attempts = $attempts[$ip]['count'] ?? 0;

    if ($current_attempts >= 5) {
        $error = 'Слишком много попыток входа. Попробуйте через 15 минут.';
    } else {
        if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
            $_SESSION['admin_auth'] = true;
            unset($attempts[$ip]);
            file_put_contents($rate_limit_file, json_encode($attempts, JSON_PRETTY_PRINT));
            header('Location: index.php');
            exit;
        } else {
            $attempts[$ip] = [
                'count' => $current_attempts + 1,
                'time' => $now
            ];
            file_put_contents($rate_limit_file, json_encode($attempts, JSON_PRETTY_PRINT));
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Вход в админ-панель</title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        body {
            font-family: sans-serif;
            background: #fff8e1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(230, 161, 0, 0.18);
            width: 300px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            color: #1f1a1d;
        }

        .field {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #4a3f44;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ffe082;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #e6a100;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        button:hover {
            background: #cc8e00;
        }

        .error {
            color: #a23a2a;
            font-size: 13px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <h1>VELVET CMS Admin</h1>
        <?php if ($error): ?>
            <div class="error">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="field">
                <label>Логин</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="field">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>

</html>
