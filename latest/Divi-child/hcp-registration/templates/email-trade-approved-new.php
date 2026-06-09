<?php
/**
 * Trade Application approval email template for NEW users (HTML).
 *
 * Variables available: $request, $reset_url, $site_name, $user.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Your NUBU Trade Account has been approved', 'hcp-registration' ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#e6edf4;font-family:Arial,sans-serif;color:#0b1d3a;line-height:1.6;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#e6edf4;margin:0;padding:0;width:100%;font-family:Arial,sans-serif;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background-color:#e6edf4;margin:0 auto;width:100%;font-family:Arial,sans-serif;">
                    <tr>
                        <td style="padding:40px 32px;background-color:#e6edf4;color:#0b1d3a;font-family:Arial,sans-serif;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background-color:#ffffff;margin:0 auto;width:100%;font-family:Arial,sans-serif;">
                    <tr>
                        <td style="padding:40px 32px;color:#0b1d3a;font-family:Arial,sans-serif;">

                            <!-- Greeting -->
                            <h1 style="margin:0 0 24px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:700;">
                                <?php
                                printf(
                                    /* translators: %s: first name */
                                    esc_html__( 'Hi %s', 'hcp-registration' ),
                                    esc_html( $request->first_name )
                                );
                                ?>
                            </h1>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'We are pleased to inform you that your NUBU Trade Account has been approved.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php
                                printf(
                                    /* translators: %s: portal URL */
                                    wp_kses(
                                        __( 'Please read the information below for the conditions of using our wholesale ordering portal at <a href="%s" style="color:#0b1d3a;">https://nubupharma.com/my-account/</a>', 'hcp-registration' ),
                                        array( 'a' => array( 'href' => array(), 'style' => array() ) )
                                    ),
                                    'https://nubupharma.com/my-account/'
                                );
                                ?>
                            </p>

                            <!-- Signing in -->
                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Signing in', 'hcp-registration' ); ?>
                            </h2>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'To get started, please set up your password by clicking the button below:', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 24px 0;text-align:center;">
                                <a href="<?php echo esc_url( $reset_url ); ?>" style="background-color:#0b1d3a;color:#ffffff;display:inline-block;font-family:Arial,sans-serif;font-size:16px;font-weight:700;line-height:1.2;padding:14px 28px;text-decoration:none;border-radius:0;">
                                    <?php esc_html_e( 'Set Password', 'hcp-registration' ); ?>
                                </a>
                            </p>

                            <p style="margin:0 0 40px 0;font-family:Arial,sans-serif;font-size:14px;color:#0b1d3a;">
                                <?php esc_html_e( 'If the button above does not work, copy and paste the following URL into your browser:', 'hcp-registration' ); ?><br>
                                <a href="<?php echo esc_url( $reset_url ); ?>" style="color:#0b1d3a;text-decoration:underline;word-break:break-all;"><?php echo esc_url( $reset_url ); ?></a>
                            </p>

                            <!-- Controlled drugs -->
                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Controlled drugs', 'hcp-registration' ); ?>
                            </h2>

                            <ul style="margin:0 0 24px 0;padding-left:20px;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <li style="margin-bottom:12px;"><?php esc_html_e( 'Any packages containing orders of controlled drugs that are dispatched from our warehouse will be wrapped in a black plastic wrapping and will contain a shipping note outlining the contents along with batch numbers and quantities of any pharmaceutical products.', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:12px;"><?php esc_html_e( 'If there are any discrepancies on the shipping note vs what is actually received, please contact us via email or phone as soon as possible.', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:0;"><?php esc_html_e( 'If it seems like the black plastic wrapping has been attempted to be opened or if the package looks like it has been tampered with, please notify us immediately.', 'hcp-registration' ); ?></li>
                            </ul>

                            <!-- Shipment tracking -->
                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Shipment tracking', 'hcp-registration' ); ?>
                            </h2>

                            <ul style="margin:0 0 24px 0;padding-left:20px;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <li style="margin-bottom:12px;"><?php esc_html_e( 'Upon dispatch you will be emailed an automatically generated shipping note which will have a link to the tracking page for your order.', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:12px;"><?php esc_html_e( 'We dispatch all of our orders via overnight New Zealand Post. In most cases you should receive your order the following business day - if placed before 3pm.', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:0;"><?php esc_html_e( 'If you have not received your order after 3 business days please get in touch as we are able to follow up with New Zealand Post as the sender.', 'hcp-registration' ); ?></li>
                            </ul>

                            <!-- Section 29 returns -->
                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Section 29 returns', 'hcp-registration' ); ?>
                            </h2>

                            <p style="margin:0 0 16px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'All of our pharmaceutical products are Section 29 medicines and as such, at the end of each month we require you to return the following to us for any products you dispense. This in order to fulfil our obligations to the Ministry of Health:', 'hcp-registration' ); ?>
                            </p>

                            <ul style="margin:0 0 16px 0;padding-left:20px;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <li style="margin-bottom:8px;"><?php esc_html_e( 'Product name and quantity', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:8px;"><?php esc_html_e( 'Patient name', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:8px;"><?php esc_html_e( 'Prescribing Doctor', 'hcp-registration' ); ?></li>
                                <li style="margin-bottom:0;"><?php esc_html_e( 'Dispensing pharmacy/clinic', 'hcp-registration' ); ?></li>
                            </ul>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php
                                printf(
                                    /* translators: %s: email address */
                                    wp_kses(
                                        __( 'This information is to be sent to <a href="mailto:hq@nubupharma.com" style="color:#0b1d3a;">hq@nubupharma.com</a> within 5 business days after the end of the month. The most convenient way for us to receive this data is via a spreadsheet generated from your dispensing software, or alternatively a manually generated spreadsheet.', 'hcp-registration' ),
                                        array( 'a' => array( 'href' => array(), 'style' => array() ) )
                                    )
                                );
                                ?>
                            </p>

                            <!-- Product & driving information -->
                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Product &amp; driving information:', 'hcp-registration' ); ?>
                            </h2>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( "CMI's and terpene profiles are available for most of our products, these are accessible in our HCP platform in the education section, as well as under each product's page.", 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'Please feel free to reach out at any stage if you have any questions regarding product availability, delayed shipments or anything else.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 32px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php
                                printf(
                                    /* translators: %s: email address */
                                    wp_kses(
                                        __( 'Best contact is 0800 463 3226 or <a href="mailto:hq@nubupharma.com" style="color:#0b1d3a;">hq@nubupharma.com</a>.', 'hcp-registration' ),
                                        array( 'a' => array( 'href' => array(), 'style' => array() ) )
                                    )
                                );
                                ?>
                            </p>

                            <!-- Sign-off -->
                            <p style="margin:0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'Kind regards,', 'hcp-registration' ); ?><br>
                                <?php esc_html_e( 'The team at NUBU', 'hcp-registration' ); ?>
                            </p>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
