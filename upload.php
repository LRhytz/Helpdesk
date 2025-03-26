<?php
header("Access-Control-Allow-Origin: https://lrhytz.github.io/Helpdesk/");
// upload.php
// This script handles file uploads and sends an email for admin approval

// Set your admin email address here
$adminEmail = "rhytz.133@gmail.com";

// Define the pending folder base directory
$basePendingDir = 'uploads/pending/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {

        // Get chosen subtopic from the form (e.g., "wi-receivables-subtopics")
        $category = isset($_POST['fileCategory']) ? $_POST['fileCategory'] : 'uncategorized';

        // Create the pending subfolder if it doesn't exist
        $pendingDir = $basePendingDir . $category . '/';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0777, true);
        }

        // Get original file name and define target file path
        $fileName = basename($_FILES['uploadedFile']['name']);
        $targetFilePath = $pendingDir . $fileName;

        // Move the uploaded file to the pending folder
        if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $targetFilePath)) {

            // Generate a unique token for this upload
            $token = md5(uniqid(rand(), true));

            // Prepare an entry to store in our JSON mapping
            $entry = [
                'token'     => $token,
                'file'      => $targetFilePath,
                'category'  => $category,
                'filename'  => $fileName,
                'uploaded_at' => time()
            ];

            // Read the existing tokens from pending_tokens.json (if it exists)
            $tokensFile = 'pending_tokens.json';
            $tokensData = [];
            if (file_exists($tokensFile)) {
                $json = file_get_contents($tokensFile);
                $tokensData = json_decode($json, true) ?: [];
            }
            // Append new entry
            $tokensData[] = $entry;
            // Save back to JSON file
            file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

            // Prepare approval and rejection links (adjust the URL as needed)
            $approvalLink = "http://localhost/helpdesk/process_approval.php?action=approve&token=" . urlencode($token);
            $rejectionLink = "http://localhost/helpdesk/process_approval.php?action=reject&token=" . urlencode($token);

            // Prepare email subject and message
            $subject = "New File Upload Pending Approval";
            $message = "A new file has been uploaded and is pending approval.\n\n";
            $message .= "File: $fileName\n";
            $message .= "Category: $category\n\n";
            $message .= "To approve the file, click here:\n$approvalLink\n\n";
            $message .= "To reject the file, click here:\n$rejectionLink\n\n";
            $message .= "This link is valid only for this upload.";

            // Send email (using PHP mail function for simplicity)
            if (mail($adminEmail, $subject, $message)) {
                echo "File uploaded successfully and sent for approval.";
            } else {
                echo "File uploaded, but failed to send email.";
            }
        } else {
            echo "Error moving the uploaded file.";
        }
    } else {
        echo "No file uploaded or an error occurred.";
    }
} else {
    echo "Invalid request method.";
}
?>
