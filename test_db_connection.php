<?php

// Load environment variables from .env file
$envContent = file_get_contents('.env');
$lines = explode("\n", $envContent);
$env = [];

foreach ($lines as $line) {
    if (empty(trim($line)) || strpos(trim($line), '#') === 0) {
        continue;
    }
    
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        // Remove quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        }
        $env[$key] = $value;
    }
}

// Database connection details from .env
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$database = $env['DB_DATABASE'] ?? 'eurekacom_wifi';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

echo "Attempting to connect to MySQL database...\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $database\n";
echo "Username: $username\n";
echo "Password: " . ($password ? str_repeat('*', strlen($password)) : '(empty)') . "\n\n";

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully to the database!\n";
    
    // Test query to check if clients table exists and has the scheduled_deletion_at column
    $stmt = $conn->query("DESCRIBE clients");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nColumns in clients table:\n";
    $hasScheduledDeletion = false;
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        if ($column['Field'] === 'scheduled_deletion_at') {
            $hasScheduledDeletion = true;
        }
    }
    
    if ($hasScheduledDeletion) {
        echo "\nThe 'scheduled_deletion_at' column exists in the clients table.\n";
        
        // Check if there are any users scheduled for deletion
        $stmt = $conn->query("SELECT COUNT(*) FROM clients WHERE scheduled_deletion_at IS NOT NULL AND scheduled_deletion_at < NOW()");
        $count = $stmt->fetchColumn();
        
        echo "There are $count users scheduled for deletion.\n";
    } else {
        echo "\nWARNING: The 'scheduled_deletion_at' column does not exist in the clients table!\n";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 