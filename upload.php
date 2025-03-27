<?php
header("Access-Control-Allow-Origin: http://localhost/Helpdesk/");

$adminEmail = "rhytz.133@gmail.com"; // Admin email for approvals
$basePendingDir = 'uploads/pending/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        $category = $_POST['fileCategory'] ?? 'uncategorized';

        // Ensure pending subfolder exists
        $pendingDir = $basePendingDir . $category . '/';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0777, true);
        }

        // Original file name
        $originalFileName = basename($_FILES['uploadedFile']['name']);

        // User-supplied title or fallback to original file name
        $userTitle = trim($_POST['fileTitle']) ?: $originalFileName;

        // Move to the pending folder using original filename
        $targetFilePath = $pendingDir . $originalFileName;

        if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $targetFilePath)) {
            // Generate a unique token
            $token = md5(uniqid(rand(), true));

            // Prepare an entry for pending_tokens.json
            $entry = [
                'token'    => $token,
                'file'     => $targetFilePath,   // Full path to the file in pending folder
                'category' => $category,
                // We'll keep the original filename but also store the userTitle
                'filename' => $originalFileName,
                'title'    => $userTitle,
                'uploaded_at' => time()
            ];

            // Append this entry to pending_tokens.json
            $tokensFile = 'pending_tokens.json';
            $tokensData = [];
            if (file_exists($tokensFile)) {
                $tokensData = json_decode(file_get_contents($tokensFile), true) ?: [];
            }
            $tokensData[] = $entry;
            file_put_contents($tokensFile, json_encode($tokensData, JSON_PRETTY_PRINT));

            // Build approval/rejection links
            $approvalLink  = "http://localhost/Helpdesk/process_approval.php?action=approve&token=" . urlencode($token);
            $rejectionLink = "http://localhost/Helpdesk/process_approval.php?action=reject&token=" . urlencode($token);

            // Email subject & message
            $subject = "New File Upload Pending Approval (Attached PDF)";
            $messageText  = "A new file has been uploaded and is pending approval.\n\n";
            $messageText .= "File (original): $originalFileName\n";
            $messageText .= "User Title: $userTitle\n";
            $messageText .= "Category: $category\n\n";
            $messageText .= "To approve the file, click here:\n$approvalLink\n\n";
            $messageText .= "To reject the file, click here:\n$rejectionLink\n\n";
            $messageText .= "This link is valid only for this upload.\n\n";
            $messageText .= "Please see the attached PDF file for reference.";

            // Attach the PDF
            $fileData = file_get_contents($targetFilePath);
            $fileDataEncoded = chunk_split(base64_encode($fileData));
            $fileNameForAttachment = $originalFileName;

            // MIME boundary
            $uid = md5(uniqid(time()));

            // Email headers
            $fromName  = "Help Desk";
            $fromEmail = "no-reply@example.com"; // Adjust if necessary
            $toEmail   = $adminEmail;

            $header  = "From: $fromName <$fromEmail>\r\n";
            $header .= "Reply-To: $fromEmail\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: multipart/mixed; boundary=\"$uid\"\r\n\r\n";

            // Build the MIME body
            $emailBody  = "--$uid\r\n";
            $emailBody .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $emailBody .= $messageText."\r\n\r\n";
            // The attachment part
            $emailBody .= "--$uid\r\n";
            $emailBody .= "Content-Type: application/pdf; name=\"$fileNameForAttachment\"\r\n";
            $emailBody .= "Content-Transfer-Encoding: base64\r\n";
            $emailBody .= "Content-Disposition: attachment; filename=\"$fileNameForAttachment\"\r\n\r\n";
            $emailBody .= $fileDataEncoded."\r\n\r\n";
            $emailBody .= "--$uid--";

            // Send email
            if (mail($toEmail, $subject, $emailBody, $header)) {
                echo "File uploaded successfully and email sent with attached PDF.";
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
