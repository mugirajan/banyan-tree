<?php
/**
 * Booking form handler - Baniyan Tree Travels
 *
 * Sends ride bookings over authenticated SMTP with PHPMailer, the
 * same transport php/contact.php uses (PHP's mail() does not work on
 * a stock XAMPP - there is no local mail server).
 *
 * Posted to by book-now.html via AJAX (assets/js/booking.js).
 * Always replies with JSON:
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

// Keys, advance amount and which methods must prepay.
require __DIR__ . '/razorpay-config.php';

/* ---------------------------------------------------------------
 * SETTINGS - keep in step with php/contact.php
 * ------------------------------------------------------------- */
const SMTP_HOST = 'smtp.hostinger.com';
const SMTP_PORT = 587;
const SMTP_USER = 'contact@blackitechs.com';
const SMTP_PASS = 'Contact@bits#737';

// The mailbox that receives bookings. TODO: change to the client's
// real inbox before going live.
const MAIL_TO      = 'info@baniyantravels.com';
const MAIL_TO_NAME = 'Baniyan Tree Travels';

// Must be an address on the authenticated domain, or the SMTP server
// will reject it. The customer goes in Reply-To instead.
const MAIL_FROM      = 'contact@blackitechs.com';
const MAIL_FROM_NAME = 'Baniyan Tree Travels Website';

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

/** Turn a posted value into its readable label, or '-' when empty. */
function label_for($value, array $map)
{
    if ($value === '') {
        return '-';
    }
    return isset($map[$value]) ? $map[$value] : $value;
}

$service_type   = field('service_type');
$pickup_date    = field('pickup_date');
$pickup_time    = field('pickup_time');
$pickup         = field('pickup_location');
$drop           = field('drop_location');
$vehicle        = field('vehicle_type');
$passengers     = field('passengers');
$trip_type      = field('trip_type');
$distance_km    = field('distance_km');
$duration_hours = field('duration_hours');
$name           = field('name');
$phone          = field('phone');
$email          = field('email');
$company        = field('company');
$payment        = field('payment_method');
$fare_estimate  = field('fare_estimate');

// The instructions box keeps its line breaks.
$instructions = isset($_POST['instructions'])
    ? trim(strip_tags((string) $_POST['instructions']))
    : '';

// Add-on checkboxes arrive as services[].
$service_labels = array(
    'ac'         => 'Air Conditioning',
    'wifi'       => 'WiFi Available',
    'water'      => 'Complimentary Water',
    'newspaper'  => 'Newspaper',
    'child_seat' => 'Child Seat',
    'wheelchair' => 'Wheelchair Accessible',
);

$addons = array();
if (isset($_POST['services']) && is_array($_POST['services'])) {
    foreach ($_POST['services'] as $key) {
        $key = trim(strip_tags((string) $key));
        if (isset($service_labels[$key])) {
            $addons[] = $service_labels[$key];
        }
    }
}
$addons_text = $addons ? implode(', ', $addons) : 'None requested';

/* ---- Validation ---------------------------------------------- */
if ($pickup === '' || $drop === '' || $vehicle === '' || $name === '' || $phone === '') {
    reply('error', 'Please complete all required fields and try again.');
}

if ($service_type === 'scheduled' && ($pickup_date === '' || $pickup_time === '')) {
    reply('error', 'Please choose the pick-up date and time for a scheduled booking.');
}

if ($payment === '') {
    reply('error', 'Please choose how you would like to pay.');
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    reply('error', 'Please enter a valid email address.');
}

/* ---- Payment ---------------------------------------------------
 * Online payments go through Razorpay Checkout before this file is
 * reached. The browser then posts the three razorpay_* values back,
 * and the signature is checked HERE - a success message from the
 * browser alone proves nothing, since anyone can post to this
 * endpoint.
 *
 * Cash settles after the trip, so it passes straight through with
 * no payment attached.
 * -------------------------------------------------------------- */
$rzp_payment_id = field('razorpay_payment_id');
$rzp_order_id   = field('razorpay_order_id');
$rzp_signature  = field('razorpay_signature');

$needs_prepay = in_array($payment, PREPAID_METHODS, true) && razorpay_is_configured();
$payment_status = 'Not applicable - settled after the trip';

if ($needs_prepay) {
    if ($rzp_payment_id === '' || $rzp_order_id === '' || $rzp_signature === '') {
        reply('error', 'We did not receive the payment confirmation. Please try the payment again, or choose Cash Payment.');
    }

    // Razorpay signs "order_id|payment_id" with the key secret.
    $expected = hash_hmac('sha256', $rzp_order_id . '|' . $rzp_payment_id, RAZORPAY_KEY_SECRET);

    if (!hash_equals($expected, $rzp_signature)) {
        error_log('Booking payment signature mismatch for order ' . $rzp_order_id);
        reply('error', 'We could not verify your payment. No booking has been made. Please contact us on +91 98765 43210 before trying again.');
    }

    $payment_status = 'PAID - advance of ₹' . number_format(BOOKING_ADVANCE_INR)
        . ' received (payment ' . $rzp_payment_id . ')';
} elseif (in_array($payment, PREPAID_METHODS, true)) {
    // Keys are not in place yet, so no money could have moved.
    $payment_status = 'Online payment not configured - collect from the customer';
}

/* ---- Readable labels ----------------------------------------- */
$service_type_label = label_for($service_type, array(
    'immediate' => 'Book Now (Immediate)',
    'scheduled' => 'Schedule for Later',
));

$vehicle_label = label_for($vehicle, array(
    'hatchback' => 'Hatchback',
    'sedan'     => 'Sedan',
    'suv'       => 'SUV',
    'muv'       => 'MUV (Innova / Ertiga class)',
    'premium'   => 'Premium Car',
    'luxury'    => 'Luxury Car',
    'tempo'     => 'Tempo Traveller',
));

