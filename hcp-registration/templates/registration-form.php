<?php
/**
 * HCP Registration Form template.
 *
 * Rendered by the [hcp_registration_form] shortcode.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="hcp-registration-wrap">
    <form id="hcp-registration-form" novalidate>
        <h2><?php esc_html_e( 'Healthcare Professional Registration', 'hcp-registration' ); ?></h2>
        <p class="hcp-description">
            <?php esc_html_e( 'Please fill in the details below. Your registration will be reviewed by an administrator before your account is created.', 'hcp-registration' ); ?>
        </p>

        <div id="hcp-form-message" style="display:none;"></div>

        <div class="hcp-field-row">
            <div class="hcp-field">
                <label for="hcp_first_name"><?php esc_html_e( 'First Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <input type="text" id="hcp_first_name" name="first_name" required />
            </div>
            <div class="hcp-field">
                <label for="hcp_last_name"><?php esc_html_e( 'Last Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <input type="text" id="hcp_last_name" name="last_name" required />
            </div>
        </div>

        <div class="hcp-field-row">
            <div class="hcp-field">
                <label for="hcp_phone"><?php esc_html_e( 'Phone', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <input type="tel" id="hcp_phone" name="phone" required />
            </div>
            <div class="hcp-field">
                <label for="hcp_email"><?php esc_html_e( 'Email', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <input type="email" id="hcp_email" name="email" required />
            </div>
        </div>

        <div class="hcp-field">
            <label for="hcp_practice_name"><?php esc_html_e( 'Practice / Clinic Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="hcp_practice_name" name="practice_name" required />
        </div>

        <div class="hcp-field-row">
            <div class="hcp-field">
                <label for="hcp_type"><?php esc_html_e( 'Healthcare Professional Type', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <select id="hcp_type" name="hcp_type" required>
                    <option value=""><?php esc_html_e( '— Select —', 'hcp-registration' ); ?></option>
                    <option value="Doctor"><?php esc_html_e( 'Doctor', 'hcp-registration' ); ?></option>
                    <option value="Nurse"><?php esc_html_e( 'Nurse', 'hcp-registration' ); ?></option>
                    <option value="Pharmacist"><?php esc_html_e( 'Pharmacist', 'hcp-registration' ); ?></option>
                    <option value="Dentist"><?php esc_html_e( 'Dentist', 'hcp-registration' ); ?></option>
                    <option value="Physiotherapist"><?php esc_html_e( 'Physiotherapist', 'hcp-registration' ); ?></option>
                    <option value="Psychologist"><?php esc_html_e( 'Psychologist', 'hcp-registration' ); ?></option>
                    <option value="Other"><?php esc_html_e( 'Other', 'hcp-registration' ); ?></option>
                </select>
            </div>
            <div class="hcp-field">
                <label for="hcp_reg_number"><?php esc_html_e( 'HCP Registration Number', 'hcp-registration' ); ?> <span class="required">*</span></label>
                <input type="text" id="hcp_reg_number" name="hcp_reg_number" required />
            </div>
        </div>

        <div class="hcp-field hcp-submit-row">
            <button type="submit" class="hcp-submit-btn"><?php esc_html_e( 'Submit Registration', 'hcp-registration' ); ?></button>
            <span class="hcp-spinner" style="display:none;"></span>
        </div>
    </form>
</div>
