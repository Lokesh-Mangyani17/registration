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
        <h2><?php esc_html_e( 'Register for Access', 'hcp-registration' ); ?></h2>
        <p class="hcp-description">
            <?php esc_html_e( 'The content in this section is intended to provide medicinal cannabis information and educational resources for healthcare professionals only. Please complete the form below to register for access and the NUBU team will review and grant access once your professional registration number has been confirmed.', 'hcp-registration' ); ?>
        </p>

        <div id="hcp-form-message" style="display:none;"></div>

        <div class="hcp-field">
            <label for="hcp_first_name"><?php esc_html_e( 'First Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="hcp_first_name" name="first_name" required />
        </div>

        <div class="hcp-field">
            <label for="hcp_last_name"><?php esc_html_e( 'Last Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="hcp_last_name" name="last_name" required />
        </div>

        <div class="hcp-field">
            <label for="hcp_phone"><?php esc_html_e( 'Phone', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="tel" id="hcp_phone" name="phone" required />
        </div>

        <div class="hcp-field">
            <label for="hcp_email"><?php esc_html_e( 'Email', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="email" id="hcp_email" name="email" required />
            <p class="hcp-field-hint"><?php esc_html_e( 'Please use a unique address. You will be unable to sign up under a shared email address.', 'hcp-registration' ); ?></p>
        </div>

        <div class="hcp-field">
            <label for="hcp_practice_name"><?php esc_html_e( 'Practice/Clinic Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="hcp_practice_name" name="practice_name" required />
        </div>

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

        <div class="hcp-field">
            <label for="hcp_password"><?php esc_html_e( 'Password', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="password" id="hcp_password" name="password" placeholder="<?php esc_attr_e( 'Password.', 'hcp-registration' ); ?>" required />
            <p class="hcp-field-hint"><?php esc_html_e( 'A minimum length of at least 8 characters, though 12 or more is strongly recommended. At least one uppercase letter (A-Z). At least one lowercase letter (a-z). At least one number (0-9). At least one special character (e.g., ! @ # $ % ^ & * ( ) - + ?).', 'hcp-registration' ); ?></p>
        </div>

        <div class="hcp-field hcp-terms-field">
            <label class="hcp-checkbox-label">
                <input type="checkbox" id="hcp_terms" name="terms" value="1" required />
                <?php esc_html_e( 'I accept the terms and conditions.', 'hcp-registration' ); ?>
            </label>
        </div>

        <div class="hcp-field hcp-submit-row">
            <button type="submit" class="hcp-submit-btn"><?php esc_html_e( 'Submit Registration', 'hcp-registration' ); ?></button>
            <span class="hcp-spinner" style="display:none;"></span>
        </div>
    </form>
</div>
