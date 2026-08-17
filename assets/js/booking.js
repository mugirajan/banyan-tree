/**
 * Booking form - Baniyan Tours and Travels (book-now.html)
 *
 * Four jobs:
 *   1. show only the fields that apply to the chosen options
 *      (date/time for a scheduled ride, km for a point-to-point trip,
 *      hours for an hourly rental)
 *   2. work out a fare ESTIMATE from the rate table below
 *   3. for online payment, collect the booking advance through
 *      Razorpay Checkout before the booking is placed
 *   4. post the booking to php/booking.php over AJAX
 *
 * Payment note: the amount and the keys live in
 * php/razorpay-config.php. This file never decides what to charge -
 * it asks php/razorpay-order.php to create the order, and the
 * signature that comes back is verified by php/booking.php. So a
 * visitor editing this script cannot change the price or fake a
 * payment.
 *
 * ---------------------------------------------------------------
 * FARE RATES - EDIT THESE
 * ---------------------------------------------------------------
 * The numbers below are placeholders so the Calculate Fare button
 * has something to work with. Replace them with the real tariff
 * before the site goes live: base = fixed starting charge, perKm =
 * running rate, hourly = rate for an hourly package. Amounts are in
 * rupees. The page always labels the result as an estimate and the
 * team confirms the final fare.
 */

