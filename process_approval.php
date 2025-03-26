<?php
header("Access-Control-Allow-Origin: https://lrhytz.github.io/Helpdesk/");
// process_approval.php
// This script processes approval or rejection of a file upload based on a unique token.

// DEBUGGING FLAG: Set to true to always output GET parameters for troubleshooting.
$debug = true;
if ($debug) {
    echo "<pre>GET parameters received:\n" . print_r($_GET, true) . "</pre>";
}

// Define the path to the JSON file that stores pending upload tokens.
$tokensFile = 'pending_tokens.json';

// Ensure that the required GET parameters are provided.
if (!isset($_GET['action']) || !isset($_GET['token'])) {
    die("Invalid request: missing 'action' or 'token' parameter.");
}

$action = $_GET['action'];
$token  = $_GET['token'];

// Read the JSON file to get the pending tokens.
if (!file_exists($tokensFile)) {
    die("No pending tokens found.");
}

$tokensData = json_decode(file_get_contents($tokensFile), true) ?: [];

// Find the entry with the given token.
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
$pendingFilePath = $entry['file'];    // Path of the file in the pending folder.
$category        = $entry['category']; // The subtopic folder name.
$fileName        = $entry['filename']; // The original file name.

// Define the approved folder for this category.
$approvedDir = "uploads/$category/";
if (!is_dir($approvedDir)) {
    mkdir($approvedDir, 0777, true);
}
$approvedFilePath = $approvedDir . $fileName;

// Process based on the action (approve or reject).
if ($action === 'approve') {
    // Move file from pending to approved folder.
    if (rename($pendingFilePath, $approvedFilePath)) {
        $resultMsg = "File approved successfully.";
    } else {
        $resultMsg = "Error approving the file.";
    }
} elseif ($action === 'reject') {
    // Delete the file from the pending folder.
    if (file_exists($pendingFilePath)) {
        unlink($pendingFilePath);
        $resultMsg = "File rejected and deleted.";
    } else {
        $resultMsg = "File not found in pending folder.";
    }
} else {
    die("Invalid action specified.");
}

// Remove the processed token entry from the JSON data.
unset($tokensData[$entryIndex]);
$tokensData = array_values($tokensData); // Re-index the array.
file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

echo $resultMsg;
?>
