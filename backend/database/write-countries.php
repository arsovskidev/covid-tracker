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

    $conn->beginTransaction();

    try {
        // Create pdo insert query. 
        $sql = "INSERT IGNORE INTO 
     countries (slug, country, iso2)
     VALUES (:slug, :country, :iso2)";

        $stmt = $conn->prepare($sql);

        echo "Writing countires in database...";

        // Foreach all of the countires and add them to the database.
        foreach ($countries as $country) {
            $values = [
                'slug' => $country["Slug"],
                'country' => $country["Country"],
                'iso2' => $country["ISO2"],
            ];
            $stmt->execute($values);
        }
        // Commiting all the queries.
        $conn->commit();
        echo "OK";
    } catch (Exception $e) {
        //Rollback the transaction if there is exceptions.
        $conn->rollBack();

        echo "There is an error writing countries. Please check logs!";
        file_put_contents('../logs/' . date("m-d-y") . '.log', $e . PHP_EOL, FILE_APPEND);
        die();
    }
}
