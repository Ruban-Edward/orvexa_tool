<!DOCTYPE html>

<head>
    <title>Hello World!</title>
</head>

<body>
    <h1>Hello World!</h1>
    <p><?php echo 'We are running PHP, version: ' . phpversion(); ?></p>
    <?
    $database = "orvexa";
    $user = "root";
    $password = "root";
    $host = "mysql";

    $conn = new PDO("mysql:host={$host};dbname={$database};charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT first_name, last_name, email_id FROM orvexa_user");
    $stmt->execute(); // Executing the query
    
    // Fetching all the results as an associative array
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results
    if ($users) {
        foreach ($users as $user) {
            echo "First Name: " . htmlspecialchars($user['first_name']) . "<br>";
            echo "Last Name: " . htmlspecialchars($user['last_name']) . "<br>";
            echo "Email ID: " . htmlspecialchars($user['email_id']) . "<br><br>";
        }
    } else {
        echo "No users found.";
    }
    ?>
</body>

</html>