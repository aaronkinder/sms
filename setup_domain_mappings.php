<?php
require_once 'includes/config.php';

// Create domain_mappings table
$sql = "CREATE TABLE IF NOT EXISTS domain_mappings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_name VARCHAR(255) NOT NULL UNIQUE,
    company_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Domain mappings table created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Add initial sample mapping if needed
$domain = 'sms.test';
$company = 'SMS';
$stmt = $conn->prepare("INSERT IGNORE INTO domain_mappings (domain_name, company_name) VALUES (?, ?)");
$stmt->bind_param("ss", $domain, $company);
$stmt->execute();

$conn->close();
?>
