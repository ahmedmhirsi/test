<?php
$dsn = 'mysql:host=127.0.0.1;dbname=smartnexus_db;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO($dsn, $user, $pass);
    $hashedPassword = password_hash('admin123', PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("UPDATE user SET password = ?, role = 'Admin' WHERE email = 'admin@smartnexus.com'");
    $stmt->execute([$hashedPassword]);
    
    if ($stmt->rowCount() > 0) {
        echo "Successfully updated admin@smartnexus.com\n";
    } else {
        // Try to insert if not exists
        echo "User not found, attempting to insert...\n";
        $stmt = $pdo->prepare("INSERT INTO user (nom, email, role, password, statut_actif, statut_channel, points) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(['Admin User', 'admin@smartnexus.com', 'Admin', $hashedPassword, 1, 'Active', 0]);
        echo "User created successfully.\n";
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
