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
                echo "<td>" . htmlspecialchars($value ?? '') . "</td>";
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

// Function to generate a random name
function generateRandomName() {
    $firstNames = ['John', 'Jane', 'Michael', 'Emily', 'David', 'Sarah', 'Robert', 'Emma', 'William', 'Olivia'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
    return [
        'first_name' => $firstNames[array_rand($firstNames)],
        'last_name' => $lastNames[array_rand($lastNames)]
    ];
}

// Function to generate a random phone number
function generateRandomPhone() {
    return '+1' . rand(100, 999) . rand(100, 999) . rand(1000, 9999);
}

// Function to generate a random IP address
function generateRandomIP() {
    return rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);
}

// Insert 100 test records
$successCount = 0;
for ($i = 0; $i < 100; $i++) {
    $name = generateRandomName();
    $phone = generateRandomPhone();
    $email = strtolower($name['first_name'] . '.' . $name['last_name']) . '@example.com';
    $ip_address = generateRandomIP();

    $stmt = $conn->prepare("INSERT INTO subscribers (first_name, last_name, phone, email, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name['first_name'], $name['last_name'], $phone, $email, $ip_address);
    
    if ($stmt->execute()) {
        $successCount++;
    } else {
        echo "Error inserting record: " . $stmt->error . "<br>";
    }
    
    $stmt->close();
}

echo "Insertion complete. Successfully inserted $successCount out of 100 records.";

// Close the database connection
$conn->close();
?>
