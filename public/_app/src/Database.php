<?php
/**
 * Mystical Expedition - SQLite Database Wrapper
 *
 * Single PDO connection. Auto-migrates schema on first use.
 */

declare(strict_types=1);

namespace Mystical;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    /**
     * Get the singleton PDO instance. Creates file + schema on first call.
     */
    public static function getInstance(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dbPath = Bootstrap::config('DB_PATH', 'data/leads.sqlite');

        // Resolve relative path against project root
        if (!self::isAbsolute($dbPath)) {
            $dbPath = Bootstrap::projectRoot() . '/' . $dbPath;
        }

        // Ensure folder exists
        $dir = dirname($dbPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        try {
            self::$pdo = new PDO('sqlite:' . $dbPath, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            // Enable WAL mode for better concurrency
            self::$pdo->exec('PRAGMA journal_mode = WAL;');
            self::$pdo->exec('PRAGMA foreign_keys = ON;');

            self::migrate();
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Database connection failed: ' . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        return self::$pdo;
    }

    /**
     * Run schema.sql to create tables/indexes/triggers.
     */
    public static function migrate(): void
    {
        $pdo = self::getInstance();
        $schemaFile = Bootstrap::projectRoot() . '/schema.sql';

        if (!file_exists($schemaFile)) {
            throw new RuntimeException("Schema file not found: $schemaFile");
        }

        $sql = file_get_contents($schemaFile);
        if ($sql === false) {
            throw new RuntimeException("Could not read schema file: $schemaFile");
        }

        // SQLite executes multiple statements separated by `;` correctly via exec()
        $pdo->exec($sql);
    }

    /**
     * Insert a lead and return its new ID.
     */
    public static function insertLead(array $data): int
    {
        $pdo = self::getInstance();

        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, city, email, phone, destination, message, ip_address, user_agent, referrer, status)
             VALUES (:name, :city, :email, :phone, :destination, :message, :ip, :ua, :ref, :status)'
        );

        $stmt->execute([
            ':name'        => $data['name'],
            ':city'        => $data['city'],
            ':email'       => $data['email'],
            ':phone'       => $data['phone'],
            ':destination' => $data['destination'],
            ':message'     => $data['message'] ?? null,
            ':ip'          => $data['ip_address'] ?? null,
            ':ua'          => $data['user_agent'] ?? null,
            ':ref'         => $data['referrer'] ?? null,
            ':status'      => $data['status'] ?? 'new',
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Count leads from the same IP within the last hour.
     */
    public static function countRecentByIp(string $ip, int $windowSeconds = 3600): int
    {
        $pdo = self::getInstance();

        $stmt = $pdo->prepare(
            "SELECT COUNT(*) AS c
             FROM leads
             WHERE ip_address = :ip
               AND created_at >= datetime('now', '-' || :secs || ' seconds')"
        );
        $stmt->execute([':ip' => $ip, ':secs' => $windowSeconds]);

        $row = $stmt->fetch();
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Get all leads (for future admin).
     */
    public static function getAllLeads(int $limit = 100): array
    {
        $pdo = self::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM leads ORDER BY created_at DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private static function isAbsolute(string $path): bool
    {
        // Windows: C:\ or \foo  |  Unix: /foo
        return (
            (strlen($path) >= 2 && $path[1] === ':') ||
            ($path[0] === '/' || $path[0] === '\\')
        );
    }
}