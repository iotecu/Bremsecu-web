<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact.html', true, 303);
    exit;
}

$recipient = 'info@bremsecu.com';
$fromAddress = 'info@bremsecu.com';
$siteName = 'Bremsecu';

function safe_substr(string $value, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
}

function clean_line(string $value, int $maxLength): string
{
    $value = trim($value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return safe_substr($value, $maxLength);
}

function source_path(string $source): string
{
    $map = [
        'homepage' => '/',
        'g1' => '/bremsecu-g1.html',
        'wt-pro' => '/bremsecu-wt-pro.html',
        'contact' => '/contact.html',
        'overview' => '/what-is-bremsecu.html',
        'methodology' => '/measurement-methodology.html',
        'about' => '/about.html',
    ];
    return $map[$source] ?? '/contact.html';
}

function redirect_result(bool $sent, string $source = 'contact'): void
{
    $path = source_path($source);
    $separator = strpos($path, '?') !== false ? '&' : '?';
    header('Location: ' . $path . $separator . 'sent=' . ($sent ? '1' : '0') . '#inquiry', true, 303);
    exit;
}

$source = clean_line((string) ($_POST['source'] ?? 'contact'), 40);

if (!empty($_POST['website'] ?? '')) {
    redirect_result(true, $source);
}

$started = isset($_POST['form_started']) ? (int) $_POST['form_started'] : 0;
$elapsedMs = $started > 0 ? ((int) round(microtime(true) * 1000)) - $started : 0;
if ($started <= 0 || $elapsedMs < 2500 || $elapsedMs > 86400000) {
    redirect_result(false, $source);
}

$name = clean_line((string) ($_POST['name'] ?? ''), 120);
$company = clean_line((string) ($_POST['company'] ?? ''), 160);
$phone = clean_line((string) ($_POST['phone'] ?? ''), 60);
$email = trim((string) ($_POST['email'] ?? ''));
$location = clean_line((string) ($_POST['location'] ?? ''), 160);
$address = clean_line((string) ($_POST['address'] ?? ''), 260);
$topic = clean_line((string) ($_POST['topic'] ?? ''), 160);
$message = safe_substr(trim((string) ($_POST['message'] ?? '')), 5000);
$consent = (string) ($_POST['consent'] ?? '');

$allowedTopics = [
    'Technical discussion',
    'Product presentation',
    'Partnership inquiry',
    'Meeting request',
    'General Bremsecu inquiry',
    'Bremsecu G1 information request',
    'Bremsecu WT Pro information request',
];

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_result(false, $source);
}

if (in_array($source, ['homepage', 'g1', 'wt-pro', 'overview', 'methodology', 'about', 'contact'], true)) {
    if ($company === '' || $phone === '' || $location === '' || $address === '' || $consent !== '1') {
        redirect_result(false, $source);
    }
}

if (!in_array($topic, $allowedTopics, true)) {
    $topic = 'General inquiry';
}

$subject = '[Bremsecu Website] ' . $topic . ($company !== '' ? ' - ' . $company : '');
$body = "New message from bremsecu.com\n\n"
    . "Source: {$source}\n"
    . "Name: {$name}\n"
    . "Company: " . ($company !== '' ? $company : '-') . "\n"
    . "Phone: " . ($phone !== '' ? $phone : '-') . "\n"
    . "Email: {$email}\n"
    . "City / Country: " . ($location !== '' ? $location : '-') . "\n"
    . "Address: " . ($address !== '' ? $address : '-') . "\n"
    . "Topic: {$topic}\n"
    . "Consent: " . ($consent === '1' ? 'Yes' : 'Not requested on this form') . "\n\n"
    . "Message:\n{$message}\n";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: ' . $siteName . ' Website <' . $fromAddress . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$sent = mail($recipient, $subject, $body, implode("\r\n", $headers));
redirect_result($sent, $source);
