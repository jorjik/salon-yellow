<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

/**
 * Заявки с формы лендинга.
 * Адрес получателя — в config.php → mail.to
 * На OSPanel включите sendmail / SMTP (иначе mail() может не работать).
 */

$config = require __DIR__ . '/config.php';
$mailTo = (string) $config['mail']['to'];

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

// Антиспам: боты часто заполняют скрытое поле
if (trim((string)($_POST['website'] ?? '')) !== '') {
    json_out(200, true, '', 'Спасибо!');
}

$name = trim((string)($_POST['name'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));
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
    json_out(500, false, 'Укажите получателя в config.php (mail.to)');
}

$host = preg_replace('/[^\w.-]+/', '', (string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
$subject = (string) $config['mail']['subject'];
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
    'From: ' . $config['mail']['from_label'] . ' <noreply@' . $host . '>',
]);

$sent = @mail($mailTo, $encodedSubject, $body, $headers);

if (!$sent) {
    json_out(500, false, 'Почта не отправлена. Проверьте настройки PHP mail / SMTP.');
}

json_out(200, true, '', 'Спасибо! Мы свяжемся с вами.');
