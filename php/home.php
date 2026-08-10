<?php

header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request"
    ]);
    exit;
}

$name     = trim($_POST['name'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$product  = trim($_POST['product'] ?? '');
$size     = trim($_POST['size'] ?? '');
$tonnage  = trim($_POST['tonnage'] ?? '');
$location = trim($_POST['location'] ?? '');
$message  = trim($_POST['message'] ?? '');

if ($name == "" || $phone == "" || $product == "") {
    echo json_encode([
        "status" => "error",
        "message" => "Name, phone number and product are required."
    ]);
    exit;
}

// Indian mobile numbers: 10 digits, optionally with +91 / 0 prefix and spacing
$digits = preg_replace('/\D/', '', $phone);
if (strlen($digits) < 10 || strlen($digits) > 13) {
    echo json_encode([
        "status" => "error",
        "message" => "Please enter a valid phone number."
    ]);
    exit;
}

// Optional rows are only rendered when the visitor filled them in
$optionalRows = '';
$optional = [
    'Size / Section'    => $size,
    'Tonnage'           => $tonnage,
    'Delivery Location' => $location,
];
$stripe = true;
foreach ($optional as $label => $value) {
    if ($value === '') continue;
    $bg = $stripe ? ' style="background:#fafafa;"' : '';
    $stripe = !$stripe;
    $optionalRows .= '
<tr' . $bg . '>
<td style="font-weight:bold;color:#333;border-bottom:1px solid #ececec;">' . htmlspecialchars($label) . '</td>
<td style="border-bottom:1px solid #ececec;color:#555;">' . htmlspecialchars($value) . '</td>
</tr>';
}

$mail = new PHPMailer(true);

try {

    $mail->isSMTP();

    $mail->Host = "smtp.hostinger.com";
    $mail->SMTPAuth = true;

    // CHANGE THESE
    $mail->Username = "contact@blackitechs.com";
    $mail->Password = "Contact@bits#737";

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // DEBUG
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        error_log("SMTP: $str");
    };

    $mail->CharSet = "UTF-8";

    // Sender
    $mail->setFrom(
        "contact@blackitechs.com",
        "SV Steels Website"
    );

    // Receiver
    $mail->addAddress("s.v.steelsandwires@hotmail.com");

    $mail->isHTML(true);

    $mail->Subject = "New Quote Request - " . $name . " (" . $product . ")";

    $mail->Body = '
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>

<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">

<tr>
<td align="center">

<table width="650" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,.08);">

<!-- Header -->
<tr>
<td style="background:#AE8333;padding:35px;text-align:center;">

<h1 style="margin:0;color:#ffffff;font-size:30px;">
SV Steels
</h1>

<p style="margin:10px 0 0;color:#ffffff;font-size:16px;">
New Quote Request
</p>

</td>
</tr>

<!-- Content -->
<tr>
<td style="padding:40px;">

<p style="margin:0;font-size:16px;color:#555;">
Hello Team,
</p>

<p style="font-size:15px;color:#666;line-height:28px;">
A new rate enquiry has been submitted through the <strong>SV Steels</strong> website quote form.
Please share today&rsquo;s Chennai rate, stock availability and a delivery slot.
</p>

<table width="100%" cellpadding="14" cellspacing="0" style="border-collapse:collapse;border:1px solid #ececec;margin-top:25px;">

<tr style="background:#fafafa;">
<td width="30%" style="font-weight:bold;color:#333;border-bottom:1px solid #ececec;">
Name
</td>

<td style="border-bottom:1px solid #ececec;color:#555;">
'.htmlspecialchars($name).'
</td>
</tr>

<tr>

<td style="font-weight:bold;color:#333;border-bottom:1px solid #ececec;">
Phone
</td>

<td style="border-bottom:1px solid #ececec;color:#555;">
'.htmlspecialchars($phone).'
</td>

</tr>

<tr style="background:#fafafa;">

<td style="font-weight:bold;color:#333;border-bottom:1px solid #ececec;">
Steel Product
</td>

<td style="border-bottom:1px solid #ececec;color:#555;">
'.htmlspecialchars($product).'
</td>

</tr>
'.$optionalRows.'
<tr>

<td style="font-weight:bold;color:#333;">
Additional Notes
</td>

<td style="color:#555;line-height:28px;">
'.($message !== '' ? nl2br(htmlspecialchars($message)) : '&mdash;').'
</td>

</tr>

</table>

<div style="margin-top:35px;padding:20px;background:#fff7eb;border-left:5px solid #AE8333;">

<h3 style="margin-top:0;color:#AE8333;">
Customer Contact Information
</h3>

<p style="margin:8px 0;color:#555;">
<strong>Name:</strong> '.htmlspecialchars($name).'
</p>

<p style="margin:8px 0;color:#555;">
<strong>Phone:</strong> <a href="tel:'.htmlspecialchars($digits).'" style="color:#AE8333;">'.htmlspecialchars($phone).'</a>
</p>

<p style="margin:8px 0;color:#555;">
Steel rates move daily &mdash; please respond to this enquiry at the earliest.
</p>

</div>

</td>
</tr>

<!-- Footer -->

<tr>

<td style="background:#1e1e1e;padding:30px;text-align:center;">

<h3 style="margin:0;color:#ffffff;">
SV Steels
</h3>

<p style="margin:10px 0;color:#d9d9d9;font-size:14px;line-height:24px;">
Premium Steel Solutions<br>
Quality &bull; Trust &bull; Excellence
</p>

<p style="margin:15px 0 0;color:#bfbfbf;font-size:13px;">
This email was automatically generated from the SV Steels website quote form.
</p>

<p style="margin-top:20px;color:#999;font-size:12px;">
&copy; '.date('Y').' SV Steels. All Rights Reserved.
</p>

</td>

</tr>

</table>

</td>
</tr>

</table>

</body>
</html>';

    $mail->AltBody =
        "Name : $name\nPhone : $phone\nProduct : $product\n" .
        "Size/Section : $size\nTonnage : $tonnage\nDelivery Location : $location\nNotes : $message";

    $mail->send();

    echo json_encode([
        "status" => "success",
        "message" => "Thanks! Your enquiry has been sent. We'll get back with today's rate shortly."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $mail->ErrorInfo,
        "exception" => $e->getMessage()
    ]);
}
