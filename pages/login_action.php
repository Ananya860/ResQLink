<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

$login_input = trim($_POST['login_input'] ?? '');
$password = $_POST['password'] ?? '';

if ($login_input === '' || $password === '') {
    die("Please fill all fields. <br><a href='login.php'>Go Back</a>");
}

$stmt = $conn->prepare("SELECT id, role_id, full_name, email, phone, password_hash FROM users WHERE email = ? OR phone = ? LIMIT 1");
$stmt->bind_param("ss", $login_input, $login_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['full_name'] = $user['full_name'];

        header("Location: dashboard.php");
        exit;
    } else {
        echo "Incorrect password. <br><a href='login.php'>Try Again</a>";
    }
} else {
    echo "User not found. <br><a href='login.php'>Try Again</a>";
}

$stmt->close();
$conn->close();
?>