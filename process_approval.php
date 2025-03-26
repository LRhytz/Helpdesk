<?php
header("Access-Control-Allow-Origin: https://yourwebsite.com"); // Adjust to your frontend URL

$debug = false;
if ($debug) {
    echo "<pre>GET parameters received:\n" . print_r($_GET, true) . "</pre>";
}

$tokensFile = 'pending_tokens.json';

if (!isset($_GET['action']) || !isset($_GET['token'])) {
    die("Invalid request: missing 'action' or 'token' parameter.");
}

$action = $_GET['action'];
$token  = $_GET['token'];

if (!file_exists($tokensFile)) {
    die("No pending tokens found.");
}

$tokensData = json_decode(file_get_contents($tokensFile), true) ?: [];

$entryIndex = null;
foreach ($tokensData as $index => $entry) {
    if ($entry['token'] === $token) {
        $entryIndex = $index;
        break;
    }
}

if ($entryIndex === null) {
    die("Token not found or already processed.");
}

$entry = $tokensData[$entryIndex];
$pendingFilePath = $entry['file'];
$category        = $entry['category'];
$fileName        = $entry['filename'];

$approvedDir = "uploads/$category/";
if (!is_dir($approvedDir)) {
    mkdir($approvedDir, 0777, true);
}
$approvedFilePath = $approvedDir . $fileName;

if ($action === 'approve') {
    if (rename($pendingFilePath, $approvedFilePath)) {
        $resultMsg = "File approved successfully.";
    } else {
        $resultMsg = "Error approving the file.";
    }
} elseif ($action === 'reject') {
    if (file_exists($pendingFilePath)) {
        unlink($pendingFilePath);
        $resultMsg = "File rejected and deleted.";
    } else {
        $resultMsg = "File not found in pending folder.";
    }
} else {
    die("Invalid action specified.");
}

unset($tokensData[$entryIndex]);
$tokensData = array_values($tokensData);
file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

echo $resultMsg;
?>
