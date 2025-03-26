<?php
// getApprovedFiles.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: https://yourwebsite.com"); // Adjust to your frontend URL

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
        "fileUrl" => $dir . $file // You can modify this to a fully qualified URL
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
