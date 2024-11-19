<?php
require_once 'includes/config.php';

$sql = "ALTER TABLE domain_mappings ADD COLUMN dba_name VARCHAR(255) NULL AFTER company_name";

if ($conn->query($sql)) {
    echo "Successfully added dba_name column\n";
} else {
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
