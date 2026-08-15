<?php
require_once __DIR__ . '/auth.php';
require_auth();

$reviews = load_json('reviews.json');

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if (isset($reviews[$id])) {
        array_splice($reviews, $id, 1);
        save_json('reviews.json', $reviews);
        header('Location: reviews.php?deleted=1');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_review = [
        'name' => $_POST['name'] ?? '',
        'stars' => $_POST['stars'] ?? '★★★★★',
        'text' => $_POST['text'] ?? '',
        'date' => $_POST['date'] ?? ''
    ];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $id = (int) $_POST['id'];
        $reviews[$id] = $new_review;
    } else {
        $reviews[] = $new_review;
    }

    if (save_json('reviews.json', $reviews)) {
        header('Location: reviews.php?saved=1');
        exit;
    }
}

$edit_review = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    if (isset($reviews[$id])) {
        $edit_review = $reviews[$id];
        $edit_review['id'] = $id;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Управление отзывами</title>
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

        .review-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .review-item {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(230, 161, 0, 0.08);
            position: relative;
        }

        .review-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stars {
            color: #f8b400;
            margin-bottom: 10px;
        }

        .review-text {
            font-size: 14px;
            color: #4a423a;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .review-date {
            font-size: 12px;
            color: #80766b;
        }

        .actions {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            font-size: 12px;
            border: none;
        }

        .btn-edit {
            background: #fff3e0;
            color: #cc8e00;
        }

        .btn-delete {
            background: #feebeb;
            color: #a23a2a;
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
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ffe082;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-add {
            background: #cc8e00;
            color: #fff;
            padding: 12px 25px;
            font-size: 16px;
            width: 100%;
            margin-top: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
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
        <a href="services.php" class="nav-link">Услуги</a>
        <a href="reviews.php" class="nav-link active">Отзывы</a>
        <a href="../index.php" target="_blank" class="nav-link">Перейти на сайт ↗</a>
        <a href="logout.php" class="btn-logout">Выйти</a>
    </div>
    <div class="content">
        <h1>Управление отзывами</h1>

        <div class="review-list">
            <?php foreach ($reviews as $id => $rev): ?>
                <div class="review-item">
                    <div class="actions">
                        <a href="?edit=<?php echo $id; ?>" class="btn btn-edit">Ред.</a>
                        <a href="?delete=<?php echo $id; ?>" class="btn btn-delete"
                            onclick="return confirm('Удалить?')">X</a>
                    </div>
                    <div class="review-name">
                        <?php echo h($rev['name']); ?>
                    </div>
                    <div class="stars">
                        <?php echo h($rev['stars']); ?>
                    </div>
                    <div class="review-text">
                        <?php echo h($rev['text']); ?>
                    </div>
                    <div class="review-date">
                        <?php echo h($rev['date']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-box" style="max-width: 600px;">
            <h2>
                <?php echo $edit_review ? 'Редактировать отзыв' : 'Добавить отзыв'; ?>
            </h2>
            <form method="post">
                <?php if ($edit_review): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_review['id']; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Имя клиента</label>
                    <input type="text" name="name" value="<?php echo $edit_review ? h($edit_review['name']) : ''; ?>"
                        required>
                </div>
                <div class="form-group">
                    <label>Звезды</label>
                    <select name="stars">
                        <option value="★★★★★" <?php echo ($edit_review && $edit_review['stars'] === '★★★★★') ? 'selected' : ''; ?>>5 звезд</option>
                        <option value="★★★★" <?php echo ($edit_review && $edit_review['stars'] === '★★★★') ? 'selected' : ''; ?>>4 звезды</option>
                        <option value="★★★" <?php echo ($edit_review && $edit_review['stars'] === '★★★') ? 'selected' : ''; ?>>3 звезды</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Текст отзыва</label>
                    <textarea name="text" required
                        style="height: 80px;"><?php echo $edit_review ? h($edit_review['text']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>Дата (текстом)</label>
                    <input type="text" name="date" value="<?php echo $edit_review ? h($edit_review['date']) : ''; ?>"
                        placeholder="напр. 2 недели назад" required>
                </div>
                <button type="submit" class="btn-add">
                    <?php echo $edit_review ? 'Сохранить изменения' : 'Добавить отзыв'; ?>
                </button>
                <?php if ($edit_review): ?>
                    <div style="text-align: center; margin-top: 10px;"><a href="reviews.php"
                            style="color: #80766b;">Отмена</a></div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>

</html>
