<?php
header("Access-Control-Allow-Origin: http://localhost/Helpdesk/");

// DEBUG FLAG
$debug = true;
if ($debug) {
    echo "<pre>GET parameters received:\n" . print_r($_GET, true) . "</pre>";
}

$tokensFile = 'pending_tokens.json';
if (!file_exists($tokensFile)) {
    die("No pending tokens found.");
}

if (!isset($_GET['action']) || !isset($_GET['token'])) {
    die("Invalid request: missing 'action' or 'token' parameter.");
}

$action = $_GET['action'];
$token  = $_GET['token'];

// Load tokens
$tokensData = json_decode(file_get_contents($tokensFile), true) ?: [];

// Find the entry for this token
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

// Pending file info
$pendingFilePath = $entry['file'];       // e.g., uploads/pending/wi-payables-subtopics/myfile.pdf
$category        = $entry['category'];   // e.g., wi-payables-subtopics
$originalName    = $entry['filename'];   // e.g., myfile.pdf
$userTitle       = $entry['title'] ?? $originalName; // user-supplied title or fallback

// Extract extension
$extension = pathinfo($originalName, PATHINFO_EXTENSION);

// If you want to keep spaces in the final name, we only remove truly invalid chars
// This pattern removes anything except letters, digits, dashes, underscores, and spaces
// If you want to keep punctuation, adjust accordingly.
$userTitleSafe = preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $userTitle);

// Build new file name with userTitle + extension
$newFileName = $userTitleSafe . '.' . $extension;

// Final approved directory
$approvedDir = "uploads/$category/";
if (!is_dir($approvedDir)) {
    mkdir($approvedDir, 0777, true);
}
$approvedFilePath = $approvedDir . $newFileName;

if ($action === 'approve') {
    // Move file from pending to final, using userTitle for the new name
    if (rename($pendingFilePath, $approvedFilePath)) {
        echo "File approved successfully. New filename: $newFileName";
    } else {
        echo "Error approving the file.";
    }
} elseif ($action === 'reject') {
    // Delete file from pending
    if (file_exists($pendingFilePath)) {
        unlink($pendingFilePath);
        echo "File rejected and deleted.";
    } else {
        echo "File not found in pending folder.";
    }
} else {
    die("Invalid action specified.");
}

// Remove token from JSON
unset($tokensData[$entryIndex]);
$tokensData = array_values($tokensData);
file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));
?>
