<?php
/**
 * Razorpay order endpoint - Baniyan Tree Travels
 *
 * Two jobs, both called by assets/js/booking.js:
 *
 *   GET  ?info=1   ->  {status, enabled, amount, amount_display}
 *                      so the page can show the advance amount and
 *                      hide online payment when keys are missing.
 *
 *   POST           ->  creates a Razorpay order and returns
 *                      {status, order_id, key_id, amount, currency}
 *                      which the browser hands to Razorpay Checkout.
 *
 * The amount ALWAYS comes from BOOKING_ADVANCE_INR in
 * razorpay-config.php. Anything the browser sends about price is
 * ignored - otherwise a visitor could edit the page and pay ₹1.
 *
 * Talks to Razorpay over plain cURL, so no Composer package or SDK
 * install is needed.
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require __DIR__ . '/razorpay-config.php';

function reply($data)
{
    echo json_encode($data);
    exit;
}

/* ---------------------------------------------------------------
 * GET: what should the page tell the customer?
 * ------------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    reply(array(
        'status'         => 'success',
        'enabled'        => razorpay_is_configured(),
        'amount'         => BOOKING_ADVANCE_INR,
        'amount_display' => '₹' . number_format(BOOKING_ADVANCE_INR),
    ));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    reply(array('status' => 'error', 'message' => 'Invalid request.'));
}

if (!razorpay_is_configured()) {
    reply(array(
        'status'  => 'error',
        'message' => 'Online payment is not set up on this site yet. Please choose Cash Payment, or call us on +91 98765 43210.',
    ));
}

/* ---------------------------------------------------------------
 * POST: create the order
 * ------------------------------------------------------------- */
$method = isset($_POST['payment_method']) ? trim((string) $_POST['payment_method']) : '';

if (!in_array($method, PREPAID_METHODS, true)) {
    reply(array(
        'status'  => 'error',
        'message' => 'This payment method does not need an online payment.',
    ));
}

$name  = isset($_POST['name']) ? substr(trim(strip_tags((string) $_POST['name'])), 0, 80) : '';
$phone = isset($_POST['phone']) ? substr(trim(strip_tags((string) $_POST['phone'])), 0, 20) : '';

// Razorpay works in the smallest currency unit, so rupees -> paise.
$payload = json_encode(array(
    'amount'          => BOOKING_ADVANCE_INR * 100,
    'currency'        => RAZORPAY_CURRENCY,
    // Receipts are capped at 40 characters by Razorpay.
    'receipt'         => substr('btt_' . date('ymdHis') . '_' . substr(preg_replace('/\D/', '', $phone), -4), 0, 40),
    'payment_capture' => 1,
    'notes'           => array(
        'purpose'   => 'Booking advance - Baniyan Tree Travels',
        'passenger' => $name,
        'phone'     => $phone,
    ),
));

$ch = curl_init('https://api.razorpay.com/v1/orders');
curl_setopt_array($ch, array(
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_USERPWD        => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
    CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
    CURLOPT_TIMEOUT        => 25,
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false) {
    error_log('Razorpay order cURL failure: ' . $curlErr);
    http_response_code(502);
    reply(array(
        'status'  => 'error',
        'message' => 'We could not reach the payment gateway. Please try again, or choose Cash Payment.',
    ));
}

$order = json_decode($response, true);

if ($httpCode >= 400 || !isset($order['id'])) {
    // The gateway's own wording is the most useful thing to log.
    error_log('Razorpay order rejected (' . $httpCode . '): ' . $response);
    http_response_code(502);
    reply(array(
        'status'  => 'error',
        'message' => 'The payment gateway refused the request. Please try again, or choose Cash Payment.',
    ));
}

reply(array(
    'status'   => 'success',
    'order_id' => $order['id'],
    // Only the PUBLIC key id goes to the browser. The secret stays here.
    'key_id'   => RAZORPAY_KEY_ID,
    'amount'   => $order['amount'],
    'currency' => $order['currency'],
));
