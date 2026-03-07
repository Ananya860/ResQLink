<?php

session_start();

if (!isset($_SESSION['user_id'])) {

header("Location: login.php");
exit;

}

$full_name = $_SESSION['full_name'];
$role_id = $_SESSION['role_id'];

?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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