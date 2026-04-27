<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Fetch all missions for this user
$stmt = $conn->prepare("
    SELECT rm.*, er.request_type, er.address, er.priority, er.created_at as req_time
    FROM rescue_missions rm
    JOIN emergency_requests er ON rm.request_id = er.id
    WHERE rm.team_user_id = ?
    ORDER BY rm.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$missions = $stmt->get_result();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assigned Tasks — ResQLink</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-card { max-width: 900px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="page-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fa-solid fa-clipboard-list text-primary me-2"></i> My Assigned Tasks</h2>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Assigned At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($missions->num_rows > 0): while ($m = $missions->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars(ucfirst($m['request_type'])) ?></strong></td>
                        <td><span class="badge" style="background:<?= ($m['priority']=='critical'?'#c62828':'#f9a825') ?>"><?= ucfirst($m['priority']) ?></span></td>
                        <td><?= htmlspecialchars($m['address']) ?></td>
                        <td><span class="badge bg-info text-dark"><?= ucfirst(str_replace('_',' ',$m['mission_status'])) ?></span></td>
                        <td><?= date('M j, H:i', strtotime($m['created_at'])) ?></td>
                        <td><a href="view_task.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted italic">No tasks assigned to you yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
