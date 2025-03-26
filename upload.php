<?php
header("Access-Control-Allow-Origin: https://lrhytz.github.io/Helpdesk/");
// upload.php
// This script handles file uploads and sends an email for admin approval

$adminEmail = "rhytz.133@gmail.com";
$basePendingDir = 'uploads/pending/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        $category = isset($_POST['fileCategory']) ? $_POST['fileCategory'] : 'uncategorized';
        $pendingDir = $basePendingDir . $category . '/';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0777, true);
        }
        $fileName = basename($_FILES['uploadedFile']['name']);
        $targetFilePath = $pendingDir . $fileName;
        if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $targetFilePath)) {
            $token = md5(uniqid(rand(), true));
            $entry = [
                'token'       => $token,
                'file'        => $targetFilePath,
                'category'    => $category,
                'filename'    => $fileName,
                'uploaded_at' => time()
            ];
            $tokensFile = 'pending_tokens.json';
            $tokensData = [];
            if (file_exists($tokensFile)) {
                $json = file_get_contents($tokensFile);
                $tokensData = json_decode($json, true) ?: [];
            }
            $tokensData[] = $entry;
            file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

            // IMPORTANT: Change these URLs to your PHP host's public URL!
            $approvalLink = "https://helpdeskpharmacy.kesug.com/process_approval.php?action=approve&token=" . urlencode($token);
            $rejectionLink = "https://helpdeskpharmacy.kesug.com/process_approval.php?action=reject&token=" . urlencode($token);

            $subject = "New File Upload Pending Approval";
            $message = "A new file has been uploaded and is pending approval.\n\n";
            $message .= "File: $fileName\n";
            $message .= "Category: $category\n\n";
            $message .= "To approve the file, click here:\n$approvalLink\n\n";
            $message .= "To reject the file, click here:\n$rejectionLink\n\n";
            $message .= "This link is valid only for this upload.";

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
