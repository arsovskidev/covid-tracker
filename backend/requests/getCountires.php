<?php
require_once '../config.php';

$stmt = $conn->query("SELECT * FROM countries");
$countries = $stmt->fetchAll();

echo json_encode($countries);
