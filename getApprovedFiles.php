<?php
// getApprovedFiles.php
// This script returns a JSON array of approved files for a given subtopic.
// It is hosted on your PHP-enabled server.

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://helpdeskpharmacy.infinityfreeapp.com"); // Update to match your actual domain

if (!isset($_GET['subtopic'])) {
    echo json_encode([]);
    exit;
}

$subtopic = $_GET['subtopic'];
$dir = "uploads/$subtopic/"; // Make sure this path matches the folder structure on InfinityFree

if (!is_dir($dir)) {
    echo json_encode([]);
    exit;
}

$files = array_diff(scandir($dir), array('.', '..'));
$result = [];
foreach ($files as $file) {
    $displayName = pathinfo($file, PATHINFO_FILENAME);
    $result[] = [
        "filename" => $file,
        "displayName" => $displayName,
        "fileUrl" => "https://helpdeskpharmacy.infinityfreeapp.com/$dir$file"  // Update URL to reflect the InfinityFree structure
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
