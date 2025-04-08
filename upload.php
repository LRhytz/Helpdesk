<?php
// upload_file.php
header("Access-Control-Allow-Origin: https://helpdeskpharmacy.infinityfreeapp.com"); // Update to match your actual domain

$adminEmail = "smjbolotaulo@gmail.com";  // Admin email for approval notifications

$basePendingDir = 'uploads/pending/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        
        $category = $_POST['fileCategory'] ?? 'uncategorized';

        $pendingDir = $basePendingDir . $category . '/';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0777, true);
        }

        $originalFileName = basename($_FILES['uploadedFile']['name']);
        $userTitle = trim($_POST['fileTitle']) ?: $originalFileName;

        $targetFilePath = $pendingDir . $originalFileName;

        if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $targetFilePath)) {
            $token = md5(uniqid(rand(), true));

            $entry = [
                'token'       => $token,
                'file'        => $targetFilePath,
                'category'    => $category,
                'filename'    => $originalFileName,
                'title'       => $userTitle,
                'uploaded_at' => time(),
                'email'       => $adminEmail
            ];

            $tokensFile = 'pending_tokens.json';
            $tokensData = [];
            if (file_exists($tokensFile)) {
                $tokensData = json_decode(file_get_contents($tokensFile), true) ?: [];
            }
            $tokensData[] = $entry;
            file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

            $approvalLink  = "https://helpdeskpharmacy.infinityfreeapp.com/process_approval.php?action=approve&token=" . urlencode($token);
            $rejectionLink = "https://helpdeskpharmacy.infinityfreeapp.com/process_approval.php?action=reject&token=" . urlencode($token);

            $subject = "New File Upload Pending Approval";
            $messageText  = "A new file has been uploaded and is pending approval.\n\n";
            $messageText .= "File (original): $originalFileName\n";
            $messageText .= "User Title: $userTitle\n";
            $messageText .= "Category: $category\n\n";
            $messageText .= "To approve the file, click here:\n$approvalLink\n\n";
            $messageText .= "To reject the file, click here:\n$rejectionLink\n\n";
            $messageText .= "This link is valid only for this upload.\n\n";

            // Send email using PHP's mail() function (or use PHPMailer's advanced features)
            mail($adminEmail, $subject, $messageText);

            echo "File uploaded successfully and approval request sent.";
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
