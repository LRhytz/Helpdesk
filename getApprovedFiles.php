<?php
// getApprovedFiles.php
// This script returns a JSON array of approved files for a given subtopic.
// It is hosted on your PHP-enabled server.

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost/Helpdesk/");  // Update to match your local XAMPP environment
  // Update to match your local XAMPP environment

if (!isset($_GET['subtopic'])) {
    echo json_encode([]);
    exit;
}

$subtopic = $_GET['subtopic'];
$dir = "uploads/$subtopic/"; // Update this path to match your local directory structure

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
        "fileUrl" => "http://localhost/Helpdesk/$dir$file"  // Update the URL for local access
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
