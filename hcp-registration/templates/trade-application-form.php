<?php
/**
 * Trade Application Form template.
 *
 * Rendered by the [trade_application_form] shortcode.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div id="hcp-registration-wrap">
    <form id="trade-application-form" enctype="multipart/form-data" novalidate>
        <h2><?php esc_html_e( 'Trade Application', 'hcp-registration' ); ?></h2>
        <p class="hcp-description">
            <?php esc_html_e( 'Please complete the form below to apply for a Trade Account. The NUBU team will review your application and you will be notified by email once it has been processed.', 'hcp-registration' ); ?>
        </p>

        <div id="trade-form-message" style="display:none;"></div>

        <!-- Personal Details -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Personal Details', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label for="trade_first_name"><?php esc_html_e( 'First Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="trade_first_name" name="first_name" value="<?php echo esc_attr( $prefill['first_name'] ); ?>" required />
        </div>

        <div class="hcp-field">
            <label for="trade_last_name"><?php esc_html_e( 'Last Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="trade_last_name" name="last_name" value="<?php echo esc_attr( $prefill['last_name'] ); ?>" required />
        </div>

        <div class="hcp-field">
            <label for="trade_phone"><?php esc_html_e( 'Phone', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="tel" id="trade_phone" name="phone" value="<?php echo esc_attr( $prefill['phone'] ); ?>" required />
        </div>

        <div class="hcp-field">
            <label for="trade_email"><?php esc_html_e( 'Email', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="email" id="trade_email" name="email" value="<?php echo esc_attr( $prefill['email'] ); ?>" required />
            <p class="hcp-field-hint"><?php esc_html_e( 'If you already have an HCP account, use the same email address to link your trade application.', 'hcp-registration' ); ?></p>
        </div>

        <!-- Healthcare Professional Details -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Healthcare Professional Details', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label for="trade_practice_name"><?php esc_html_e( 'Practice/Clinic Name', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_practice_name" name="practice_name" value="<?php echo esc_attr( $prefill['practice_name'] ); ?>" />
        </div>

        <div class="hcp-field">
            <label for="trade_hcp_type"><?php esc_html_e( 'Healthcare Professional Type', 'hcp-registration' ); ?></label>
            <select id="trade_hcp_type" name="hcp_type">
                <option value=""><?php esc_html_e( '— Select —', 'hcp-registration' ); ?></option>
                <option value="Doctor" <?php selected( $prefill['hcp_type'], 'Doctor' ); ?>><?php esc_html_e( 'Doctor', 'hcp-registration' ); ?></option>
                <option value="Nurse" <?php selected( $prefill['hcp_type'], 'Nurse' ); ?>><?php esc_html_e( 'Nurse', 'hcp-registration' ); ?></option>
                <option value="Pharmacist" <?php selected( $prefill['hcp_type'], 'Pharmacist' ); ?>><?php esc_html_e( 'Pharmacist', 'hcp-registration' ); ?></option>
                <option value="Dentist" <?php selected( $prefill['hcp_type'], 'Dentist' ); ?>><?php esc_html_e( 'Dentist', 'hcp-registration' ); ?></option>
                <option value="Physiotherapist" <?php selected( $prefill['hcp_type'], 'Physiotherapist' ); ?>><?php esc_html_e( 'Physiotherapist', 'hcp-registration' ); ?></option>
                <option value="Psychologist" <?php selected( $prefill['hcp_type'], 'Psychologist' ); ?>><?php esc_html_e( 'Psychologist', 'hcp-registration' ); ?></option>
                <option value="Other" <?php selected( $prefill['hcp_type'], 'Other' ); ?>><?php esc_html_e( 'Other', 'hcp-registration' ); ?></option>
            </select>
        </div>

        <div class="hcp-field">
            <label for="trade_hcp_reg_number"><?php esc_html_e( 'HCP Registration Number', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_hcp_reg_number" name="hcp_reg_number" value="<?php echo esc_attr( $prefill['hcp_reg_number'] ); ?>" />
        </div>

        <!-- Business Details -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Business Details', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label for="trade_company_number"><?php esc_html_e( 'Company Number', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_company_number" name="company_number" />
        </div>

        <div class="hcp-field">
            <label for="trade_nz_business_number"><?php esc_html_e( 'NZ Business Number', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_nz_business_number" name="nz_business_number" />
        </div>

        <div class="hcp-field">
            <label for="trade_legal_entity_number"><?php esc_html_e( 'Legal Entity Number', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_legal_entity_number" name="legal_entity_number" />
        </div>

        <div class="hcp-field">
            <label><?php esc_html_e( 'Does the applicant act as trustee?', 'hcp-registration' ); ?></label>
            <div class="trade-radio-group">
                <label class="trade-radio-label"><input type="radio" name="acts_as_trustee" value="yes" /> <?php esc_html_e( 'Yes', 'hcp-registration' ); ?></label>
                <label class="trade-radio-label"><input type="radio" name="acts_as_trustee" value="no" checked /> <?php esc_html_e( 'No', 'hcp-registration' ); ?></label>
            </div>
        </div>

        <div class="hcp-field">
            <label for="trade_trading_name"><?php esc_html_e( 'Trading Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="trade_trading_name" name="trading_name" required />
        </div>

        <div class="hcp-field">
            <label for="trade_physical_address"><?php esc_html_e( 'Physical Address', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <textarea id="trade_physical_address" name="physical_address" rows="3" required></textarea>
        </div>

        <div class="hcp-field">
            <label for="trade_postal_address"><?php esc_html_e( 'Postal Address', 'hcp-registration' ); ?></label>
            <textarea id="trade_postal_address" name="postal_address" rows="3"></textarea>
        </div>

        <!-- Contact & Operations -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Contact & Operations', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label for="trade_business_email"><?php esc_html_e( 'Common Business Email for Access', 'hcp-registration' ); ?></label>
            <input type="email" id="trade_business_email" name="business_email" />
        </div>

        <div class="hcp-field">
            <label for="trade_accounts_payable"><?php esc_html_e( 'Accounts Payable Contact', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_accounts_payable" name="accounts_payable_contact" />
        </div>

        <div class="hcp-field">
            <label for="trade_delivery_contact"><?php esc_html_e( 'Delivery Contact', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_delivery_contact" name="delivery_contact" />
        </div>

        <div class="hcp-field">
            <label for="trade_nature_of_business"><?php esc_html_e( 'Nature of Business', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_nature_of_business" name="nature_of_business" />
        </div>

        <div class="hcp-field">
            <label for="trade_date_incorporation"><?php esc_html_e( 'Date of Incorporation', 'hcp-registration' ); ?></label>
            <input type="date" id="trade_date_incorporation" name="date_of_incorporation" />
        </div>

        <div class="hcp-field">
            <label for="trade_ird_number"><?php esc_html_e( 'IRD Number', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_ird_number" name="ird_number" />
        </div>

        <!-- Financial -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Financial', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label><?php esc_html_e( 'Do you require a credit limit over $5,000?', 'hcp-registration' ); ?></label>
            <div class="trade-radio-group">
                <label class="trade-radio-label"><input type="radio" name="credit_limit_over_5000" value="yes" /> <?php esc_html_e( 'Yes', 'hcp-registration' ); ?></label>
                <label class="trade-radio-label"><input type="radio" name="credit_limit_over_5000" value="no" checked /> <?php esc_html_e( 'No', 'hcp-registration' ); ?></label>
            </div>
        </div>

        <!-- Supporting Documents -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Supporting Documents', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label for="trade_media_upload"><?php esc_html_e( 'Media Upload', 'hcp-registration' ); ?></label>
            <input type="file" id="trade_media_upload" name="media_upload" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx" />
            <p class="hcp-field-hint"><?php esc_html_e( 'Accepted formats: jpg, png, gif, pdf, doc, docx. Max 5 MB.', 'hcp-registration' ); ?></p>
        </div>

        <div class="hcp-field">
            <label for="trade_trade_reference"><?php esc_html_e( 'Trade Reference', 'hcp-registration' ); ?></label>
            <textarea id="trade_trade_reference" name="trade_reference" rows="3"></textarea>
        </div>

        <!-- Signature -->
        <h3 class="trade-section-heading"><?php esc_html_e( 'Signature', 'hcp-registration' ); ?></h3>

        <div class="hcp-field">
            <label><?php esc_html_e( 'Please sign below', 'hcp-registration' ); ?></label>
            <div class="trade-signature-wrap">
                <canvas id="trade-signature-pad" width="600" height="200" role="img" aria-label="<?php esc_attr_e( 'Signature drawing area', 'hcp-registration' ); ?>"></canvas>
                <input type="hidden" name="signature" id="trade_signature_data" />
                <button type="button" id="trade-clear-signature" class="trade-clear-sig-btn"><?php esc_html_e( 'Clear Signature', 'hcp-registration' ); ?></button>
            </div>
        </div>

        <!-- Terms -->
        <div class="hcp-field hcp-terms-field">
            <label class="hcp-checkbox-label">
                <input type="checkbox" id="trade_terms" name="terms" value="1" required />
                <?php esc_html_e( 'I accept the terms and conditions.', 'hcp-registration' ); ?>
            </label>
        </div>

        <div class="hcp-field hcp-submit-row">
            <button type="submit" class="hcp-submit-btn"><?php esc_html_e( 'Submit Trade Application', 'hcp-registration' ); ?></button>
            <span class="trade-spinner" style="display:none;"></span>
        </div>
    </form>
</div>
