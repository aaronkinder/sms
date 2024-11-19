<?php
// Include database configuration
require_once 'includes/config.php';

// Security check - require a specific confirmation parameter
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    die("WARNING: This script will delete ALL data from the database. To proceed, add ?confirm=yes to the URL.");
}

// Get all tables in the database
$tables = array();
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Drop each table
foreach ($tables as $table) {
    $sql = "DROP TABLE IF EXISTS `$table`";
    if ($conn->query($sql)) {
        echo "Table '$table' has been deleted successfully.<br>";
    } else {
        echo "Error deleting table '$table': " . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();

echo "<br>Database cleanup completed.";
?>
