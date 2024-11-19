<?php
include 'includes/config.php';

// Function to display table structure
function displayTableStructure($conn, $tableName) {
    $result = $conn->query("DESCRIBE $tableName");
    if ($result) {
        echo "<h2>Structure of table '$tableName':</h2>";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "Error describing table $tableName: " . $conn->error;
    }
}

// Display structure of subscribers table
displayTableStructure($conn, 'subscribers');

// Close the connection
$conn->close();
?>
