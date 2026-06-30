<?php
/**
 * Mystical Expedition - Application Bootstrap
 *
 * Loads Composer autoloader, .env, config constants,
 * starts session, sets error handler.
 */

declare(strict_types=1);

namespace Mystical;

use Dotenv\Dotenv;
use PDOException;
use Throwable;

final class Bootstrap
{
    private static bool $booted = false;

    /**
     * Initialise the application. Safe to call multiple times.
     */
    public static function init(): void
    {
        if (self::$booted) {
            return;
        }

        self::loadEnv();
        self::setErrorHandler();
        self::startSession();
        self::ensureDatabase();

        self::$booted = true;
    }

    /**
     * Get a config value from the environment.
     */
    public static function config(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            return $default;
        }
        return (string) $value;
    }

    /**
     * Convenience boolean helper.
     */
    public static function isDebug(): bool
    {
        return strtolower((string) self::config('APP_DEBUG', 'false')) === 'true';
    }

    /**
     * Convenience boolean helper.
     */
    public static function isProduction(): bool
    {
        return strtolower((string) self::config('APP_ENV', 'production')) === 'production';
    }

    // -----------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------

    private static function loadEnv(): void
    {
        $root = self::projectRoot();

        if (file_exists($root . '/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->safeLoad();
        }
    }

    private static function setErrorHandler(): void
    {
        $debug = self::isDebug();
        $logFile = self::projectRoot() . '/logs/error.log';

        // Ensure log file is writable
        self::ensureLogFile($logFile);

        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use ($debug, $logFile) {
            $msg = sprintf(
                "[%s] %s in %s:%d\n",
                date('Y-m-d H:i:s'),
                $errstr,
                $errfile,
                $errline
            );
            @file_put_contents($logFile, $msg, FILE_APPEND);

            if ($debug) {
                echo "<pre style='background:#fee;padding:10px;border:1px solid #c00;'>$msg</pre>";
            }
            return true;
        });

        set_exception_handler(static function (Throwable $e) use ($debug, $logFile) {
            $msg = sprintf(
                "[%s] EXCEPTION %s: %s in %s:%d\n%s\n",
                date('Y-m-d H:i:s'),
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );
            @file_put_contents($logFile, $msg, FILE_APPEND);

            if ($debug) {
                echo "<pre style='background:#fee;padding:10px;border:1px solid #c00;'>$msg</pre>";
            } else {
                http_response_code(500);
                if (!headers_sent()) {
                    header('Content-Type: text/plain; charset=UTF-8');
                }
                echo 'An unexpected error occurred. Please try again later.';
            }
            exit;
        });
    }

    private static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Cookies can only be configured before any output is sent.
        // In CLI / smoke-test mode, just skip the cookie config.
        if (PHP_SAPI === 'cli') {
            return; // No sessions in CLI
        }
        if (!headers_sent()) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => self::isProduction(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_name('ME_SESSID');
            @session_start();
        }
    }

    private static function ensureDatabase(): void
    {
        try {
            Database::getInstance();
        } catch (PDOException $e) {
            // Database error is already logged; re-throw so it surfaces
            throw $e;
        }
    }

    private static function ensureLogFile(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!file_exists($path)) {
            @touch($path);
            @chmod($path, 0664);
        }
    }

    /**
     * Absolute path to project root (where .env, src/, views/, vendor/ live).
     *
     * Local (XAMPP): project root is the parent of src/.
     * Production (Hostinger): src/ is at public_html/shimla/_app/src/, so we walk up TWO levels.
     *
     * To support both layouts, we walk up levels until we find a directory
     * that contains composer.json (the canonical "project root" marker).
     */
    public static function projectRoot(): string
    {
        // Start from src/ and walk up to find composer.json
        $dir = __DIR__;
        for ($i = 0; $i < 5; $i++) {
            $dir = dirname($dir);
            if (is_file($dir . '/composer.json')) {
                return $dir;
            }
        }
        // Fallback: assume the old layout (parent of src/)
        return dirname(__DIR__);
    }

    /**
     * Absolute path to public/ (the web root).
     * The public folder is the parent directory of the project root.
     */
    public static function publicPath(): string
    {
        return dirname(self::projectRoot());
    }
}