/**
 * HCP Registration Form – Front-end JavaScript
 */
(function ($) {
    'use strict';

    $(function () {
        var $form    = $('#hcp-registration-form');
        var $message = $('#hcp-form-message');
        var $btn     = $form.find('.hcp-submit-btn');
        var $spinner = $form.find('.hcp-spinner');

        $form.on('submit', function (e) {
            e.preventDefault();

            // Clear previous state.
            $message.hide().removeClass('hcp-msg-success hcp-msg-error');
            $form.find('.hcp-error').removeClass('hcp-error');

            // Basic client-side validation.
            var valid = true;
            $form.find('[required]').each(function () {
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

            if (!valid) {
                $message
                    .addClass('hcp-msg-error')
                    .text('Please fill in all required fields correctly.')
                    .show();
                return;
            }

            // Disable button and show spinner.
            $btn.prop('disabled', true);
            $spinner.show();

            $.ajax({
                url:  hcpReg.ajaxurl,
                type: 'POST',
                data: $form.serialize() + '&action=hcp_register&nonce=' + hcpReg.nonce,
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        $message
                            .addClass('hcp-msg-success')
                            .text(res.data.message)
                            .show();
                        $form[0].reset();
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
