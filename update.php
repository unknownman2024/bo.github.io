<?php
// Function to fetch movie data
function fetchMovieData($movieName, $checkDate) {
    $mode = 'track';
    $url = "https://crudapi.trackboxoffice.com/livedata?movie_name=" . urlencode($movieName) . "&check_date=" . urlencode($checkDate) . "&mode=" . urlencode($mode);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing (not recommended for production)

    $response = curl_exec($ch);
    if ($response === FALSE) {
        die("Error fetching data from API: " . curl_error($ch));
    }

    curl_close($ch);

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error decoding JSON response: " . json_last_error_msg());
    }

    return $data;
}

// Function to fetch collection multipliers from JSON
function fetchCollectionMultipliers() {
    $url = 'https://boxoffice24.pages.dev/movies.json';
    $json = file_get_contents($url);
    if ($json === FALSE) {
        die("Error fetching JSON file");
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error decoding JSON file");
    }

    $multipliers = [];
    foreach ($data as $movie) {
        $multipliers[$movie['name']] = [
            'collection_multiplier' => floatval($movie['collection_multiplier']),
            'releaseDate' => $movie['releaseDate']
        ];
    }

    return $multipliers;
}

// Function to save data to JSON file
function saveDataToJson($file, $data) {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    if (file_put_contents($file, $jsonData) === FALSE) {
        die("Error saving data to JSON file");
    }
}

// Function to get IST formatted date and time
function getISTFormattedDateTime($dateTime) {
    $dateTime->setTimezone(new DateTimeZone('Asia/Kolkata')); // Set timezone to IST
    return $dateTime->format('Y-m-d h:i:s A'); // Format date and time with AM/PM
}

// Main function to update data
function updateMovieData($movieName, $releaseDate) {
    $movieMultipliers = fetchCollectionMultipliers();
    $today = new DateTime();
    $releaseDateObj = new DateTime($releaseDate);
    $dayNo = $today->diff($releaseDateObj)->days;

    $totalTrackedGross = 0;
    $totalTrackedFootfalls = 0;
    $totalTrackedShows = 0;

    for ($i = 0; $i <= $dayNo; $i++) {
        $date = clone $releaseDateObj;
        $date->modify("+$i day");
        $formattedDate = $date->format('Y-m-d');
        $data = fetchMovieData($movieName, $formattedDate);

        if (isset($data['Total Tracked Gross'])) {
            $totalTrackedGross += $data['Total Tracked Gross'];
            $totalTrackedFootfalls += $data['Tracked Footfalls'];
            $totalTrackedShows += $data['Tracked Shows'];
        }
    }

    if (isset($movieMultipliers[$movieName])) {
        $multiplier = $movieMultipliers[$movieName]['collection_multiplier'];
        $totalTrackedGross *= $multiplier;
        $totalTrackedFootfalls *= $multiplier;
        $totalTrackedShows *= $multiplier;
    }

    // Round off tracked gross, footfalls, and shows
    $totalTrackedGross = round($totalTrackedGross);
    $totalTrackedFootfalls = round($totalTrackedFootfalls);
    $totalTrackedShows = round($totalTrackedShows);

    // Load existing data from JSON file
    $file = 'data.json';
    $existingData = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

    // Update or add movie data
    $existingData[$movieName] = [
        'totalTrackedGross' => $totalTrackedGross,
        'totalTrackedFootfalls' => $totalTrackedFootfalls,
        'totalTrackedShows' => $totalTrackedShows,
        'trackedDate' => getISTFormattedDateTime($today) // Set date and time to IST with AM/PM
    ];

    // Save updated data to JSON file
    saveDataToJson($file, $existingData);
}

// Get query parameters
$movieName = isset($_GET['movie_name']) ? $_GET['movie_name'] : null;
$releaseDate = isset($_GET['release_date']) ? $_GET['release_date'] : null;

if ($movieName && $releaseDate) {
    updateMovieData($movieName, $releaseDate);
    // Redirect to data.json
    echo "Success $movieName.";

} else {
    echo "Invalid input. Please provide both movie_name and release_date.";
}
?>
