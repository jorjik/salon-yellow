<?php
require_once __DIR__ . '/auth.php';
require_auth();

$services = load_json('services.json');
$message = '';

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if (isset($services[$id])) {
        array_splice($services, $id, 1);
        save_json('services.json', $services);
        header('Location: services.php?deleted=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_service = [
        'title' => $_POST['title'] ?? '',
        'description' => $_POST['description'] ?? '',
        'price' => $_POST['price'] ?? ''
    ];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int) $_POST['id'];
        $services[$id] = $new_service;
    } else {
        $services[] = $new_service;
    }

    if (save_json('services.json', $services)) {
        header('Location: services.php?saved=1');
        exit;
    }
}

$edit_service = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if (isset($services[$id])) {
        $edit_service = $services[$id];
        $edit_service['id'] = $id;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Управление услугами</title>
    <style>
        body {
            font-family: sans-serif;
            background: #fdfbf3;
            margin: 0;
            color: #1f1a1d;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            height: 100vh;
            background: #1f1a1d;
            color: #fff;
            padding: 30px 0;
        }

        .sidebar-brand {
            padding: 0 30px;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 40px;
            color: #ffb300;
        }

        .nav-link {
            display: block;
            padding: 12px 30px;
            color: #d9cfc1;
            text-decoration: none;
            transition: 0.2s;
        }

        .nav-link:hover {
            background: #332d28;
            color: #fff;
        }

        .nav-link.active {
            background: #ffb300;
            color: #1f1a1d;
        }

        .content {
            margin-left: 240px;
            padding: 40px;
            max-width: 1000px;
        }

        h1 {
            margin-top: 0;
            border-bottom: 2px solid #f2e8cf;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(230, 161, 0, 0.08);
            margin-bottom: 40px;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f2e8cf;
        }

        .table th {
            background: #fffaf0;
            color: #80766b;
            font-size: 13px;
            text-transform: uppercase;
        }

        .form-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(230, 161, 0, 0.08);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ffe082;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            border: none;
        }

        .btn-add {
            background: #cc8e00;
            color: #fff;
            padding: 12px 25px;
            font-size: 16px;
        }

        .btn-edit {
            background: #6b4423;
            color: #fff;
            margin-right: 5px;
        }

        .btn-delete {
            background: #a23a2a;
            color: #fff;
        }

        .badge-price {
            background: #fff3e0;
            color: #cc8e00;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }

        .btn-logout {
            position: absolute;
            bottom: 30px;
            left: 30px;
            color: #80766b;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand">VELVET CMS</div>
        <a href="index.php" class="nav-link">Дашборд</a>
        <a href="settings.php" class="nav-link">Настройки сайта</a>
        <a href="services.php" class="nav-link active">Услуги</a>
        <a href="reviews.php" class="nav-link">Отзывы</a>
        <a href="../index.php" target="_blank" class="nav-link">Перейти на сайт ↗</a>
        <a href="logout.php" class="btn-logout">Выйти</a>
    </div>
    <div class="content">
        <h1>Управление услугами</h1>

        <?php if (isset($_GET['saved'])): ?>
            <div style="background: #e7f3e1; color: #4a6b3a; padding: 15px; margin-bottom: 20px; border-radius: 4px;">Услуга
                сохранена!</div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Описание</th>
                    <th>Цена</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $id => $svc): ?>
                    <tr>
                        <td><strong>
                                <?php echo h($svc['title']); ?>
                            </strong></td>
                        <td style="font-size: 14px; color: #80766b;">
                            <?php echo h($svc['description']); ?>
                        </td>
                        <td><span class="badge-price">
                                <?php echo h($svc['price']); ?>
                            </span></td>
                        <td>
                            <a href="?edit=<?php echo $id; ?>" class="btn btn-edit">Изменить</a>
                            <a href="?delete=<?php echo $id; ?>" class="btn btn-delete"
                                onclick="return confirm('Удалить эту услугу?')">X</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="form-box">
            <h2>
                <?php echo $edit_service ? 'Редактировать услугу' : 'Добавить услугу'; ?>
            </h2>
            <form method="post">
                <?php if ($edit_service): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_service['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Название</label>
                    <input type="text" name="title"
                        value="<?php echo $edit_service ? h($edit_service['title']) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description"
                        required><?php echo $edit_service ? h($edit_service['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Цена (текстом)</label>
                    <input type="text" name="price"
                        value="<?php echo $edit_service ? h($edit_service['price']) : ''; ?>"
                        placeholder="напр. от 8 500 ₸" required>
                </div>
                <button type="submit" class="btn btn-add">
                    <?php echo $edit_service ? 'Сохранить изменения' : 'Добавить услугу'; ?>
                </button>
                <?php if ($edit_service): ?>
                    <a href="services.php" style="margin-left: 15px; color: #80766b;">Отмена</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>

</html>
