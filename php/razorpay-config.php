<?php
/**
 * Razorpay settings - Baniyan Tree Travels
 *
 * Shared by php/razorpay-order.php (creates the order) and
 * php/booking.php (verifies the payment signature).
 *
 * ---------------------------------------------------------------
 * WHAT YOU MUST FILL IN
 * ---------------------------------------------------------------
 * 1. Sign in at https://dashboard.razorpay.com  ->  Settings  ->
 *    API Keys  ->  Generate Key.
 * 2. Paste the Key ID and Key Secret below.
 *      - Test keys start with  rzp_test_   (no real money moves)
 *      - Live keys start with  rzp_live_   (real money moves)
 * 3. Test the whole flow with the TEST key first. Razorpay's test
 *    cards are listed at https://razorpay.com/docs/payments/payments/test-card-details/
 *
 * Until real keys are in place, the site tells the customer that
 * online payment is not available and asks them to pick Cash
 * Payment. Nothing breaks and no payment is faked.
 *
 * KEEP THE SECRET PRIVATE. It never goes to the browser - only the
 * Key ID does. Do not commit real keys to a public repository, and
 * serve the live site over HTTPS.
 */

// TODO: replace both placeholders with your own keys.
const RAZORPAY_KEY_ID     = 'rzp_test_XXXXXXXXXXXXXX';
const RAZORPAY_KEY_SECRET = 'XXXXXXXXXXXXXXXXXXXXXXXX';

/**
 * The advance charged online to confirm a booking, in rupees.
 * The balance is collected after the trip. Change this one number
 * to change the advance everywhere - the page reads it from here.
 */
const BOOKING_ADVANCE_INR = 500;

/** Currency code sent to Razorpay. */
const RAZORPAY_CURRENCY = 'INR';

/**
 * Payment methods that must pay the advance online. Cash settles
 * after the trip, so it skips checkout. Razorpay's own window offers
 * UPI, cards, netbanking and wallets behind this single option.
 */
const PREPAID_METHODS = array('online');

/** True once real-looking keys have been entered. */
function razorpay_is_configured()
{
    return strpos(RAZORPAY_KEY_ID, 'XXXX') === false
        && strpos(RAZORPAY_KEY_SECRET, 'XXXX') === false
        && RAZORPAY_KEY_ID !== ''
        && RAZORPAY_KEY_SECRET !== '';
}
