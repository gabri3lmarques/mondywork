<?php

namespace App\Core;

class Config
{
    private static ?array $config = null;

    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if (self::$config === null) {
            $rootDir = dirname(__DIR__, 2);
            $localFile = $rootDir . '/config.local.php';
            $prodFile  = $rootDir . '/config.php';

            $file = file_exists($localFile) ? $localFile : $prodFile;
            self::$config = file_exists($file) ? require $file : [];
        }

        if ($key === null) {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }
}
