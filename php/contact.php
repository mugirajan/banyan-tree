<?php
/**
 * Contact form handler - Baniyan Tours and Travels
 *
 * Sends enquiries over authenticated SMTP with PHPMailer, so it works
 * on XAMPP as well as on the live server (PHP's mail() does not - a
 * stock XAMPP has no local mail transport).
 *
 * Posted to by contact.html via AJAX. Always replies with JSON:
 *   { "status": "success" | "error", "message": "..." }
 */

header('Content-Type: application/json; charset=utf-8');

// Never print PHP warnings into the JSON body - it would break
// parsing and leak server paths to visitors. Errors still reach the
// PHP error log.
ini_set('display_errors', '0');
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

/* ---------------------------------------------------------------
 * SETTINGS
 * ------------------------------------------------------------- */
const SMTP_HOST = 'smtp.hostinger.com';
const SMTP_PORT = 587;
const SMTP_USER = 'contact@blackitechs.com';
const SMTP_PASS = 'Contact@bits#737';

// The mailbox that receives enquiries. TODO: change to the client's
// real inbox (e.g. support@baniyantravels.com) before going live.
const MAIL_TO      = 'support@baniyantravels.com';
const MAIL_TO_NAME = 'Baniyan Tours and Travels';

// Must be an address on the authenticated domain, or the SMTP server
// will reject it. The visitor goes in Reply-To instead.
const MAIL_FROM      = 'contact@blackitechs.com';
const MAIL_FROM_NAME = 'Baniyan Tours and Travels Website';

// 0 = off. Set to 2 to write the SMTP conversation to the PHP error
// log while debugging a connection problem.
const SMTP_DEBUG = 0;

/* ---------------------------------------------------------------
 * INPUT
 * ------------------------------------------------------------- */
function reply($status, $message)
{
    echo json_encode(array('status' => $status, 'message' => $message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    reply('error', 'Invalid request.');
}

/** Trim a field and strip newlines so they cannot inject mail headers. */
function field($key)
{
    if (!isset($_POST[$key])) {
        return '';
    }
    return trim(str_replace(array("\r", "\n"), ' ', strip_tags((string) $_POST[$key])));
}

$name       = field('name');
$email      = field('email');
$phone      = field('phone');
$subject    = field('subject');
$newsletter = field('newsletter') !== '' ? 'Yes' : 'No';

// The message keeps its line breaks.
$message = isset($_POST['message']) ? trim(strip_tags((string) $_POST['message'])) : '';

if ($name === '' || $email === '' || $phone === '' || $subject === '' || $message === '') {
    reply('error', 'Please complete all required fields and try again.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    reply('error', 'Please enter a valid email address.');
}

// Map the select's values back to readable labels.
$subject_labels = array(
    'corporate'  => 'Corporate Travel Enquiry',
    'airport'    => 'Airport Pick-Up and Drop',
    'outstation' => 'Outstation Trip',
    'daily'      => 'Daily Car Rental',
    'longterm'   => 'Long-Term Vehicle Rental',
    'quote'      => 'Request a Quote',
    'other'      => 'Other',
);
$subject_label = isset($subject_labels[$subject]) ? $subject_labels[$subject] : $subject;

/* ---------------------------------------------------------------
 * SEND
 * ------------------------------------------------------------- */
$e = function ($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};

$rows = array(
    'Name'       => $e($name),
    'Email'      => $e($email),
    'Phone'      => $e($phone),
    'Subject'    => $e($subject_label),
    'Newsletter' => $e($newsletter),
    'Message'    => nl2br($e($message)),
);

$rows_html = '';
$i = 0;
foreach ($rows as $label => $value) {
    $bg = ($i % 2 === 0) ? '#fafafa' : '#ffffff';
    $rows_html .= '<tr style="background:' . $bg . ';">'
        . '<td width="30%" style="font-weight:bold;color:#050B20;border-bottom:1px solid #ececec;vertical-align:top;">' . $label . '</td>'
        . '<td style="border-bottom:1px solid #ececec;color:#555555;line-height:26px;">' . $value . '</td>'
        . '</tr>';
    $i++;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    if (SMTP_DEBUG > 0) {
        $mail->SMTPDebug   = SMTP_DEBUG;
        $mail->Debugoutput = function ($str, $level) {
            error_log("SMTP: $str");
        };
    }

    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO, MAIL_TO_NAME);
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Website enquiry: ' . $subject_label . ' - ' . $name;

    $mail->Body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">
<tr><td align="center">
<table width="650" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,.08);">

<tr>
<td style="background:#1A3D0A;padding:35px;text-align:center;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Baniyan Tours and Travels</h1>
<p style="margin:10px 0 0;color:#dbe5d6;font-size:16px;">New Website Enquiry</p>
</td>
</tr>

<tr>
<td style="padding:40px;">
<p style="margin:0;font-size:16px;color:#050B20;">Hello Team,</p>
<p style="font-size:15px;color:#555555;line-height:28px;">
A new enquiry has been submitted through the <strong>Baniyan Tours and Travels</strong> website.
The customer details are below.
</p>

<table width="100%" cellpadding="14" cellspacing="0" style="border-collapse:collapse;border:1px solid #ececec;margin-top:25px;">
' . $rows_html . '
</table>

<div style="margin-top:35px;padding:20px;background:#f1f6ee;border-left:5px solid #1A3D0A;">
<h3 style="margin-top:0;color:#1A3D0A;font-size:17px;">Respond to this enquiry</h3>
<p style="margin:8px 0;color:#555555;">
Reply directly to this email to reach <strong>' . $e($name) . '</strong> at ' . $e($email) . '.
</p>
<p style="margin:8px 0;color:#555555;">
Or call them on <strong>' . $e($phone) . '</strong>.
</p>
</div>
</td>
</tr>

<tr>
<td style="background:#1e1e1e;padding:30px;text-align:center;">
<h3 style="margin:0;color:#ffffff;">Baniyan Tours and Travels</h3>
<p style="margin:10px 0;color:#d9d9d9;font-size:14px;line-height:24px;">
Car Rental &amp; Travel Services<br>
Safe &bull; Comfortable &bull; On Time
</p>
<p style="margin:15px 0 0;color:#bfbfbf;font-size:13px;">
This email was generated automatically from the website contact form.
</p>
<p style="margin-top:20px;color:#999999;font-size:12px;">
&copy; ' . date('Y') . ' Baniyan Tours and Travels. All Rights Reserved.
</p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>';

    $mail->AltBody = "New enquiry from the Baniyan Tours and Travels website\n\n"
        . "Name:       $name\n"
        . "Email:      $email\n"
        . "Phone:      $phone\n"
        . "Subject:    $subject_label\n"
        . "Newsletter: $newsletter\n\n"
        . "Message:\n$message\n";

    $mail->send();

    reply('success', 'Thank you! Your message has been sent. Our team will get back to you shortly.');

} catch (Exception $ex) {
    // Log the real reason; show the visitor something useful instead.
    error_log('Contact form SMTP failure: ' . $mail->ErrorInfo);

    http_response_code(500);
    reply('error', 'Sorry, we could not send your message just now. Please call us on +91 98412 11173 or email support@baniyantravels.com.');
}
