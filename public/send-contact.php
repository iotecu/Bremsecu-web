<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact.html', true, 303);
    exit;
}

$recipient = 'info@bremsecu.com';
$fromAddress = 'info@bremsecu.com';
$siteName = 'Bremsecu';

function clean_line(string $value, int $maxLength): string
{
    $value = trim($value);
    $value = str_replace(["\r", "\n"], ' ', $value);
    return mb_substr($value, 0, $maxLength);
}

function redirect_result(bool $sent): void
{
    header('Location: /contact.html?sent=' . ($sent ? '1' : '0'), true, 303);
    exit;
}

// Honeypot: humans should never fill this field.
if (!empty($_POST['website'] ?? '')) {
    redirect_result(true);
}

// Basic timing check to reject instant automated submissions.
$started = isset($_POST['form_started']) ? (int) $_POST['form_started'] : 0;
$elapsedMs = $started > 0 ? ((int) round(microtime(true) * 1000)) - $started : 0;
if ($started <= 0 || $elapsedMs < 2500 || $elapsedMs > 86400000) {
    redirect_result(false);
}

$name = clean_line((string) ($_POST['name'] ?? ''), 120);
$company = clean_line((string) ($_POST['company'] ?? ''), 160);
$email = trim((string) ($_POST['email'] ?? ''));
$topic = clean_line((string) ($_POST['topic'] ?? ''), 120);
$message = trim((string) ($_POST['message'] ?? ''));
$message = mb_substr($message, 0, 5000);

$allowedTopics = [
    'Technical discussion',
    'Product presentation',
    'Partnership inquiry',
    'Meeting request',
];

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_result(false);
}

if (!in_array($topic, $allowedTopics, true)) {
    $topic = 'General inquiry';
}

$subject = '[Bremsecu Website] ' . $topic;
$body = "New message from bremsecu.com\n\n"
    . "Name: {$name}\n"
    . "Company: " . ($company !== '' ? $company : '-') . "\n"
    . "Email: {$email}\n"
    . "Topic: {$topic}\n\n"
    . "Message:\n{$message}\n";

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'From: ' . $siteName . ' Website <' . $fromAddress . '>',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . PHP_VERSION,
];

$sent = mail($recipient, $subject, $body, implode("\r\n", $headers));
redirect_result($sent);
