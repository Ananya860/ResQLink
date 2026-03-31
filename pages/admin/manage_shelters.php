<?php
session_start();
require_once __DIR__ . "/../../DB/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if ((int)$_SESSION['role_id'] !== 2) {
    die("Access denied");
}

$success = "";
$error = "";

// Add shelter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $created_by = (int) $_SESSION['user_id'];
    $shelter_name = trim($_POST['shelter_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $total_capacity = (int) ($_POST['total_capacity'] ?? 0);
    $current_occupancy = (int) ($_POST['current_occupancy'] ?? 0);

    if ($shelter_name === '' || $address === '' || $city === '' || $total_capacity <= 0 || $current_occupancy < 0) {
        $error = "Please fill all fields correctly.";
    } elseif ($current_occupancy > $total_capacity) {
        $error = "Current occupancy cannot be greater than total capacity.";
    } else {
        if ($current_occupancy >= $total_capacity) {
            $status = "full";
        } else {
            $status = "open";
        }

        $stmt = $conn->prepare("
            INSERT INTO shelters
            (created_by, shelter_name, address, city, total_capacity, current_occupancy, status, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "isssiis",
            $created_by,
            $shelter_name,
            $address,
            $city,
            $total_capacity,
            $current_occupancy,
            $status
        );

        if ($stmt->execute()) {
            $success = "Shelter added successfully!";
        } else {
            $error = "Failed to add shelter.";
        }

        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM shelters ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Shelters - ResQLink</title>

    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #bb2d3b 100%);
            min-height: 100vh;
        }

        .page-card {
            width: 100%;
            max-width: 950px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
            padding: 2rem;
        }

        .page-title {
            color: #dc3545;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .shelter-box {
            background: #f8f9fa;
            border-left: 5px solid #dc3545;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .status-badge {
            text-transform: uppercase;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="../../index.php">
            <span class="badge bg-danger">ResQLink</span>
        </a>
    </div>
</nav>

<div class="container">
    <div class="page-card">
        <h2 class="page-title">Manage Shelters</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label class="form-label">Shelter Name</label>
                <input type="text" name="shelter_name" class="form-control" placeholder="Enter shelter name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address / Location</label>
                <input type="text" name="address" class="form-control" placeholder="Enter shelter address" required>
            </div>

            <div class="mb-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" placeholder="Enter city" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Capacity</label>
                    <input type="number" name="total_capacity" class="form-control" min="1" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Current Occupancy</label>
                    <input type="number" name="current_occupancy" class="form-control" min="0" value="0" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">Add Shelter</button>
                <a href="../dashboard.php" class="btn btn-secondary">Back</a>
            </div>
        </form>

        <hr>

        <h4 class="mb-3">All Shelters</h4>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $available = (int)$row['total_capacity'] - (int)$row['current_occupancy'];
                    if ($available < 0) {
                        $available = 0;
                    }
                ?>
                <div class="shelter-box">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-1 text-danger"><?php echo htmlspecialchars($row['shelter_name']); ?></h5>
                        <span class="badge bg-<?php echo $row['status'] === 'full' ? 'danger' : ($row['status'] === 'closed' ? 'secondary' : 'success'); ?> status-badge">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </span>
                    </div>

                    <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($row['address']); ?></p>
                    <p class="mb-1"><strong>City:</strong> <?php echo htmlspecialchars($row['city'] ?? 'N/A'); ?></p>
                    <p class="mb-1"><strong>Total Capacity:</strong> <?php echo (int)$row['total_capacity']; ?></p>
                    <p class="mb-1"><strong>Current Occupancy:</strong> <?php echo (int)$row['current_occupancy']; ?></p>
                    <p class="mb-0"><strong>Available Space:</strong> <?php echo $available; ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">No shelters added yet.</div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>