(function ($) {
    'use strict';

    var form = $('#booking-form');
    if (!form.length) {
        return;
    }

    var RATES = {
        hatchback: { base: 300, perKm: 14, hourly: 250 },
        sedan:     { base: 350, perKm: 16, hourly: 300 },
        suv:       { base: 450, perKm: 20, hourly: 380 },
        muv:       { base: 500, perKm: 22, hourly: 420 },
        premium:   { base: 800, perKm: 30, hourly: 600 },
        luxury:    { base: 1200, perKm: 45, hourly: 900 },
        tempo:     { base: 700, perKm: 26, hourly: 500 }
    };

    var CHILD_SEAT_CHARGE = 250;   // one-off, only when the add-on is ticked
    var GST_RATE = 0.05;           // 5% on car rental
    var MIN_HOURLY_PACKAGE = 4;    // hourly bookings are billed for at least this many hours

    var ORDER_URL = 'php/razorpay-order.php';
    var PREPAID_METHODS = ['online'];   // must match PREPAID_METHODS in razorpay-config.php

    var messages = $('#booking-messages');
    var submitBtn = form.find('.btn-book');
    var fareBox = $('#fare-result');
    var fareList = $('#fare-lines');
    var fareField = form.find('input[name="fare_estimate"]');

    function money(value) {
        return '₹' + Math.round(value).toLocaleString('en-IN');
    }

    function showMessage(text, isError) {
        messages
            .removeClass('success error')
            .addClass(isError ? 'error' : 'success')
            .text(text)
            .show();

        if (messages[0].scrollIntoView) {
            messages[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function setButtonText($btn, text) {
        if ($btn.find('.rts__btn__wrap').length) {
            $btn.find('.rts__btn__txt-one, .rts__btn__txt-two').text(text);
        } else {
            $btn.text(text);
        }
    }

    /* ---------------------------------------------------------------
     * 1. Conditional fields
     * ------------------------------------------------------------- */
    function syncServiceType() {
        var scheduled = form.find('input[name="service_type"]:checked').val() === 'scheduled';

        $('#schedule-fields').toggleClass('is-hidden', !scheduled);
        $('#schedule-fields').find('input').prop('required', scheduled);

        // The submit button says what it will actually do.
        setButtonText(submitBtn, scheduled ? 'Schedule Booking' : 'Book Now');
    }


    function syncTripType() {
        var hourly = form.find('input[name="trip_type"]:checked').val() === 'hourly';

        $('#distance-field').toggleClass('is-hidden', hourly);
        $('#hours-field').toggleClass('is-hidden', !hourly);
    }

    function currentPaymentMethod() {
        return form.find('input[name="payment_method"]:checked').val() || '';
    }

    function isPrepaid(method) {
        return $.inArray(method, PREPAID_METHODS) !== -1;
    }

    form.on('change', 'input[name="service_type"]', syncServiceType);
    form.on('change', 'input[name="trip_type"]', syncTripType);
    syncServiceType();
    syncTripType();

    /* ---------------------------------------------------------------
     * 2. Fare estimate
     * ------------------------------------------------------------- */
    $('#calculate-fare').on('click', function () {
        var vehicle = form.find('select[name="vehicle_type"]').val();
        var tripType = form.find('input[name="trip_type"]:checked').val();
        var rate = RATES[vehicle];

        if (!rate) {
            showMessage('Please choose a vehicle type first, then calculate the fare.', true);
            return;
        }

        var lines = [];
        var subtotal = 0;

        if (tripType === 'hourly') {
            var hours = parseFloat(form.find('input[name="duration_hours"]').val());
            if (!hours || hours <= 0) {
                showMessage('Please enter how many hours you need the car for.', true);
                return;
            }
            var billedHours = Math.max(hours, MIN_HOURLY_PACKAGE);
            var hourlyCharge = billedHours * rate.hourly;

            lines.push(['Hourly package (' + billedHours + ' hrs × ' + money(rate.hourly) + ')', hourlyCharge]);
            if (billedHours > hours) {
                lines.push(['Minimum package is ' + MIN_HOURLY_PACKAGE + ' hours', 0]);
            }
            subtotal += hourlyCharge;
        } else {
            var km = parseFloat(form.find('input[name="distance_km"]').val());
            if (!km || km <= 0) {
                showMessage('Please enter the approximate distance in kilometres.', true);
                return;
            }
            if (tripType === 'roundtrip') {
                km = km * 2;
                lines.push(['Round trip - distance counted both ways', 0]);
            }
            var kmCharge = km * rate.perKm;

            lines.push(['Base fare', rate.base]);
            lines.push(['Distance (' + km + ' km × ' + money(rate.perKm) + ')', kmCharge]);
            subtotal += rate.base + kmCharge;
        }

        if (form.find('input[value="child_seat"]').is(':checked')) {
            lines.push(['Child seat', CHILD_SEAT_CHARGE]);
            subtotal += CHILD_SEAT_CHARGE;
        }

        var gst = subtotal * GST_RATE;
        lines.push(['GST (' + (GST_RATE * 100) + '%)', gst]);

        var total = subtotal + gst;

        var html = '';
        $.each(lines, function (i, line) {
            html += '<li><span>' + line[0] + '</span><span>' + (line[1] ? money(line[1]) : '—') + '</span></li>';
        });
        html += '<li class="total"><span>Estimated total</span><span>' + money(total) + '</span></li>';

        fareList.html(html);
        fareBox.removeClass('is-hidden');
        fareField.val(money(total));
        messages.hide();

        if (fareBox[0].scrollIntoView) {
            fareBox[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    /* ---------------------------------------------------------------
     * 3. Reset
     * ------------------------------------------------------------- */
    $('#reset-form').on('click', function () {
        form[0].reset();
        fareBox.addClass('is-hidden');
        fareField.val('');
        messages.hide();

        // niceSelect draws its own label, so put it back by hand.
        var select = form.find('select[name="vehicle_type"]');
        select.val('');
        if ($.fn.niceSelect) {
            select.niceSelect('update');
        }

        syncServiceType();
        syncTripType();
    });

    /* ---------------------------------------------------------------
     * 4. Submit
     *
     * Cash / corporate  ->  place the booking straight away.
     * Card / UPI        ->  create an order, take the advance through
     *                       Razorpay Checkout, and only place the
     *                       booking once the payment succeeds.
     * ------------------------------------------------------------- */
    var busyLabel = null;

    function setBusy(text) {
        if (busyLabel === null) {
            busyLabel = submitBtn.find('.rts__btn__txt-one').length ? submitBtn.find('.rts__btn__txt-one').text() : submitBtn.text();
        }
        submitBtn.prop('disabled', true);
        setButtonText(submitBtn, text);
    }

    function clearBusy() {
        submitBtn.prop('disabled', false);
        setButtonText(submitBtn, busyLabel === null ? 'Book Now' : busyLabel);
        busyLabel = null;
    }


    /** Post the whole form, plus the payment fields when there are any. */
    function placeBooking(payment) {
        var data = form.serialize();

        if (payment) {
            data += '&razorpay_payment_id=' + encodeURIComponent(payment.razorpay_payment_id)
                + '&razorpay_order_id=' + encodeURIComponent(payment.razorpay_order_id)
                + '&razorpay_signature=' + encodeURIComponent(payment.razorpay_signature);
        }

        setBusy('Sending...');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: data,
            dataType: 'json'
        })
            .done(function (response) {
                // php/booking.php always replies with {status, message}.
                // A validation failure comes back as 200 with
                // status:"error", so check the payload, not the code.
                if (response && response.status === 'error') {
                    showMessage(response.message, true);
                    return;
                }

                showMessage(
                    (response && response.message) || 'Thank you! Your booking request has been received.',
                    false
                );

                form[0].reset();
                fareBox.addClass('is-hidden');
                fareField.val('');
                syncServiceType();
                syncTripType();
            })
            .fail(function (xhr) {
                // A failure with status 0 means the request never reached
                // PHP at all - almost always the page was opened as a
                // file:// path, or Apache is not running. The details go
                // to the console so that is easy to see in DevTools.
                if (window.console && console.error) {
                    console.error('[booking] submit failed - HTTP status', xhr.status,
                        xhr.status === 0 ? '(request never reached the server: open the page through http://localhost/, not as a file, and make sure Apache is running)' : '',
                        xhr.responseText || '');
                }

                var res = xhr.responseJSON;
                var fallback = payment
                    // Money has already moved, so never tell the customer to just try again.
                    ? 'Your payment went through but we could not save the booking. Please call us on +91 98412 11173 and quote payment ' + payment.razorpay_payment_id + '.'
                    : 'Sorry, something went wrong. Please call us on +91 98412 11173.';

                showMessage((res && res.message) || fallback, true);
            })
            .always(clearBusy);
    }

    /** Ask the server for an order, then hand it to Razorpay Checkout. */
    function payThenBook() {
        if (typeof Razorpay === 'undefined') {
            showMessage('The payment window could not load. Please check your connection, or choose Cash Payment.', true);
            clearBusy();
            return;
        }

        setBusy('Opening payment...');

        $.ajax({
            type: 'POST',
            url: ORDER_URL,
            data: {
                payment_method: currentPaymentMethod(),
                name: form.find('input[name="name"]').val(),
                phone: form.find('input[name="phone"]').val()
            },
            dataType: 'json'
        })
            .done(function (order) {
                if (!order || order.status !== 'success') {
                    showMessage((order && order.message) || 'We could not start the payment. Please choose Cash Payment or call us.', true);
                    clearBusy();
                    return;
                }

                var checkout = new Razorpay({
                    key: order.key_id,
                    order_id: order.order_id,
                    amount: order.amount,
                    currency: order.currency,
                    name: 'Baniyan Tours and Travels',
                    description: 'Booking advance',
                    image: 'assets/images/logo/logo.webp',
                    prefill: {
                        name: form.find('input[name="name"]').val(),
                        email: form.find('input[name="email"]').val(),
                        contact: form.find('input[name="phone"]').val()
                        // No `method` here on purpose: the customer picks UPI,
                        // card, netbanking or a wallet inside Razorpay's window.
                    },
                    theme: { color: '#1A3D0A' },
                    modal: {
                        ondismiss: function () {
                            showMessage('Payment was cancelled, so no booking has been made. You can try again or choose Cash Payment.', true);
                            clearBusy();
                        }
                    },
                    handler: function (payment) {
                        // Razorpay says it succeeded; php/booking.php
                        // checks the signature before believing it.
                        placeBooking(payment);
                    }
                });

                checkout.on('payment.failed', function (resp) {
                    var reason = resp && resp.error && resp.error.description
                        ? ' (' + resp.error.description + ')'
                        : '';
                    showMessage('The payment did not go through' + reason + '. No booking has been made. Please try again or choose Cash Payment.', true);
                    clearBusy();
                });

                setBusy('Waiting for payment...');
                checkout.open();
            })
            .fail(function (xhr) {
                var res = xhr.responseJSON;
                showMessage((res && res.message) || 'We could not reach the payment gateway. Please choose Cash Payment or call us on +91 98412 11173.', true);
                clearBusy();
            });
    }

    form.on('submit', function (e) {
        e.preventDefault();

        // niceSelect hides the real <select>, and the browser refuses to
        // validate a hidden control - so the vehicle is checked here
        // instead of with a `required` attribute. php/booking.php
        // checks it again on the server.
        if (!form.find('select[name="vehicle_type"]').val()) {
            showMessage('Please choose a vehicle type.', true);
            return;
        }

        // No client-side gate on online payment: php/razorpay-order.php
        // is the authority on whether it can run, and its reply says
        // exactly what is wrong. Guessing here only produced a vaguer
        // message when the real problem was elsewhere.
        if (isPrepaid(currentPaymentMethod())) {
            payThenBook();
        } else {
            placeBooking(null);
        }
    });
})(jQuery);
