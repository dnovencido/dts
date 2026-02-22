<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function send_mail($toEmail, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {

        // $mail->SMTPDebug = 2;
        // SMTP Settings
        // $mail->isSMTP();
        // $mail->Host       = 'mail.coa-dts.site';
        // $mail->SMTPAuth   = true;
        // $mail->Username   = 'no-reply@coa-dts.site';
        // $mail->Password   = 'noreply@2026';
        // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
        // $mail->Port       = 465;
        $mail->isSMTP();
        $mail->Host       = 'localhost'; // GoDaddy's internal relay
        $mail->SMTPAuth   = false;                          // No login needed inside their network
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        $mail->Port       = 25;

        // Email Headers
        $mail->setFrom('no-reply@coa-dts.site', 'Document Tracking System');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}