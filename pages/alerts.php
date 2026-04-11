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

    if (strpos($type, 'fire') !== false) return "🔥";
    if (strpos($type, 'flood') !== false) return "🌊";
    if (strpos($type, 'earthquake') !== false) return "🌍";
    if (strpos($type, 'cyclone') !== false) return "🌀";
    if (strpos($type, 'storm') !== false) return "🌪️";
    if (strpos($type, 'landslide') !== false) return "⛰️";
    if (strpos($type, 'accident') !== false) return "🚨";

    return "⚠️";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alerts - ResQLink</title>

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
    border-left: 5px solid #fff;
    padding: 20px;
    margin-bottom: 18px;
    border-radius: 10px;
    min-height: 230px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    background-size: cover;
    background-position: center;
    color: #fff;
}

.alert-title {
    font-weight: 700;
    margin-bottom: 8px;
}

.icon {
    font-size: 28px;
    margin-right: 8px;
}

.text-soft {
    color: rgba(255,255,255,0.9);
}
</style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">ResQLink</span>
    </div>
</nav>

<div class="container-box">

<h2 class="text-danger mb-4">Disaster Alerts</h2>

<?php if ($result && $result->num_rows > 0): ?>
<?php while ($row = $result->fetch_assoc()): ?>

<?php
$imageFile = !empty($row['image']) ? $row['image'] : 'default.jpg';
$imagePath = "../uploads/default_alerts/" . rawurlencode($imageFile);
?>

<div class="alert-card"
     style="background-image:
     linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
     url('<?php echo $imagePath; ?>');">

    <div class="d-flex justify-content-between">
        <div>
            <h4 class="alert-title">
                <span class="icon"><?php echo getDisasterIcon($row['alert_type']); ?></span>
                <?php echo htmlspecialchars(ucfirst($row['alert_type'])); ?>
            </h4>

            <small class="text-soft">
                Published: <?php echo $row['published_at']; ?>
            </small>
        </div>

        <span class="badge bg-warning text-dark">
            <?php echo $row['severity']; ?>
        </span>
    </div>

    <p class="mt-3"><strong>Location:</strong> <?php echo $row['location_text']; ?></p>
    <p><?php echo $row['instructions']; ?></p>

    <?php if (!$row['is_read']): ?>
        <a href="?read=<?php echo $row['notif_id']; ?>" class="btn btn-light btn-sm">
            Mark Read
        </a>
    <?php endif; ?>

</div>

<?php endwhile; ?>
<?php else: ?>
<div class="alert alert-info">No alerts found.</div>
<?php endif; ?>

<a href="dashboard.php" class="btn btn-secondary">Back</a>

</div>

</body>
</html>