$trip_type_label = label_for($trip_type, array(
    'oneway'    => 'One Way',
    'roundtrip' => 'Round Trip',
    'hourly'    => 'Hourly Rental',
));

$payment_label = label_for($payment, array(
    'cash'   => 'Cash Payment',
    'online' => 'Online Payment (UPI / Card / Netbanking)',
));

$when = $service_type === 'scheduled'
    ? trim($pickup_date . ' at ' . $pickup_time)
    : 'As soon as possible';

$trip_size = $trip_type === 'hourly'
    ? ($duration_hours !== '' ? $duration_hours . ' hour(s)' : '-')
    : ($distance_km !== '' ? $distance_km . ' km (approx.)' : '-');

/* ---------------------------------------------------------------
 * SEND
 * ------------------------------------------------------------- */
$e = function ($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
};

$rows = array(
    'Booking type'     => $e($service_type_label),
    'Pick-up when'     => $e($when),
    'Pick-up location' => $e($pickup),
    'Drop location'    => $e($drop),
    'Vehicle'          => $e($vehicle_label),
    'Passengers'       => $e($passengers !== '' ? $passengers : '-'),
    'Trip type'        => $e($trip_type_label),
    'Distance / hours' => $e($trip_size),
    'Fare estimate'    => $e($fare_estimate !== '' ? $fare_estimate : 'Not calculated'),
    'Passenger name'   => $e($name),
    'Phone'            => $e($phone),
    'Email'            => $e($email !== '' ? $email : '-'),
    'Company'          => $e($company !== '' ? $company : '-'),
    'Extras'           => $e($addons_text),
    'Instructions'     => $instructions !== '' ? nl2br($e($instructions)) : '-',
    'Payment method'   => $e($payment_label),
    'Payment status'   => $e($payment_status),
    'Razorpay order'   => $e($rzp_order_id !== '' ? $rzp_order_id : '-'),
);

$rows_html = '';
$i = 0;
foreach ($rows as $rlabel => $value) {
    $bg = ($i % 2 === 0) ? '#fafafa' : '#ffffff';
    $rows_html .= '<tr style="background:' . $bg . ';">'
        . '<td width="30%" style="font-weight:bold;color:#050B20;border-bottom:1px solid #ececec;vertical-align:top;">' . $rlabel . '</td>'
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
    if ($email !== '') {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(true);
    $mail->Subject = 'Ride booking: ' . $vehicle_label . ' - ' . $name . ' (' . $when . ')';

    $mail->Body = '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 0;">
<tr><td align="center">
<table width="650" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:10px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,.08);">

<tr>
<td style="background:#1A3D0A;padding:35px;text-align:center;">
<h1 style="margin:0;color:#ffffff;font-size:28px;">Baniyan Tree Travels</h1>
<p style="margin:10px 0 0;color:#dbe5d6;font-size:16px;">New Ride Booking</p>
</td>
</tr>

<tr>
<td style="padding:40px;">
<p style="margin:0;font-size:16px;color:#050B20;">Hello Team,</p>
<p style="font-size:15px;color:#555555;line-height:28px;">
A new booking request has come in through the <strong>Baniyan Tree Travels</strong> website.
Please confirm the car and the final fare with the customer.
</p>

<table width="100%" cellpadding="14" cellspacing="0" style="border-collapse:collapse;border:1px solid #ececec;margin-top:25px;">
' . $rows_html . '
</table>

<div style="margin-top:35px;padding:20px;background:#f1f6ee;border-left:5px solid #1A3D0A;">
<h3 style="margin-top:0;color:#1A3D0A;font-size:17px;">Confirm this booking</h3>
<p style="margin:8px 0;color:#555555;">
Call <strong>' . $e($name) . '</strong> on <strong>' . $e($phone) . '</strong>.
</p>
<p style="margin:8px 0;color:#555555;">
The fare estimate above is the figure the website showed the customer. It is an estimate only.
</p>
</div>
</td>
</tr>

<tr>
<td style="background:#1e1e1e;padding:30px;text-align:center;">
<h3 style="margin:0;color:#ffffff;">Baniyan Tree Travels</h3>
<p style="margin:10px 0;color:#d9d9d9;font-size:14px;line-height:24px;">
Car Rental &amp; Travel Services<br>
Safe &bull; Comfortable &bull; On Time
</p>
<p style="margin:15px 0 0;color:#bfbfbf;font-size:13px;">
This email was generated automatically from the website booking form.
</p>
<p style="margin-top:20px;color:#999999;font-size:12px;">
&copy; ' . date('Y') . ' Baniyan Tree Travels. All Rights Reserved.
</p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>';

    $alt = "New booking from the Baniyan Tree Travels website\n\n";
    foreach ($rows as $rlabel => $value) {
        $alt .= str_pad($rlabel . ':', 20) . html_entity_decode(strip_tags(str_replace('<br />', ' ', $value)), ENT_QUOTES, 'UTF-8') . "\n";
    }

    $mail->AltBody = $alt;

    $mail->send();

    reply('success', $needs_prepay
        ? 'Payment received, thank you! Your advance of ₹' . number_format(BOOKING_ADVANCE_INR)
            . ' is confirmed and your booking is placed. Our team will call you shortly to confirm the car and the balance fare.'
        : 'Thank you! Your booking request has been received. Our team will call you shortly to confirm the car and the fare.');

} catch (Exception $ex) {
    // Log the real reason; show the visitor something useful instead.
    error_log('Booking form SMTP failure: ' . $mail->ErrorInfo);

    http_response_code(500);
    reply('error', 'Sorry, we could not send your booking just now. Please call us on +91 98765 43210 or email info@baniyantravels.com.');
}
