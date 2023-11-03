<?php
require 'simple_html_dom.php'; // Include the PHP Simple HTML DOM Parser library.

// Database connection settings.
$db_host = 'your_database_host';
$db_user = 'your_database_user';
$db_pass = 'your_database_password';
$db_name = 'your_database_name';

try {
    // Create a database connection.
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // URL of the Wikipedia page to scrape.
    $url = 'https://www.wikipedia.org/';

    // Make an HTTP GET request to the URL.
    $response = file_get_contents($url);

    // Parse the HTML content.
    $html = str_get_html($response);

    // Extract headings, abstracts, pictures, and links from the page sections.
    $sections = $html->find('div.mw-parser-output > *'); // Adjust the selector to match the actual structure of the page.

    foreach ($sections as $section) {
        $heading = $section->find('span.mw-headline', 0)->plaintext;
        $abstract = $section->find('p', 0)->plaintext;
        $picture = ''; // Extract the picture URL if available.
        $links = $section->find('a'); // Extract links from the section.

        // Iterate through the links in the section.
        foreach ($links as $link) {
            $url = $link->href; // Extract the URL of the link.
            // Save the extracted data into the database table 'wiki_sections'.
            $query = "INSERT INTO wiki_sections (title, url, picture, abstract) VALUES (:title, :url, :picture, :abstract)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':title', $heading);
            $stmt->bindParam(':url', $url);
            $stmt->bindParam(':picture', $picture);
            $stmt->bindParam(':abstract', $abstract);
            $stmt->execute();
        }
    }

    // Close the database connection.
    $pdo = null;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
