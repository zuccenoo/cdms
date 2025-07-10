<?php
session_start(); // Start session for login persistence

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Database Connection
    $db_server = "localhost";
    $db_user = "root";
    $db_pass = "versoza07";
    $db_name = "cdms";

    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed. Please try again later.");
    }

    $stmt = $conn->prepare("SELECT username, password, user_level FROM `accounts` WHERE username = ?");

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        if ($password === $row['password']) {
            $_SESSION['username'] = $username;
            $_SESSION['user_level'] = $row['user_level'];

            if ($row['user_level'] === 'admin') {
                header("Location: dashboard.html");
            } else {
                header("Location: dashboard.html");
            }
            exit();
        } else {
            echo "<script>alert('Invalid username or password.'); window.location.href = 'index.html';</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password.'); window.location.href = 'index.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
