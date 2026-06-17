<?php

function logMsg($message, array $context = [])
{
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $level = strtoupper($context['level'] ?? 'INFO');
    $ctx = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $line = date('Y-m-d H:i:s') . " [{$level}] {$message}{$ctx}" . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

function logError($message, array $context = [])
{
    $context['level'] = 'ERROR';
    logMsg($message, $context);
}
