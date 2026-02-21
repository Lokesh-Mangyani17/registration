<?php
/**
 * Approval email template (HTML).
 *
 * Variables available: $request, $reset_url, $site_name, $user.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;color:#333;line-height:1.6;max-width:600px;margin:0 auto;">
    <h2 style="color:#0073aa;">
        <?php
        printf(
            /* translators: %s: first name */
            esc_html__( 'Welcome, %s!', 'hcp-registration' ),
            esc_html( $request->first_name )
        );
        ?>
    </h2>
    <p>
        <?php
        printf(
            /* translators: %s: site name */
            esc_html__( 'Your Healthcare Professional registration on %s has been approved.', 'hcp-registration' ),
            esc_html( $site_name )
        );
        ?>
    </p>
    <p><?php esc_html_e( 'To get started, please set up your password by clicking the button below:', 'hcp-registration' ); ?></p>
    <p style="text-align:center;margin:30px 0;">
        <a href="<?php echo esc_url( $reset_url ); ?>"
           style="background:#0073aa;color:#fff;padding:12px 30px;text-decoration:none;border-radius:4px;display:inline-block;font-size:16px;">
            <?php esc_html_e( 'Set Your Password', 'hcp-registration' ); ?>
        </a>
    </p>
    <p style="font-size:13px;color:#666;">
        <?php esc_html_e( 'If the button above does not work, copy and paste the following URL into your browser:', 'hcp-registration' ); ?><br>
        <a href="<?php echo esc_url( $reset_url ); ?>"><?php echo esc_url( $reset_url ); ?></a>
    </p>
    <hr style="border:none;border-top:1px solid #eee;margin:30px 0;">
    <p style="font-size:12px;color:#999;">
        <?php
        printf(
            /* translators: %s: site name */
            esc_html__( 'This email was sent by %s.', 'hcp-registration' ),
            esc_html( $site_name )
        );
        ?>
    </p>
</body>
</html>
