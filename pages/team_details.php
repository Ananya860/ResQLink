<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['full_name'];

// Fetch team info
$stmt = $conn->prepare("SELECT * FROM rescue_teams WHERE members LIKE ? LIMIT 1");
$search = "%$username%";
$stmt->bind_param("s", $search);
$stmt->execute();
$team = $stmt->get_result()->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Team Details — ResQLink</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-card { max-width: 600px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .team-icon { width: 80px; height: 80px; background: #ffebee; color: #c62828; border-radius: 50%; display: grid; place-items: center; font-size: 35px; margin: 0 auto 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="page-card text-center">
        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary float-start"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="clearfix"></div>
        
        <div class="team-icon"><i class="fa-solid fa-people-group"></i></div>
        
        <?php if ($team): ?>
            <h2 class="mb-2 text-danger"><?= htmlspecialchars($team['team_name']) ?></h2>
            <p class="text-muted mb-4">Assigned Area: <?= htmlspecialchars($team['assigned_area']) ?></p>
            
            <div class="text-start bg-light p-4 rounded-3 mb-4">
                <h5 class="mb-3">Team Members</h5>
                <p><?= nl2br(htmlspecialchars($team['members'])) ?></p>
            </div>
            
            <div class="mb-3">
                <strong>Current Status:</strong> 
                <span class="badge bg-<?= $team['status']=='available'?'success':'warning' ?>"><?= ucfirst($team['status']) ?></span>
            </div>
        <?php else: ?>
            <h3>No Team Assigned</h3>
            <p class="text-muted">You are not currently assigned to any specific rescue team.</p>
        <?php endif; ?>
        
        <a href="dashboard.php" class="btn btn-danger px-5 mt-3">Back to Dashboard</a>
    </div>
</div>
</body>
</html>
