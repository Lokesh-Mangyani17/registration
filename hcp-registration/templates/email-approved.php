<?php
/**
 * Approval email template (HTML).
 *
 * Variables available: $request, $reset_url, $site_name, $user.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$trade_application_url = 'https://order.nubu.co.nz/trade-application-form/';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Your HCP account has been approved', 'hcp-registration' ); ?></title>
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
                            <h1 style="margin:0 0 24px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:700;">
                                <?php
                                printf(
                                    /* translators: %s: first name */
                                    esc_html__( 'Welcome, %s', 'hcp-registration' ),
                                    esc_html( $request->first_name )
                                );
                                ?>
                            </h1>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'We are pleased to inform you that your registration request is approved. We have prepared some relevant information for you to make the most of NUBU’s Healthcare Professional community.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'To get started, please set up your password by clicking the button below:', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 24px 0;text-align:center;">
                                <a href="<?php echo esc_url( $reset_url ); ?>" style="background-color:#0b1d3a;color:#ffffff;display:inline-block;font-family:Arial,sans-serif;font-size:16px;font-weight:700;line-height:1.2;padding:14px 28px;text-decoration:none;border-radius:0;">
                                    <?php esc_html_e( 'Set Password', 'hcp-registration' ); ?>
                                </a>
                            </p>

                            <p style="margin:0 0 32px 0;font-family:Arial,sans-serif;font-size:14px;color:#0b1d3a;">
                                <?php esc_html_e( 'If the button above does not work, copy and paste the following URL into your browser:', 'hcp-registration' ); ?><br>
                                <a href="<?php echo esc_url( $reset_url ); ?>" style="color:#0b1d3a;text-decoration:underline;word-break:break-all;"><?php echo esc_url( $reset_url ); ?></a>
                            </p>

                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Educational content:', 'hcp-registration' ); ?>
                            </h2>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'Once logged into the community, navigate to “Education” on the header of the website. This will take you to a landing page with various education sections. These sections are organised further into chapters, with each chapter designed to build on the previous.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 32px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'We encourage you to review these in order.', 'hcp-registration' ); ?>
                            </p>

                            <h2 style="margin:0 0 12px 0;color:#0b1d3a;font-family:Arial,sans-serif;font-size:20px;line-height:1.3;font-weight:700;">
                                <?php esc_html_e( 'Product information:', 'hcp-registration' ); ?>
                            </h2>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'By clicking “Products” on the website header you will be directed our product range where you have access to in-depth information and downloadable resources for each product.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 18px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'Approved trade account customers also have the ability to order products directly, saving up to 10% on standard wholesale costs and with same day dispatch.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'If you would like to be considered for a trade account, please click on the button below:', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0 0 32px 0;text-align:center;">
                                <a href="<?php echo esc_url( $trade_application_url ); ?>" style="background-color:#0b1d3a;color:#ffffff;display:inline-block;font-family:Arial,sans-serif;font-size:16px;font-weight:700;line-height:1.2;padding:14px 28px;text-decoration:none;border-radius:0;">
                                    <?php esc_html_e( 'Trade account application here', 'hcp-registration' ); ?>
                                </a>
                            </p>

                            <p style="margin:0 0 24px 0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'If you have any further questions, please get in touch by calling us on 0800 463 3226.', 'hcp-registration' ); ?>
                            </p>

                            <p style="margin:0;font-family:Arial,sans-serif;font-size:16px;color:#0b1d3a;">
                                <?php esc_html_e( 'Regards,', 'hcp-registration' ); ?><br>
                                <?php esc_html_e( 'The NUBU team.', 'hcp-registration' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
