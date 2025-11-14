<?php
echo "<h1>Email Test with Regular Password</h1>";

require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = 'shaniyasen20@gmail.com';
$mail->Password = 'your_actual_gmail_password'; // ⚠️ Use your REAL Gmail password here
$mail->SMTPSecure = 'tls';

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

try {
    $mail->setFrom('shaniyasen20@gmail.com', 'Test');
    $mail->addAddress('shaniyasen20@gmail.com');
    $mail->Subject = 'Test with Regular Password';
    $mail->Body = 'Testing with regular Gmail password.';
    
    if ($mail->send()) {
        echo "<p style='color: green; font-size: 20px;'>✅ SUCCESS! Email sent with regular password!</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>