<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/**
 * Заявки с формы лендинга.
 * Адрес получателя — в админке: Настройки сайта → Почта (data/settings.json).
 * На OSPanel включите sendmail / SMTP (иначе mail() может не работать).
 */

function load_json(string $filename): array
{
    $path = __DIR__ . '/data/' . $filename;
    if (!file_exists($path))
        return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

$config = load_json('settings.json');
if (empty($config)) {
    // Запасной вариант — если data/settings.json ещё нет
    $config = require __DIR__ . '/config.php';
}
$mailTo = (string) ($config['mail']['to'] ?? '');

function json_out(int $httpCode, bool $ok, string $error = '', string $message = ''): void
{
    http_response_code($httpCode);
    echo json_encode(
        ['ok' => $ok, 'error' => $error, 'message' => $message],
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(405, false, 'Метод не поддерживается');
}

// Антиспам: ограничение по IP (макс 3 заявки за 5 минут)
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$rate_limit_file = __DIR__ . '/data/contact_rate_limit.json';
$attempts = file_exists($rate_limit_file) ? json_decode(file_get_contents($rate_limit_file), true) : [];
$attempts = is_array($attempts) ? $attempts : [];
$now = time();

foreach ($attempts as $a_ip => $data) {
    if ($now - $data['time'] > 300) { // 5 минут
        unset($attempts[$a_ip]);
    }
}

if (isset($attempts[$ip]) && $attempts[$ip]['count'] >= 3) {
    json_out(429, false, 'Слишком много заявок. Попробуйте позже.');
}

// Антиспам: боты часто заполняют скрытое поле
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    json_out(200, true, '', 'Спасибо!');
}

// Увеличиваем счетчик попыток
$attempts[$ip] = [
    'count' => ($attempts[$ip]['count'] ?? 0) + 1,
    'time' => $now
];
file_put_contents($rate_limit_file, json_encode($attempts));

$name = trim((string) ($_POST['name'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$consent = isset($_POST['consent']);

$lenFn = function_exists('mb_strlen')
    ? static fn (string $s): int => mb_strlen($s, 'UTF-8')
    : static fn (string $s): int => strlen($s);

if ($lenFn($name) < 2) {
    json_out(422, false, 'Укажите имя');
}
if ($lenFn($name) > 120) {
    json_out(422, false, 'Имя слишком длинное');
}

if ($phone === '' || preg_match('/\d/u', $phone) !== 1) {
    json_out(422, false, 'Укажите корректный телефон');
}
if ($lenFn($phone) > 32) {
    json_out(422, false, 'Телефон слишком длинный');
}

if (!$consent) {
    json_out(422, false, 'Нужно согласие на обработку данных');
}

if ($mailTo === 'you@example.com') {
    json_out(500, false, 'Укажите получателя в админке (Настройки → Почта)');
}

$host = preg_replace('/[^\w.-]+/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
$subject = (string) ($config['mail']['subject'] ?? '');
$bodyLines = [
    'Имя: ' . $name,
    'Телефон: ' . $phone,
    'Согласие на обработку ПДн: да',
    'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? ''),
    'Время: ' . date('c'),
];
$body = implode("\n", $bodyLines);

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$headers = implode("\r\n", [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: ' . ($config['mail']['from_label'] ?? '') . ' <noreply@' . $host . '>',
]);

$sent = @mail($mailTo, $encodedSubject, $body, $headers);

// Сохраняем в JSON (даже если почта не ушла, чтобы не потерять заявку)
$leads = load_json('leads.json');
$leads[] = [
    'date' => date('Y-m-d H:i:s'),
    'name' => $name,
    'phone' => $phone,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
];
// Оставляем только свежие заявки (например, последние 500)
if (count($leads) > 500) {
    $leads = array_slice($leads, -500);
}
file_put_contents(__DIR__ . '/data/leads.json', json_encode($leads, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));

if (!$sent) {
    // Заявка сохранилась, клиенту показываем «Спасибо»
    error_log('VELVET CMS: Почта не отправлена. Заявка сохранена в leads.json');
}

json_out(200, true, '', 'Спасибо! Мы свяжемся с вами.');
