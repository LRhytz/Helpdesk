<?php
// getApprovedFiles.php
// This script returns a JSON array of approved files for a given subtopic.
// It is hosted on your PHP-enabled server.

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://lrhytz.github.io/Helpdesk/");

if (!isset($_GET['subtopic'])) {
    echo json_encode([]);
    exit;
}

$subtopic = $_GET['subtopic'];
$dir = "uploads/$subtopic/";

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
        "fileUrl" => $dir . $file  // Modify if you need a fully qualified URL
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
