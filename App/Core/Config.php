<?php

namespace App\Core;

class Config
{
    private static ?array $config = null;

    public static function get(?string $key = null, mixed $default = null): mixed
    {
        if (self::$config === null) {
            $rootDir    = dirname(__DIR__, 2);
            $prodFile   = $rootDir . '/config.php';
            $localFile  = $rootDir . '/config.local.php';
            $prodConfig = file_exists($prodFile) ? (require $prodFile) : [];
            $localConfig = file_exists($localFile) ? (require $localFile) : [];
            self::$config = array_replace_recursive($prodConfig, $localConfig);


        }

        if ($key === null) {
            return self::$config;
        }

        return self::$config[$key] ?? $default;
    }
}
