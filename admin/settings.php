<?php
require_once __DIR__ . '/auth.php';
require_auth();

$settings = load_json('settings.json');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Основные настройки
    $settings['site']['title'] = $_POST['site_title'] ?? '';
    $settings['site']['description'] = $_POST['site_description'] ?? '';
    $settings['site']['name_short'] = $_POST['name_short'] ?? '';
    $settings['site']['name_legal'] = $_POST['name_legal'] ?? '';
    $settings['site']['url'] = rtrim($_POST['site_url'] ?? '', '/') . '/';

    $settings['phone']['display'] = $_POST['phone_display'] ?? '';
    $settings['phone']['digits'] = preg_replace('/[^0-9]/', '', $_POST['phone_display'] ?? '');
    $settings['phone']['e164'] = '+' . $settings['phone']['digits'];

    $settings['whatsapp']['message'] = $_POST['whatsapp_message'] ?? '';
    $settings['og_image'] = $_POST['og_image'] ?? '';

    $settings['salon']['schema_description'] = $_POST['schema_description'] ?? '';
    $settings['salon']['address_line'] = $_POST['address_line'] ?? '';
    $settings['salon']['address_street'] = $_POST['address_street'] ?? '';
    $settings['salon']['address_city'] = $_POST['address_city'] ?? '';
    $settings['salon']['address_country'] = $_POST['address_country'] ?? '';
    $settings['salon']['opens'] = $_POST['opens'] ?? '';
    $settings['salon']['closes'] = $_POST['closes'] ?? '';
    $settings['salon']['hours_label'] = $_POST['hours_label'] ?? '';
    $settings['salon']['google_maps_url'] = $_POST['google_maps_url'] ?? '';
    $settings['salon']['map_lat'] = $_POST['map_lat'] ?? '';
    $settings['salon']['map_lng'] = $_POST['map_lng'] ?? '';
    $settings['salon']['map_zoom'] = $_POST['map_zoom'] ?? '';

    // Hero секция
    $settings['hero']['badge'] = $_POST['hero_badge'] ?? '';
    $settings['hero']['title'] = $_POST['hero_title'] ?? '';
    $settings['hero']['description'] = $_POST['hero_description'] ?? '';
    $settings['hero']['benefits_title'] = $_POST['hero_benefits_title'] ?? '';

    // Преимущества (список)
    if (isset($_POST['benefits']) && is_array($_POST['benefits'])) {
        $settings['hero']['benefits'] = array_values(array_filter(array_map('trim', $_POST['benefits']), 'strlen'));
    }

    // Спецпредложение (Offer)
    $settings['offer']['show'] = isset($_POST['offer_show']);
    $settings['offer']['title'] = $_POST['offer_title'] ?? '';
    $settings['offer']['description'] = $_POST['offer_description'] ?? '';

    // Почта
    $settings['mail']['to'] = $_POST['mail_to'] ?? '';
    $settings['mail']['subject'] = $_POST['mail_subject'] ?? '';
    $settings['mail']['from_label'] = $_POST['mail_from_label'] ?? '';

    // Футер
    $settings['footer']['year'] = (int) ($_POST['footer_year'] ?? 0);

    if (save_json('settings.json', $settings)) {
        $message = 'Настройки успешно сохранены!';
    } else {
        $message = 'Ошибка при сохранении.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Настройки сайта</title>
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
            max-width: 800px;
        }

        h1 {
            margin-top: 0;
            border-bottom: 2px solid #f2e8cf;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        h2 {
            border-left: 4px solid #ffb300;
            padding-left: 15px;
            margin: 40px 0 20px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
            color: #4a3f44;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ffe082;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 15px;
        }

        textarea {
            height: 80px;
            resize: vertical;
        }

        .benefit-item {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .btn-save {
            background: #cc8e00;
            color: #fff;
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            position: sticky;
            bottom: 20px;
            box-shadow: 0 5px 15px rgba(230, 161, 0, 0.25);
        }

        .btn-save:hover {
            background: #b37e00;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #e7f3e1;
            color: #4a6b3a;
            border: 1px solid #d6e9cd;
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
        <a href="settings.php" class="nav-link active">Настройки сайта</a>
        <a href="services.php" class="nav-link">Услуги</a>
        <a href="reviews.php" class="nav-link">Отзывы</a>
        <a href="../index.php" target="_blank" class="nav-link">Перейти на сайт ↗</a>
        <a href="logout.php" class="btn-logout">Выйти</a>
    </div>
    <div class="content">
        <h1>Настройки сайта</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>

        <form method="post">
            <h2>Общие данные</h2>
            <div class="form-group">
                <label>Заголовок сайта (Title)</label>
                <input type="text" name="site_title" value="<?php echo h($settings['site']['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="site_description"><?php echo h($settings['site']['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Краткое имя (в логотипе)</label>
                <input type="text" name="name_short" value="<?php echo h($settings['site']['name_short'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Полное юридическое имя (в футере / schema.org)</label>
                <input type="text" name="name_legal" value="<?php echo h($settings['site']['name_legal'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>URL сайта (со слэшем в конце)</label>
                <input type="text" name="site_url" value="<?php echo h($settings['site']['url'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Картинка для соцсетей (OG image, 1200×630)</label>
                <input type="text" name="og_image" value="<?php echo h($settings['og_image'] ?? ''); ?>">
            </div>

            <h2>Контакты</h2>
            <div class="form-group">
                <label>Телефон для отображения</label>
                <input type="text" name="phone_display" value="<?php echo h($settings['phone']['display'] ?? ''); ?>"
                    placeholder="+7 (700) 123-45-67">
            </div>
            <div class="form-group">
                <label>Сообщение в WhatsApp</label>
                <input type="text" name="whatsapp_message"
                    value="<?php echo h($settings['whatsapp']['message'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Адрес (строка)</label>
                <input type="text" name="address_line" value="<?php echo h($settings['salon']['address_line'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Адрес: улица (для schema.org)</label>
                <input type="text" name="address_street"
                    value="<?php echo h($settings['salon']['address_street'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Адрес: город</label>
                <input type="text" name="address_city" value="<?php echo h($settings['salon']['address_city'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Адрес: страна (код, напр. KZ)</label>
                <input type="text" name="address_country"
                    value="<?php echo h($settings['salon']['address_country'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>График работы (строка, напр. «ежедневно 09:00 - 21:00»)</label>
                <input type="text" name="hours_label" value="<?php echo h($settings['salon']['hours_label'] ?? ''); ?>">
            </div>
            <div class="form-group" style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Открыто с (schema.org)</label>
                    <input type="text" name="opens" value="<?php echo h($settings['salon']['opens'] ?? ''); ?>">
                </div>
                <div style="flex: 1;">
                    <label>Открыто до</label>
                    <input type="text" name="closes" value="<?php echo h($settings['salon']['closes'] ?? ''); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Ссылка на Google Maps (кнопка в отзывах)</label>
                <input type="text" name="google_maps_url"
                    value="<?php echo h($settings['salon']['google_maps_url'] ?? ''); ?>">
            </div>
            <div class="form-group" style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label>Карта: широта (lat)</label>
                    <input type="text" name="map_lat" value="<?php echo h($settings['salon']['map_lat'] ?? ''); ?>">
                </div>
                <div style="flex: 1;">
                    <label>Карта: долгота (lng)</label>
                    <input type="text" name="map_lng" value="<?php echo h($settings['salon']['map_lng'] ?? ''); ?>">
                </div>
                <div style="flex: 1;">
                    <label>Карта: масштаб (zoom)</label>
                    <input type="text" name="map_zoom" value="<?php echo h($settings['salon']['map_zoom'] ?? ''); ?>">
                </div>
            </div>

            <h2>Главный блок (Hero)</h2>
            <div class="form-group">
                <label>Текст над заголовком (Бейдж)</label>
                <input type="text" name="hero_badge" value="<?php echo h($settings['hero']['badge'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Главный заголовок (Разрешен HTML тег &lt;em&gt;)</label>
                <input type="text" name="hero_title" value="<?php echo h($settings['hero']['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Подзаголовок</label>
                <textarea name="hero_description"><?php echo h($settings['hero']['description'] ?? ''); ?></textarea>
            </div>

            <h2>Преимущества (справа от Hero)</h2>
            <div class="form-group">
                <label>Заголовок блока преимуществ</label>
                <input type="text" name="hero_benefits_title"
                    value="<?php echo h($settings['hero']['benefits_title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Список преимуществ</label>
                <div id="benefits-container">
                    <?php foreach (($settings['hero']['benefits'] ?? []) as $benefit): ?>
                        <div class="benefit-item">
                            <input type="text" name="benefits[]" value="<?php echo h($benefit); ?>">
                        </div>
                    <?php endforeach; ?>
                    <!-- Пустое поле для добавления -->
                    <div class="benefit-item">
                        <input type="text" name="benefits[]" value="" placeholder="Добавить еще...">
                    </div>
                </div>
            </div>

            <h2>Спецпредложение (Offer / Цены)</h2>
            <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                <input type="checkbox" name="offer_show" value="1" <?php echo !empty($settings['offer']['show']) ? 'checked' : ''; ?> style="width: auto;">
                <label style="margin: 0;">Показывать блок спецпредложения</label>
            </div>
            <div class="form-group">
                <label>Заголовок акции</label>
                <input type="text" name="offer_title" value="<?php echo h($settings['offer']['title'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Описание и условия акции</label>
                <textarea name="offer_description"><?php echo h($settings['offer']['description'] ?? ''); ?></textarea>
            </div>

            <h2>Почта (заявки с формы)</h2>
            <div class="form-group">
                <label>E-mail получателя заявок</label>
                <input type="text" name="mail_to" value="<?php echo h($settings['mail']['to'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Тема письма</label>
                <input type="text" name="mail_subject" value="<?php echo h($settings['mail']['subject'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Имя отправителя (From)</label>
                <input type="text" name="mail_from_label"
                    value="<?php echo h($settings['mail']['from_label'] ?? ''); ?>">
            </div>

            <h2>Футер</h2>
            <div class="form-group">
                <label>Год в копирайте</label>
                <input type="text" name="footer_year" value="<?php echo h($settings['footer']['year'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-save">Сохранить всё</button>
        </form>
    </div>
</body>

</html>
