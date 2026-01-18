<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT * FROM SalonService";
$result = $conn->query($sql);

$services = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

echo json_encode($services);
exit();
?>