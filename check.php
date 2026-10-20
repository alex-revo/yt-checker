<?php

declare(strict_types=1);

// ================== НАСТРОЙКИ ==================
define('BOT_TOKEN', getenv('BOT_TOKEN') ?: '');
define('CHAT_ID', getenv('CHAT_ID') ?: '');
define('APY_KEY', getenv('APY_KEY') ?: '');
const START_MINOR = 52;    // С какой версии начинать
const MAX_MINOR   = 200;   // Предохранитель
const STATE_FILE  = __DIR__ . '/state.json';
const HTTP_TIMEOUT = 15;

// ================== УТИЛИТЫ ==================
function out(string $msg): void
{
    echo '[' . date('H:i:s') . '] ' . $msg . PHP_EOL;
}

function loadState(): array
{
    if (!is_file(STATE_FILE)) return ['next_minor' => START_MINOR, 'notified' => []];
    $data = json_decode((string)file_get_contents(STATE_FILE), true);
    return is_array($data) ? $data + ['next_minor' => START_MINOR, 'notified' => []] : ['next_minor' => START_MINOR, 'notified' => []];
}

function saveState(array $state): void
{
    file_put_contents(STATE_FILE, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function httpStatus(string $url): ?int
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => HTTP_TIMEOUT,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_NOBODY         => true, // Используем HEAD, чтобы не качать весь zip
    ]);
    curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err !== '') {
        out("Ошибка cURL: $err");
        return null;
    }
    // Если HEAD не поддерживается сервером, делаем GET с Range
    if (in_array($code, [403, 405, 501], true)) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => HTTP_TIMEOUT,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            CURLOPT_RANGE          => '0-0',
        ]);
        curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    }
    return $code;
}

function urlForMinor(int $minor): string
{
    $apiKey = defined('API_KEY') ? API_KEY : '';
    $ver = '5.0.' . $minor;
    $url = "https://api.yootheme.com/v1/package/yootheme_j33/{$ver}/yootheme_j_{$ver}.zip";
        
        $url .= '?key=' . rawurlencode(API_KEY);
    
    return $url;
}

function sendTelegram(string $text): bool
{
    $url = 'https://api.telegram.org/bot' . BOT_TOKEN . '/sendMessage';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => [
            'chat_id' => CHAT_ID,
            'text' => $text,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ],
        CURLOPT_TIMEOUT        => HTTP_TIMEOUT,
    ]);
    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200);
}

// ================== ЛОГИКА ==================
$state = loadState();
$minor = (int)$state['next_minor'];

out("Проверка версии 5.0.$minor...");
$url  = urlForMinor($minor);
$code = httpStatus($url);

out("HTTP код: " . ($code ?? 'null'));

if ($code === null || $code >= 500) {
    out("Сетевой сбой, выходим.");
    exit(1);
}

$exists = ($code >= 200 && $code < 300);

if (!$exists) {
    out("Файла 5.0.$minor ещё нет. Ждём.");
    exit(0);
}

if (!in_array($minor, $state['notified'], true)) {
    $text = "🚀 <b>YOOtheme</b>: появилась новая версия <b>5.0.$minor</b>\n"
        . "Скачать: <a href=\"{$url}\">yootheme_j_5.0.$minor.zip</a>";
    if (sendTelegram($text)) {
        $state['notified'][] = $minor;
        out("Уведомление о 5.0.$minor отправлено в Telegram.");
    } else {
        out("Ошибка отправки в Telegram.");
        exit(1);
    }
} else {
    out("Уведомление о 5.0.$minor уже было отправлено ранее.");
}

$state['next_minor'] = $minor + 1;
saveState($state);
out("Переключаемся на проверку 5.0.{$state['next_minor']}.");
