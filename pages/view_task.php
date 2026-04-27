<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mission_id = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

// Fetch mission details
$stmt = $conn->prepare("
    SELECT rm.*, er.request_type, er.description, er.address, er.priority, er.created_at as req_time
    FROM rescue_missions rm
    JOIN emergency_requests er ON rm.request_id = er.id
    WHERE rm.id = ? AND (rm.team_user_id = ? OR ? IN (SELECT id FROM users WHERE role_id IN (2, 5)))
");
$stmt->bind_param("iii", $mission_id, $user_id, $user_id);
$stmt->execute();
$mission = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$mission) {
    die("Mission not found or access denied.");
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['mission_status'];
    $remarks = trim($_POST['remarks'] ?? '');
    
    $stmt = $conn->prepare("UPDATE rescue_missions SET mission_status = ?, remarks = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_status, $remarks, $mission_id);
    
    if ($stmt->execute()) {
        if ($new_status === 'completed' || $new_status === 'failed') {
            $req_status = ($new_status === 'completed') ? 'resolved' : 'pending';
            $conn->query("UPDATE emergency_requests SET status='$req_status' WHERE id=" . $mission['request_id']);
        }
        header("Location: view_task.php?id=$mission_id&msg=updated");
        exit();
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mission Details — ResQLink</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-card { max-width: 700px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .status-badge { font-size: 14px; padding: 6px 12px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa-solid fa-truck-medical text-danger me-2"></i> Mission Details</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">Status updated successfully!</div>
        <?php endif; ?>

        <div class="card mb-4 border-0 bg-light">
            <div class="card-body">
                <h5 class="card-title text-primary"><?= htmlspecialchars(ucfirst($mission['request_type'])) ?> Request</h5>
                <p class="mb-2"><strong>Location:</strong> <?= htmlspecialchars($mission['address']) ?></p>
                <p class="mb-2"><strong>Priority:</strong> <span class="badge" style="background:<?= ($mission['priority']=='critical'?'#c62828':'#f9a825') ?>"><?= ucfirst($mission['priority']) ?></span></p>
                <p class="mb-2"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($mission['description'])) ?></p>
                <p class="mb-0 text-muted small">Requested on: <?= date('M j, Y H:i', strtotime($mission['req_time'])) ?></p>
            </div>
        </div>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Mission Status</label>
                <select name="mission_status" class="form-select">
                    <option value="assigned" <?= $mission['mission_status']=='assigned'?'selected':'' ?>>Assigned</option>
                    <option value="en_route" <?= $mission['mission_status']=='en_route'?'selected':'' ?>>En Route</option>
                    <option value="in_progress" <?= $mission['mission_status']=='in_progress'?'selected':'' ?>>In Progress</option>
                    <option value="completed" <?= $mission['mission_status']=='completed'?'selected':'' ?>>Completed</option>
                    <option value="failed" <?= $mission['mission_status']=='failed'?'selected':'' ?>>Failed / Aborted</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Remarks / Updates</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Enter any notes about the mission..."><?= htmlspecialchars($mission['remarks'] ?? '') ?></textarea>
            </div>

            <button type="submit" name="update_status" class="btn btn-danger w-100">Update Mission Status</button>
        </form>
    </div>
</div>
</body>
</html>
