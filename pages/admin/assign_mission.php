<?php
session_start();
require_once __DIR__ . "/../../DB/db.php";

// Auth guard
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 5])) {
    header("Location: ../login.php");
    exit();
}

$msg = "";
$error = "";

// Handle assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $request_id = (int)$_POST['request_id'];
    $team_user_id = (int)$_POST['team_user_id'];

    if ($request_id > 0 && $team_user_id > 0) {
        $stmt = $conn->prepare("INSERT INTO rescue_missions (request_id, team_user_id, mission_status) VALUES (?, ?, 'assigned')");
        $stmt->bind_param("ii", $request_id, $team_user_id);
        
        if ($stmt->execute()) {
            // Update request status
            $conn->query("UPDATE emergency_requests SET status='assigned' WHERE id=$request_id");
            $msg = "Mission assigned successfully!";
        } else {
            $error = "Error assigning mission: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Please select both a request and a team member.";
    }
}

// Fetch pending requests
$pending_requests = $conn->query("SELECT * FROM emergency_requests WHERE status='pending' ORDER BY created_at DESC");

// Fetch rescue team users (role_id = 3)
$rescue_users = $conn->query("SELECT id, full_name FROM users WHERE role_id=3 AND is_active=1");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Mission — ResQLink</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        body { background: #f0f2f5; font-family: 'Plus Jakarta Sans', sans-serif; }
        .page-card { max-width: 800px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .btn-primary { background: #1565C0; border: none; }
        .btn-primary:hover { background: #0d47a1; }
    </style>
</head>
<body>
<div class="container">
    <div class="page-card">
        <h2 class="mb-4"><i class="fa-solid fa-clipboard-check text-primary me-2"></i> Assign Rescue Mission</h2>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Select Emergency Request</label>
                <select name="request_id" class="form-select" required>
                    <option value="">-- Select Pending Request --</option>
                    <?php while ($req = $pending_requests->fetch_assoc()): ?>
                        <option value="<?= $req['id'] ?>">
                            [<?= ucfirst($req['priority']) ?>] <?= htmlspecialchars($req['request_type']) ?> at <?= htmlspecialchars($req['address']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Assign to Rescue Team Member</label>
                <select name="team_user_id" class="form-select" required>
                    <option value="">-- Select Team Member --</option>
                    <?php while ($u = $rescue_users->fetch_assoc()): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="assign" class="btn btn-primary">Assign Mission</button>
                <a href="../dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </form>

        <hr class="my-4">

        <h4>Active Missions</h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $active_missions = $conn->query("
                        SELECT rm.*, er.request_type, u.full_name 
                        FROM rescue_missions rm
                        JOIN emergency_requests er ON rm.request_id = er.id
                        JOIN users u ON rm.team_user_id = u.id
                        ORDER BY rm.created_at DESC LIMIT 10
                    ");
                    while ($m = $active_missions->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($m['request_type']) ?></td>
                        <td><?= htmlspecialchars($m['full_name']) ?></td>
                        <td><span class="badge bg-info text-dark"><?= ucfirst($m['mission_status']) ?></span></td>
                        <td><?= date('M j, H:i', strtotime($m['created_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
