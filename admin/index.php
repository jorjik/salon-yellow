<?php
require_once __DIR__ . '/auth.php';
require_auth();
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Панель управления</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(230, 161, 0, 0.08);
        }

        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 14px;
            text-transform: uppercase;
            color: #80766b;
        }

        .stat-card div {
            font-size: 32px;
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

        .btn-logout:hover {
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-brand">VELVET CMS</div>
        <a href="index.php" class="nav-link active">Дашборд</a>
        <a href="settings.php" class="nav-link">Настройки сайта</a>
        <a href="services.php" class="nav-link">Услуги</a>
        <a href="reviews.php" class="nav-link">Отзывы</a>
        <a href="../index.php" target="_blank" class="nav-link">Перейти на сайт ↗</a>
        <a href="logout.php" class="btn-logout">Выйти</a>
    </div>
    <div class="content">
        <h1>Добро пожаловать в админ-панель</h1>
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Услуг</h3>
                <div>
                    <?php echo count(load_json('services.json')); ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Отзывов</h3>
                <div>
                    <?php echo count(load_json('reviews.json')); ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Заявок</h3>
                <div>
                    <?php
                    $leads = load_json('leads.json');
                    echo count($leads);
                    ?>
                </div>
            </div>
        </div>

        <div
            style="margin-top: 40px; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(230,161,0,0.08);">
            <h2 style="margin-top: 0; color: #1f1a1d;">Последние заявки с сайта</h2>
            <?php
            // Сортируем заявки (новые сверху)
            $recent_leads = array_reverse($leads);
            $recent_leads = array_slice($recent_leads, 0, 20); // Показываем последние 20

            if (empty($recent_leads)):
                ?>
                <p style="color: #80766b;">Пока нет ни одной заявки.</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr>
                            <th
                                style="text-align: left; padding: 12px; border-bottom: 2px solid #f2e8cf; color: #80766b; font-size: 13px; text-transform: uppercase;">
                                Дата</th>
                            <th
                                style="text-align: left; padding: 12px; border-bottom: 2px solid #f2e8cf; color: #80766b; font-size: 13px; text-transform: uppercase;">
                                Имя</th>
                            <th
                                style="text-align: left; padding: 12px; border-bottom: 2px solid #f2e8cf; color: #80766b; font-size: 13px; text-transform: uppercase;">
                                Телефон</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_leads as $lead): ?>
                            <tr>
                                <td
                                    style="padding: 15px 12px; border-bottom: 1px solid #f2e8cf; color: #4a423a; font-size: 14px;">
                                    <?php echo h($lead['date']); ?>
                                </td>
                                <td style="padding: 15px 12px; border-bottom: 1px solid #f2e8cf; font-weight: bold;">
                                    <?php echo h($lead['name']); ?>
                                </td>
                                <td style="padding: 15px 12px; border-bottom: 1px solid #f2e8cf;">
                                    <a href="tel:<?php echo h(preg_replace('/[^0-9+]/', '', $lead['phone'])); ?>"
                                        style="color: #cc8e00; text-decoration: none;">
                                        <?php echo h($lead['phone']); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
