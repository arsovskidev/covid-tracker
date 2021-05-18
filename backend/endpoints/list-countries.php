<?php
require_once '../config.php';

$stmt = $conn->query("SELECT * FROM countries");
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($countries);
