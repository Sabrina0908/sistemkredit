<?php

function envValue(string $key, ?string $default = null): ?string
{
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null || $value === '') {
        return $default;
    }

    return (string) $value;
}

function getConnection(): PDO
{
    static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $driver = strtolower(envValue('DB_CONNECTION', getDefaultDriver()));

    if ($driver === 'mysql') {
        $host = envValue('DB_HOST', '127.0.0.1');
        $port = envValue('DB_PORT', '3306');
        $database = envValue('DB_DATABASE', 'sistemkredit');
        $username = envValue('DB_USERNAME', 'root');
        $password = envValue('DB_PASSWORD', '');

        $bootstrap = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        $bootstrap->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $connection = new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        initializeMysqlSchema($connection, $database);

        return $connection;
    }

    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('Driver SQLite tidak tersedia di server ini.');
    }

    $databasePath = envValue('SQLITE_PATH', __DIR__ . '/data/database.sqlite');
    $directory = dirname($databasePath);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $connection = new PDO(
        'sqlite:' . $databasePath,
        null,
        null,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    initializeSqliteSchema($connection);

    return $connection;
}

function getDefaultDriver(): string
{
    if (extension_loaded('pdo_sqlite')) {
        return 'sqlite';
    }

    return 'mysql';
}

function initializeMysqlSchema(PDO $connection, string $database): void
{
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS analisis_kredit (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            jenis_nasabah VARCHAR(30) NOT NULL,
            penghasilan BIGINT NOT NULL,
            jaminan VARCHAR(100) NOT NULL,
            tanggungan INT NOT NULL,
            pekerjaan VARCHAR(50) NOT NULL,
            pengajuan BIGINT NOT NULL,
            skor_rata_rata DECIMAL(10,2) NOT NULL,
            rule_keputusan INT NOT NULL,
            keputusan VARCHAR(30) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $statement = $connection->prepare(
        'SELECT COLUMN_NAME
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = :database
         AND TABLE_NAME = :table
         AND COLUMN_NAME = :column'
    );
    $statement->execute([
        'database' => $database,
        'table' => 'analisis_kredit',
        'column' => 'status_usaha',
    ]);

    if ($statement->fetch() !== false) {
        $connection->exec(
            'ALTER TABLE analisis_kredit
             CHANGE COLUMN status_usaha pekerjaan VARCHAR(50) NOT NULL'
        );
    }
}

function initializeSqliteSchema(PDO $connection): void
{
    $connection->exec(
        'CREATE TABLE IF NOT EXISTS analisis_kredit (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nama TEXT NOT NULL,
            jenis_nasabah TEXT NOT NULL,
            penghasilan INTEGER NOT NULL,
            jaminan TEXT NOT NULL,
            tanggungan INTEGER NOT NULL,
            pekerjaan TEXT NOT NULL,
            pengajuan INTEGER NOT NULL,
            skor_rata_rata REAL NOT NULL,
            rule_keputusan INTEGER NOT NULL,
            keputusan TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $columns = $connection->query('PRAGMA table_info(analisis_kredit)')->fetchAll();
    $columnNames = array_map(
        static fn (array $column): string => (string) ($column['name'] ?? ''),
        $columns
    );

    if (in_array('status_usaha', $columnNames, true) && !in_array('pekerjaan', $columnNames, true)) {
        $connection->exec('ALTER TABLE analisis_kredit RENAME COLUMN status_usaha TO pekerjaan');
    }
}

function formatRupiah(int|float $nominal): string
{
    return 'Rp ' . number_format((float) $nominal, 0, ',', '.');
}
