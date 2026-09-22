<?php

declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Class Database
 * 
 * Manages database connection using PDO singleton pattern.
 * Reads credentials from environment variables.
 * 
 * @package App\Config
 */
class Database
{
    /**
     * @var PDO|null The single PDO connection instance.
     */
    private static ?PDO $connection = null;

    /**
     * Private constructor to prevent direct instantiation (Singleton pattern).
     */
    private function __construct()
    {
    }

    /**
     * Get the active PDO database connection instance.
     *
     * @return PDO
     * @throws RuntimeException If connection fails.
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Load environment variables if not already loaded
            if (!isset($_ENV['DB_HOST'])) {
                $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
                $dotenv->safeLoad();
            }

            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $database = $_ENV['DB_DATABASE'] ?? 'knowledge_learning';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

            try {
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection error: ' . $e->getMessage(), (int) $e->getCode(), $e);
            }
        }

        return self::$connection;
    }

    /**
     * Set a custom PDO instance (mainly used for automated unit/integration tests).
     *
     * @param PDO|null $pdo
     * @return void
     */
    public static function setConnection(?PDO $pdo): void
    {
        self::$connection = $pdo;
    }
}
