<?php
header("Access-Control-Allow-Origin: http://localhost/Helpdesk/");

// Set the admin email for approvals
$adminEmail = "smjbolotaulo@gmail.com"; 

$basePendingDir = 'uploads/pending/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['uploadedFile']) && $_FILES['uploadedFile']['error'] === UPLOAD_ERR_OK) {
        
        // Get category from POST data, or default to 'uncategorized'
        $category = $_POST['fileCategory'] ?? 'uncategorized';

        // Ensure pending subfolder exists
        $pendingDir = $basePendingDir . $category . '/';
        if (!is_dir($pendingDir)) {
            mkdir($pendingDir, 0777, true);
        }

        // Get original file name and user-supplied title
        $originalFileName = basename($_FILES['uploadedFile']['name']);
        $userTitle = trim($_POST['fileTitle']) ?: $originalFileName;

        // Build the target file path in the pending folder
        $targetFilePath = $pendingDir . $originalFileName;

        if (move_uploaded_file($_FILES['uploadedFile']['tmp_name'], $targetFilePath)) {
            // Generate a unique token
            $token = md5(uniqid(rand(), true));

            // Build the entry using variables defined above, including admin email
            $entry = [
                'token'       => $token,
                'file'        => $targetFilePath,         // Full path to the file in pending folder
                'category'    => $category,
                'filename'    => $originalFileName,
                'title'       => $userTitle,
                'uploaded_at' => time(),
                'email'       => $adminEmail              // Store the admin email here
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
            $approvalLink  = "http://helpdeskpharmacy.infinityfreeapp.com/process_approval.php?action=approve&token=" . urlencode($token);
            $rejectionLink = "http://helpdeskpharmacy.infinityfreeapp.com/process_approval.php?action=reject&token=" . urlencode($token);


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

            // Define a MIME boundary
            $uid = md5(uniqid(time()));

            // Build email headers
            $fromName  = "Help Desk";
            $fromEmail = "no-reply@example.com"; // Adjust if necessary
            $toEmail   = $adminEmail;

            $header  = "From: $fromName <$fromEmail>\r\n";
            $header .= "Reply-To: $fromEmail\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-Type: multipart/mixed; boundary=\"$uid\"\r\n\r\n";

            // Build the MIME body for the email
            $emailBody  = "--$uid\r\n";
            $emailBody .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $emailBody .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $emailBody .= $messageText."\r\n\r\n";
            // Attachment part
            $emailBody .= "--$uid\r\n";
            $emailBody .= "Content-Type: application/pdf; name=\"$fileNameForAttachment\"\r\n";
            $emailBody .= "Content-Transfer-Encoding: base64\r\n";
            $emailBody .= "Content-Disposition: attachment; filename=\"$fileNameForAttachment\"\r\n\r\n";
            $emailBody .= $fileDataEncoded."\r\n\r\n";
            $emailBody .= "--$uid--";

            // Send email using PHP's mail() function
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
