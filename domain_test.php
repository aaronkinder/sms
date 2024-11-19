<?php
// Get the domain name from the server
$domain = $_SERVER['HTTP_HOST'];

// Display the domain with some basic HTML formatting
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }
        .domain-display {
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <h1>Domain Test</h1>
    <div class="domain-display">
        Current Domain: <strong><?php echo htmlspecialchars($domain); ?></strong>
    </div>
</body>
</html>
