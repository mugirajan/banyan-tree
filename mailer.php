<?php
/**
 * Contact form handler - Baniyan Tree Travels
 *
 * Returns a short plain-text message and an HTTP status code.
 * assets/js/plugins/contact-form.js posts here over AJAX and shows
 * the response inside the form.
 *
 * ---------------------------------------------------------------
 * WHERE ENQUIRIES GO
 * ---------------------------------------------------------------
 * $recipient below is the inbox that receives every enquiry.
 *
 * Sending uses PHP's mail(), which needs a working mail transport.
 * Most shared hosting provides one; a stock XAMPP install does NOT,
 * which is why local submits report "Failed to connect to mailserver
 * at localhost port 25".
 *
 * Every submission is also appended to $log_file, so an enquiry is
 * never lost even when mail() fails. Keep that file out of the web
 * root on a live server, or delete the logging block once real SMTP
 * is in place.
 */

// Never let PHP notices or warnings print into the AJAX response -
// they would be shown to the visitor as the "message". Errors still
// go to the PHP error log.
ini_set('display_errors', '0');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

// TODO: change this to the address that should receive enquiries.
$recipient = 'info@baniyantravels.com';
$log_file  = __DIR__ . '/contact-submissions.log';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo 'There was a problem with your submission, please try again.';
    exit;
}

/** Read a POST field safely: missing keys return '' instead of warning. */
function field($key)
{
    if (!isset($_POST[$key])) {
        return '';
    }
    $value = trim((string) $_POST[$key]);
    // Collapse newlines so they cannot be used to inject mail headers.
    return str_replace(array("\r", "\n"), ' ', strip_tags($value));
}

$name       = field('name');
$email      = filter_var(field('email'), FILTER_SANITIZE_EMAIL);
$phone      = field('phone');
$subject    = field('subject');
$newsletter = field('newsletter') !== '' ? 'Yes' : 'No';

// The message keeps its line breaks, so it is read separately.
$message = isset($_POST['message'])
    ? trim(strip_tags((string) $_POST['message']))
    : '';

// Map the select's values back to readable labels for the email.
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

// Validate the required fields.
if ($name === '' || $message === '' || $phone === '' || $subject === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Please complete all required fields with a valid email address, then try again.';
    exit;
}

$mail_subject = 'Website enquiry: ' . $subject_label . ' - ' . $name;

$body  = "New enquiry from the Baniyan Tree Travels website\n";
$body .= str_repeat('-', 48) . "\n\n";
$body .= "Name:       $name\n";
$body .= "Email:      $email\n";
$body .= "Phone:      $phone\n";
$body .= "Subject:    $subject_label\n";
$body .= "Newsletter: $newsletter\n\n";
$body .= "Message:\n$message\n";

// From must be an address on your own domain or most mail servers
// reject it. The visitor's address goes in Reply-To, so hitting
// reply in your inbox still answers them directly.
$headers  = 'From: Baniyan Tree Travels <' . $recipient . ">\r\n";
$headers .= 'Reply-To: ' . $name . ' <' . $email . ">\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

// Keep a copy regardless of whether mail() succeeds.
@file_put_contents(
    $log_file,
    '[' . date('Y-m-d H:i:s') . "]\n" . $body . "\n\n",
    FILE_APPEND | LOCK_EX
);

// @ suppresses the connection warning; the boolean tells us what
// happened and the real reason is written to the PHP error log.
if (@mail($recipient, $mail_subject, $body, $headers)) {
    http_response_code(200);
    echo 'Thank you! Your message has been sent. Our team will get back to you shortly.';
} else {
    http_response_code(500);
    echo 'Sorry, we could not send your message just now. Please call us on +91 98765 43210 or email info@baniyantravels.com.';
}
