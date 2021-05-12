<?php

function writeCountries($conn)
{
    // Get the countries from the API.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/countries');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $countries = curl_exec($cURLConnection);
    curl_close($cURLConnection);

    // Json decode them.
    $countries = json_decode($countries, true);

    // Create pdo insert query. 
    $sql = "INSERT INTO 
     countries (slug, country, iso2)
     VALUES (:slug, :country, :iso2)";

    $stmt = $conn->prepare($sql);

    echo "Writing countires in database...<br>";

    // Foreach all of the countires and add them to the database.
    foreach ($countries as $country) {
        $data = [
            'slug' => $country["Slug"],
            'country' => $country["Country"],
            'iso2' => $country["ISO2"],
        ];
        $stmt->execute($data);
    }

    echo "Finished.<br>";
}
