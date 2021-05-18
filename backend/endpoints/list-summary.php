<?php
require_once '../config.php';

$stmt = $conn->query("SELECT * FROM summary");
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
$country = 0;

if (isset($_GET["country"])) {
    if ($_GET["country"] == "global") {
        foreach ($summary as $value) {
            if ($value["id"] == "global") {
                $country = json_encode($value);
            }
        }
        echo $country;
    } else {
        foreach ($summary as $value) {
            if ($value["slug"] == $_GET["country"]) {
                $country = json_encode($value);
            }
        }
        echo $country;
    }
} else {
    echo json_encode($summary);
}
