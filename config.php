<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function getConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = '127.0.0.1';
    $username = 'root';
    $password = '';
    $database = 'sistemkredit';

    $connection = new mysqli($host, $username, $password);
    $connection->set_charset('utf8mb4');
    $connection->query("CREATE DATABASE IF NOT EXISTS `{$database}`");
    $connection->select_db($database);

    $connection->query(
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

    $columnCheck = $connection->query(
        "SELECT COLUMN_NAME
         FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = '{$database}'
         AND TABLE_NAME = 'analisis_kredit'
         AND COLUMN_NAME = 'status_usaha'"
    );

    if ($columnCheck->num_rows > 0) {
        $connection->query(
            'ALTER TABLE analisis_kredit
             CHANGE COLUMN status_usaha pekerjaan VARCHAR(50) NOT NULL'
        );
    }

    return $connection;
}

function formatRupiah(int|float $nominal): string
{
    return 'Rp ' . number_format((float) $nominal, 0, ',', '.');
}
