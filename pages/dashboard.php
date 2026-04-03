<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$role_id = (int) $_SESSION['role_id'];

$count = 0;

if ($role_id != 2) {
    $q = $conn->query("
        SELECT COUNT(*) AS total
        FROM alert_notifications
        WHERE user_id = $user_id AND is_read = 0
    ");
    if ($q) {
        $count = (int) $q->fetch_assoc()['total'];
    }
}

$role_name = "User";
if ($role_id == 1) $role_name = "Citizen";
elseif ($role_id == 2) $role_name = "Admin";
elseif ($role_id == 3) $role_name = "Rescue Team";
elseif ($role_id == 4) $role_name = "Government";
elseif ($role_id == 5) $role_name = "System Admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ResQLink</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
            min-height: 100vh;
        }

        .card-box {
            max-width: 1000px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .title {
            color: #dc3545;
            font-weight: bold;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
        }

        .side-buttons a {
            width: 100%;
            margin-bottom: 10px;
        }

        .logout-center {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>

<div class="container mt-5">

<div class="card shadow p-4">

<h2>Welcome <?php echo htmlspecialchars($full_name); ?></h2>

<p>You are logged in successfully.</p>

<a href="logout.php" class="btn btn-danger">Logout</a>

</div>

</div>

</body>
</html>