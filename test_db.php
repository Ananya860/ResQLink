<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Step 1: test file running<br>";

require_once __DIR__ . "/DB/db.php";

echo "Step 2: db.php included<br>";

if ($conn) {
    echo "Step 3: Database Connected Successfully";
}
?>
