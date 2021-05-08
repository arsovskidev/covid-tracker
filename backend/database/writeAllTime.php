<?php
function writeAllTimeStats($conn)
{
    // Get the countries from the API.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/summary');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $allTimeStats = curl_exec($cURLConnection);
    curl_close($cURLConnection);

    // Json decode them.
    $allTimeStats = json_decode($allTimeStats, true);

    $global = $allTimeStats["Global"];
    $countries = $allTimeStats["Countries"];

    // Create pdo insert query. 
    $sql = "INSERT INTO 
     all_time (slug, total_confirmed, total_deaths, total_recovered)
     VALUES (:slug, :total_confirmed, :total_deaths, :total_recovered)";

    $stmt = $conn->prepare($sql);

    echo "Writing alltime stats for countries in database...<br>";

    // Global.
    $data = [
        'slug' => "global",
        'total_confirmed' => $global["TotalConfirmed"],
        'total_deaths' => $global["TotalDeaths"],
        'total_recovered' => $global["TotalRecovered"],
    ];
    $stmt->execute($data);

    // Foreach all of the countires and add them to the database.
    foreach ($countries as $country) {
        $data = [
            'slug' => $country["Slug"],
            'total_confirmed' => $country["TotalConfirmed"],
            'total_deaths' => $country["TotalDeaths"],
            'total_recovered' => $country["TotalRecovered"],
        ];
        $stmt->execute($data);
    }

    echo "Finished.<br>";
}
