<?php

function cachePath($key)
{
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir . '/' . md5($key) . '.json';
}

function cacheGet($key, $ttl = 300)
{
    $file = cachePath($key);
    if (!file_exists($file)) return null;
    if (time() - filemtime($file) > $ttl) {
        @unlink($file);
        return null;
    }
    $data = @file_get_contents($file);
    return $data ? json_decode($data, true) : null;
}

function cacheSet($key, $data)
{
    $file = cachePath($key);
    @file_put_contents($file, json_encode($data), LOCK_EX);
}

function cacheClear()
{
    $dir = __DIR__ . '/../cache';
    if (!is_dir($dir)) return;
    foreach (glob($dir . '/*.json') as $f) {
        @unlink($f);
    }
}
