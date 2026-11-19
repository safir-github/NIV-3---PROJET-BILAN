<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use App\Config\Database;

/**
 * Class BaseRepository
 * 
 * Abstract base DAO component providing PDO database connection.
 * 
 * @package App\Repositories
 */
abstract class BaseRepository
{
    protected PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }
}
