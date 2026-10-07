<?php

$env = parse_ini_file(__DIR__ . '/../.env', false, INI_SCANNER_RAW);

$hosts = [$env['DB_HOST']];
if ($env['DB_HOST'] !== '127.0.0.1') {
    $hosts[] = '127.0.0.1';
}

$pdo = null;
foreach ($hosts as $host) {
    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';port=' . $env['DB_PORT'] . ';dbname=' . $env['DB_DATABASE'],
            $env['DB_USERNAME'],
            $env['DB_PASSWORD'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        break;
    } catch (PDOException $exception) {
        if ($host === end($hosts)) {
            throw $exception;
        }
    }
}

$statements = [
    "CREATE TABLE IF NOT EXISTS admin_support_tickets (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        subject VARCHAR(255) NOT NULL,
        requester_name VARCHAR(255) NULL,
        requester_email VARCHAR(255) NULL,
        priority VARCHAR(255) NOT NULL DEFAULT 'Normal',
        status VARCHAR(255) NOT NULL DEFAULT 'Open',
        message TEXT NULL,
        resolution TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS shipment_bookings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        booking_no VARCHAR(255) NULL,
        mode VARCHAR(255) NOT NULL DEFAULT 'Air',
        origin VARCHAR(255) NULL,
        destination VARCHAR(255) NULL,
        carrier_name VARCHAR(255) NULL,
        scheduled_date DATE NULL,
        status VARCHAR(255) NOT NULL DEFAULT 'Scheduled',
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS flight_schedules (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        flight_no VARCHAR(255) NULL,
        airline VARCHAR(255) NULL,
        origin VARCHAR(255) NULL,
        destination VARCHAR(255) NULL,
        departure_at DATETIME NULL,
        arrival_at DATETIME NULL,
        status VARCHAR(255) NOT NULL DEFAULT 'Scheduled',
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS sea_vessel_schedules (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        vessel_name VARCHAR(255) NULL,
        voyage_no VARCHAR(255) NULL,
        origin_port VARCHAR(255) NULL,
        destination_port VARCHAR(255) NULL,
        departure_at DATETIME NULL,
        arrival_at DATETIME NULL,
        status VARCHAR(255) NOT NULL DEFAULT 'Scheduled',
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS warehouse_supplies (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        sku VARCHAR(255) NULL,
        quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
        reorder_level DECIMAL(12,2) NOT NULL DEFAULT 0,
        unit VARCHAR(255) NOT NULL DEFAULT 'pcs',
        status VARCHAR(255) NOT NULL DEFAULT 'Available',
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS internal_inventory (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        sku VARCHAR(255) NULL,
        category VARCHAR(255) NULL,
        quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
        value DECIMAL(12,2) NOT NULL DEFAULT 0,
        status VARCHAR(255) NOT NULL DEFAULT 'Active',
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS internal_waste_logs (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        item_name VARCHAR(255) NOT NULL,
        quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
        unit VARCHAR(255) NOT NULL DEFAULT 'pcs',
        reason VARCHAR(255) NULL,
        logged_at DATE NULL,
        disposal_method VARCHAR(255) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS finance_entries (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(255) NOT NULL,
        category VARCHAR(255) NULL,
        amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        entry_date DATE NULL,
        reference VARCHAR(255) NULL,
        description TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS portal_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        `group` VARCHAR(255) NOT NULL DEFAULT 'general',
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($statements as $statement) {
    $pdo->exec($statement);
}

$migration = '2026_06_17_000001_create_admin_portal_feature_tables';
$exists = $pdo->prepare('SELECT COUNT(*) FROM migrations WHERE migration = ?');
$exists->execute([$migration]);

if ((int) $exists->fetchColumn() === 0) {
    $batch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations')->fetchColumn();
    $insert = $pdo->prepare('INSERT INTO migrations (migration, batch) VALUES (?, ?)');
    $insert->execute([$migration, $batch]);
}

echo "portal tables ready\n";
