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
            $form.find('input[required]:not([type="checkbox"]):not([type="radio"]), select[required], textarea[required]').each(function () {
                if (!$(this).val().trim()) {
                    $(this).addClass('hcp-error');
                    valid = false;
                }
            });

            var email = $form.find('[name="email"]').val().trim();
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $form.find('[name="email"]').addClass('hcp-error');
                valid = false;
            }

            // Terms checkbox validation.
            if (!$form.find('[name="terms"]').is(':checked')) {
                valid = false;
            }

            if (!valid) {
                $message
                    .addClass('hcp-msg-error')
                    .text('Please fill in all required fields correctly and accept the terms and conditions.')
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
