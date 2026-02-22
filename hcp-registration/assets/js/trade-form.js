/**
 * Trade Application Form – Front-end JavaScript
 */
(function ($) {
    'use strict';

    $(function () {
        var $form    = $('#trade-application-form');
        var $message = $('#trade-form-message');
        var $btn     = $form.find('.hcp-submit-btn');
        var $spinner = $form.find('.trade-spinner');

        /* ---- Trustee toggle ---- */
        $form.on('change', 'input[name="acts_as_trustee"]', function () {
            if ($(this).val() === 'yes') {
                $('#trust-name-field').show();
            } else {
                $('#trust-name-field').hide().find('input').val('');
            }
        });

        /* ---- Postal same as physical toggle ---- */
        $form.on('change', 'input[name="postal_same_as_physical"]', function () {
            if ($(this).val() === 'no') {
                $('#postal-address-fields').show();
            } else {
                $('#postal-address-fields').hide();
            }
        });

        /* ---- Country "Other" toggle ---- */
        $form.on('change', '#physical_country', function () {
            if ($(this).val() === 'Other') {
                $('#physical-country-other-field').show();
            } else {
                $('#physical-country-other-field').hide().find('input').val('');
            }
        });

        $form.on('change', '#postal_country', function () {
            if ($(this).val() === 'Other') {
                $('#postal-country-other-field').show();
            } else {
                $('#postal-country-other-field').hide().find('input').val('');
            }
        });

        /* ---- Signature Pad ---- */
        var canvas = document.getElementById('trade-signature-pad');
        var ctx    = canvas ? canvas.getContext('2d') : null;
        var drawing = false;
        var hasDrawn = false;

        if (canvas && ctx) {
            ctx.strokeStyle = '#0b1d3a';
            ctx.lineWidth   = 2;
            ctx.lineCap     = 'round';

            canvas.addEventListener('mousedown', function (e) {
                drawing = true;
                hasDrawn = true;
                ctx.beginPath();
                ctx.moveTo(e.offsetX, e.offsetY);
            });
            canvas.addEventListener('mousemove', function (e) {
                if (!drawing) return;
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.stroke();
            });
            canvas.addEventListener('mouseup',   function () { drawing = false; });
            canvas.addEventListener('mouseleave', function () { drawing = false; });

            // Touch support.
            canvas.addEventListener('touchstart', function (e) {
                e.preventDefault();
                var rect = canvas.getBoundingClientRect();
                var touch = e.touches[0];
                drawing = true;
                hasDrawn = true;
                ctx.beginPath();
                ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            });
            canvas.addEventListener('touchmove', function (e) {
                e.preventDefault();
                if (!drawing) return;
                var rect = canvas.getBoundingClientRect();
                var touch = e.touches[0];
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.stroke();
            });
            canvas.addEventListener('touchend', function () { drawing = false; });

            $('#trade-clear-signature').on('click', function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                $('#trade_signature_data').val('');
                hasDrawn = false;
            });
        }

        /* ---- Form submission ---- */
        $form.on('submit', function (e) {
            e.preventDefault();

            // Clear previous state.
            $message.hide().removeClass('hcp-msg-success hcp-msg-error');
            $form.find('.hcp-error').removeClass('hcp-error');

            // Basic client-side validation.
            var valid = true;
            $form.find('input[required]:visible:not([type="checkbox"]):not([type="radio"]), select[required]:visible, textarea[required]:visible').each(function () {
                if (!$(this).val().trim()) {
                    $(this).addClass('hcp-error');
                    valid = false;
                }
            });

            // Email validation for all email-type inputs.
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            $form.find('input[type="email"]').each(function () {
                var val = $(this).val().trim();
                if (val && !emailPattern.test(val)) {
                    $(this).addClass('hcp-error');
                    valid = false;
                }
            });

            // Terms checkbox validation.
            if (!$form.find('[name="terms"]').is(':checked')) {
                valid = false;
            }

            if (!valid) {
                $message
                    .addClass('hcp-msg-error')
                    .text('Please fill in all required fields correctly and accept the terms of trade.')
                    .show();
                return;
            }

            // Capture signature data.
            if (canvas && hasDrawn) {
                $('#trade_signature_data').val(canvas.toDataURL('image/png'));
            }

            // Build FormData for file uploads.
            var formData = new FormData($form[0]);
            formData.append('action', 'trade_register');
            formData.append('nonce', tradeReg.nonce);

            // Disable button and show spinner.
            $btn.prop('disabled', true);
            $spinner.show();

            $.ajax({
                url:  tradeReg.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        $message
                            .addClass('hcp-msg-success')
                            .text(res.data.message)
                            .show();
                        $form[0].reset();
                        $('#trust-name-field').hide();
                        $('#postal-address-fields').hide();
                        if (ctx) {
                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            hasDrawn = false;
                        }
                    } else {
                        $message
                            .addClass('hcp-msg-error')
                            .text(res.data.message)
                            .show();
                    }
                },
                error: function () {
                    $message
                        .addClass('hcp-msg-error')
                        .text('An unexpected error occurred. Please try again.')
                        .show();
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $spinner.hide();
                }
            });
        });

        // Remove error styling on input.
        $form.on('input change', '.hcp-error', function () {
            $(this).removeClass('hcp-error');
        });
    });
})(jQuery);
