<?php

function getCountries($conn)
{
    // Get the countries from the API.
    // I am using the /summary endpoint instead of /countries because there are countries with 0 data.
    $cURLConnection = curl_init();

    curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.covid19api.com/summary');
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $countries = curl_exec($cURLConnection);

    // JSON decode them.
    $countries = json_decode($countries, true);
    $retry = 0;

    // If there is an connection error, retry to get response for 5 times with 5 seconds delay.
    while (empty($countries["Countries"]) && $retry < 5) {
        echo "Retrying connection in 5 seconds!\n";
        sleep(5);
        $countries = curl_exec($cURLConnection);
        $countries = json_decode($countries, true);
        $retry++;

        if ($retry === 5) {
            echo "Failed 5 times, aborting.\n";
            die();
        }
    }

    curl_close($cURLConnection);

    $allCountries = [];

    // Foreach all of the countires and add them to an array.
    foreach ($countries['Countries'] as $country) {
        $data = [
            'slug' => $country["Slug"],
            'country' => $country["Country"],
            'code' => $country["CountryCode"],
        ];
        array_push($allCountries, $data);
    }

    echo "Finished getting all the countries.\n";
    return $allCountries;
}
