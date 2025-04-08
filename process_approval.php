<?php
// process_approval.php
header("Access-Control-Allow-Origin: https://helpdeskpharmacy.infinityfreeapp.com"); // Update to match your actual domain

// 1) Place your `use` statements at the top level, outside any blocks:
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2) Include the PHPMailer files (make sure PHPMailer is uploaded to the server)
require 'libs/PHPMailer.php';
require 'libs/SMTP.php';
require 'libs/Exception.php';

$debug = false;
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
$originalName    = $entry['filename'];
$userTitle       = $entry['title'] ?? $originalName;

$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$userTitleSafe = preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $userTitle);
$newFileName = $userTitleSafe . '.' . $extension;

$approvedDir = "uploads/$category/";
if (!is_dir($approvedDir)) {
    mkdir($approvedDir, 0777, true);
}
$approvedFilePath = $approvedDir . $newFileName;

if ($action === 'approve') {
    if (rename($pendingFilePath, $approvedFilePath)) {
        echo "<h4 style='color: green;'>File approved successfully.</h4>";
        echo "<h4 style='color: black;'>New filename: $newFileName</h4>";

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'smjbolotaulo@gmail.com';  // Gmail address
            $mail->Password   = 'dpehuhlskyolzseu';        // Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('smjbolotaulo@gmail.com', 'Admin');
            $mail->addAddress($entry['email'], 'User');

            $mail->isHTML(true);
            $mail->Subject = 'File Approved';
            $mail->Body    = 'Your file has been approved. The new filename is: ' . $newFileName;

            $mail->send();
            echo 'Approval email sent successfully.';
        } catch (Exception $e) {
            echo "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        echo "<h4 style='color: red;'>Error approving the file.</h4>";
    }
} elseif ($action === 'reject') {
    if (file_exists($pendingFilePath)) {
        unlink($pendingFilePath);
        echo "<h2 style='color: red;'>File rejected and deleted.</h2>";
    } else {
        echo "<h2 style='color: yellow;'>File not found in pending folder.</h2>";
    }
} else {
    die("Invalid action specified.");
}

unset($tokensData[$entryIndex]);
$tokensData = array_values($tokensData);
file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));
?>
