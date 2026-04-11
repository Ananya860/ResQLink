<?php
session_start();
require_once __DIR__ . "/../DB/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if (isset($_GET['read'])) {
    $nid = (int) $_GET['read'];
    $conn->query("UPDATE alert_notifications SET is_read = 1 WHERE id = $nid AND user_id = $user_id");
    header("Location: alerts.php");
    exit;
}

$sql = "
SELECT an.id AS notif_id, an.is_read,
       da.alert_type, da.location_text, da.severity, da.instructions, da.image, da.published_at
FROM alert_notifications an
JOIN disaster_alerts da ON an.alert_id = da.id
WHERE an.user_id = $user_id
ORDER BY da.published_at DESC
";

$result = $conn->query($sql);

function getDisasterIcon($type) {
    $type = strtolower(trim($type));

    if ($type == 'fire') return "🔥";
    if ($type == 'flood') return "🌊";
    if ($type == 'earthquake') return "🌍";
    if ($type == 'cyclone') return "🌀";
    if ($type == 'storm') return "🌪️";
    if ($type == 'landslide') return "⛰️";
    if ($type == 'accident') return "🚨";

    return "⚠️";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - ResQLink</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
            min-height: 100vh;
        }

        .container-box {
            max-width: 950px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .alert-card {
            border-left: 5px solid #dc3545;
            padding: 18px;
            margin-bottom: 15px;
            background: #f9f9f9;
            border-radius: 8px;
        }

        .alert-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .alert-title {
            color: #dc3545;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .icon {
            font-size: 28px;
            margin-right: 8px;
        }

        .severity-badge {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../index.php">
            <span class="badge bg-danger">ResQLink</span>
        </a>
    </div>
</nav>

<div class="container-box">

    <h2 class="text-danger mb-4">Disaster Alerts</h2>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="alert-card">

                <?php if (!empty($row['image'])): ?>
                    <img src="../uploads/default_alerts/<?php echo htmlspecialchars($row['image']); ?>" class="alert-image" alt="Alert Image">
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="alert-title">
                            <span class="icon"><?php echo getDisasterIcon($row['alert_type']); ?></span>
                            <?php echo htmlspecialchars($row['alert_type']); ?>
                        </h4>
                        <small class="text-muted">
                            Published: <?php echo htmlspecialchars($row['published_at']); ?>
                        </small>
                    </div>

                    <span class="badge bg-danger severity-badge">
                        <?php echo htmlspecialchars($row['severity']); ?>
                    </span>
                </div>

                <p class="mt-2 mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($row['location_text']); ?></p>
                <p class="mb-2"><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></p>

                <?php if (!$row['is_read']): ?>
                    <a href="?read=<?php echo $row['notif_id']; ?>" class="btn btn-success btn-sm">
                        Mark Read
                    </a>
                <?php endif; ?>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info">No published alerts found.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary">Back</a>

</div>

</body>
</html>