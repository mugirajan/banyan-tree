/**
 *
 * Template : Go-Rental HTML TEMPLATE
 * Author : ThemeWant
 * Author URI : https://themewant.com/
 *
 * Adapted for Baniyan Tree Travels:
 *  - clears the fields the form actually has (the original reset a
 *    #car input that no longer exists, and left phone/subject filled)
 *  - shows the response inside the form and scrolls it into view
 *  - disables the button while sending so it cannot be double-posted
 *
 **/

(function ($) {
    'use strict';

    var form = $('#contact-form');
    if (!form.length) {
        return;
    }

    var formMessages = $('#form-messages');
    var submitBtn = form.find('.rts-btn-send');
    var btnLabel = submitBtn.text();

    function showMessage(text, isError) {
        formMessages
            .removeClass('success error')
            .addClass(isError ? 'error' : 'success')
            .text(text)
            .show();

        // Bring the message into view - the button sits well below the
        // fold on a long form, so the reply is easy to miss otherwise.
        if (formMessages.length && formMessages[0].scrollIntoView) {
            formMessages[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    form.on('submit', function (e) {
        e.preventDefault();

        submitBtn.prop('disabled', true).text('Sending...');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json'
        })
            .done(function (response) {
                // php/contact.php always replies with {status, message}.
                // A validation failure comes back as 200 with
                // status:"error", so check the payload, not just the
                // HTTP code.
                if (response && response.status === 'error') {
                    showMessage(response.message, true);
                    return;
                }

                showMessage(
                    (response && response.message) || 'Thank you! Your message has been sent.',
                    false
                );

                // Reset every field, then put the nice-select widget
                // back to its placeholder (it mirrors the hidden
                // <select>, so clearing the select alone is not enough).
                form[0].reset();
                form.find('select').each(function () {
                    var $select = $(this);
                    var placeholder = $select.find('option').first().text();
                    $select.next('.nice-select')
                        .find('.current').text(placeholder).end()
                        .find('.option').removeClass('selected').end()
                        .find('.option').first().addClass('selected');
                });
            })
            .fail(function (xhr) {
                // Prefer the server's own wording when it sent JSON;
                // fall back to a generic line for network errors or a
                // PHP fatal that produced no parseable body.
                var text = 'Sorry, an error occurred and your message could not be sent.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    text = xhr.responseJSON.message;
                }
                showMessage(text, true);
            })
            .always(function () {
                submitBtn.prop('disabled', false).text(btnLabel);
            });
    });

})(jQuery);
