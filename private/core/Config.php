<?php

class Config
{
    public function __construct()
    {
        self::blockedRoutes([
            'index.php',
            'public/index.php',
            'public',
            'private',
        ]);
    }

    // Database
    public const DB_LOCALHOST = 'localhost';
    public const DB_USER = 'root';
    public const DB_PASSWORD = '';
    public const DB_NAME = 'mvc';
    public const APP_KEY = 'pPQ90Co9y2L2869CIjBc1pmzglF2canmIppLlLI4SCI';

    










    // Roots
    public const PRIVATEROOT = '../private';
    public const PUBLICROOT = './';

    public static function URLROOT(): string
    {
        $protocol = (
            !empty($_SERVER['HTTPS']) &&
            $_SERVER['HTTPS'] !== 'off'
        )
            ? 'https://'
            : 'http://';

        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $domain = $protocol . $host;

        if (strpos($domain, 'localhost') !== false) {
            $domain .= '/' .
                trim(
                    dirname($_SERVER['SCRIPT_NAME'] ?? '', 2),
                    '/'
                );
        }

        return rtrim($domain, '/') . '/';
    }

    public static function blockedRoutes(array $routes): void
    {
        $currentPath = parse_url(
            $_SERVER['REQUEST_URI'] ?? '/',
            PHP_URL_PATH
        );

        $basePath = parse_url(
            self::URLROOT(),
            PHP_URL_PATH
        );

        $basePath = trim($basePath, '/');
        $currentPath = trim($currentPath, '/');

        foreach ($routes as $route) {

            $blockedPath = trim(
                $basePath . '/' . ltrim($route, '/'),
                '/'
            );

            if ($currentPath === $blockedPath) {
                Errors::error(404);
                exit;
            }
        }
    }
}

new Config;