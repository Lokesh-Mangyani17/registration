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

        <div class="hcp-field" id="trust-name-field" style="display:none;">
            <label for="trade_trust_name"><?php esc_html_e( 'Trust Name', 'hcp-registration' ); ?></label>
            <input type="text" id="trade_trust_name" name="trust_name" />
        </div>

        <div class="hcp-field">
            <label for="trade_trading_name"><?php esc_html_e( 'Trading Name', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <input type="text" id="trade_trading_name" name="trading_name" required />
        </div>

        <div class="hcp-field">
            <label><?php esc_html_e( 'Physical Address', 'hcp-registration' ); ?> <span class="required">*</span></label>
            <div class="hcp-address-group">
                <div class="hcp-field">
                    <label for="physical_street_address"><?php esc_html_e( 'Street Address', 'hcp-registration' ); ?></label>
                    <input type="text" id="physical_street_address" name="physical_street_address" required />
                </div>
                <div class="hcp-field-row">
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_suburb"><?php esc_html_e( 'Suburb', 'hcp-registration' ); ?></label>
                        <input type="text" id="physical_suburb" name="physical_suburb" />
                    </div>
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_city"><?php esc_html_e( 'City', 'hcp-registration' ); ?></label>
                        <input type="text" id="physical_city" name="physical_city" required />
                    </div>
                </div>
                <div class="hcp-field-row">
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_state"><?php esc_html_e( 'State / Region', 'hcp-registration' ); ?></label>
                        <input type="text" id="physical_state" name="physical_state" />
                    </div>
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_postal_code"><?php esc_html_e( 'Postal Code', 'hcp-registration' ); ?></label>
                        <input type="text" id="physical_postal_code" name="physical_postal_code" />
                    </div>
                </div>
                <div class="hcp-field">
                    <label for="physical_country"><?php esc_html_e( 'Country', 'hcp-registration' ); ?></label>
                    <select id="physical_country" name="physical_country">
                        <option value=""><?php esc_html_e( '— Select Country —', 'hcp-registration' ); ?></option>
                        <option value="New Zealand"><?php esc_html_e( 'New Zealand', 'hcp-registration' ); ?></option>
                        <option value="Australia"><?php esc_html_e( 'Australia', 'hcp-registration' ); ?></option>
                        <option value="United Kingdom"><?php esc_html_e( 'United Kingdom', 'hcp-registration' ); ?></option>
                        <option value="United States"><?php esc_html_e( 'United States', 'hcp-registration' ); ?></option>
                        <option value="Canada"><?php esc_html_e( 'Canada', 'hcp-registration' ); ?></option>
                        <option value="India"><?php esc_html_e( 'India', 'hcp-registration' ); ?></option>
                        <option value="China"><?php esc_html_e( 'China', 'hcp-registration' ); ?></option>
                        <option value="Japan"><?php esc_html_e( 'Japan', 'hcp-registration' ); ?></option>
                        <option value="South Korea"><?php esc_html_e( 'South Korea', 'hcp-registration' ); ?></option>
                        <option value="Singapore"><?php esc_html_e( 'Singapore', 'hcp-registration' ); ?></option>
                        <option value="Germany"><?php esc_html_e( 'Germany', 'hcp-registration' ); ?></option>
                        <option value="France"><?php esc_html_e( 'France', 'hcp-registration' ); ?></option>
                        <option value="Ireland"><?php esc_html_e( 'Ireland', 'hcp-registration' ); ?></option>
                        <option value="South Africa"><?php esc_html_e( 'South Africa', 'hcp-registration' ); ?></option>
                        <option value="Fiji"><?php esc_html_e( 'Fiji', 'hcp-registration' ); ?></option>
                        <option value="Samoa"><?php esc_html_e( 'Samoa', 'hcp-registration' ); ?></option>
                        <option value="Tonga"><?php esc_html_e( 'Tonga', 'hcp-registration' ); ?></option>
                        <option value="Other"><?php esc_html_e( 'Other', 'hcp-registration' ); ?></option>
                    </select>
                </div>
                <div class="hcp-field-row">
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_phone"><?php esc_html_e( 'Phone', 'hcp-registration' ); ?></label>
                        <input type="tel" id="physical_phone" name="physical_phone" />
                    </div>
                    <div class="hcp-field hcp-field-half">
                        <label for="physical_fax"><?php esc_html_e( 'Fax', 'hcp-registration' ); ?></label>
                        <input type="text" id="physical_fax" name="physical_fax" />
                    </div>
                </div>
            </div>
        </div>

        <div class="hcp-field">
            <label><?php esc_html_e( 'Postal Address', 'hcp-registration' ); ?></label>
            <div class="hcp-field" style="margin-bottom:12px;">
                <label><?php esc_html_e( 'Same as physical address?', 'hcp-registration' ); ?></label>
                <div class="trade-radio-group">
                    <label class="trade-radio-label"><input type="radio" name="postal_same_as_physical" value="yes" checked /> <?php esc_html_e( 'Yes', 'hcp-registration' ); ?></label>
                    <label class="trade-radio-label"><input type="radio" name="postal_same_as_physical" value="no" /> <?php esc_html_e( 'No', 'hcp-registration' ); ?></label>
                </div>
            </div>
            <div id="postal-address-fields" style="display:none;">
                <div class="hcp-address-group">
                    <div class="hcp-field">
                        <label for="postal_address_line"><?php esc_html_e( 'Postal Address', 'hcp-registration' ); ?></label>
                        <input type="text" id="postal_address_line" name="postal_address_line" />
                    </div>
                    <div class="hcp-field-row">
                        <div class="hcp-field hcp-field-half">
                            <label for="postal_suburb"><?php esc_html_e( 'Suburb', 'hcp-registration' ); ?></label>
                            <input type="text" id="postal_suburb" name="postal_suburb" />
                        </div>
                        <div class="hcp-field hcp-field-half">
                            <label for="postal_country"><?php esc_html_e( 'Country', 'hcp-registration' ); ?></label>
                            <select id="postal_country" name="postal_country">
                                <option value=""><?php esc_html_e( '— Select Country —', 'hcp-registration' ); ?></option>
                                <option value="New Zealand"><?php esc_html_e( 'New Zealand', 'hcp-registration' ); ?></option>
                                <option value="Australia"><?php esc_html_e( 'Australia', 'hcp-registration' ); ?></option>
                                <option value="United Kingdom"><?php esc_html_e( 'United Kingdom', 'hcp-registration' ); ?></option>
                                <option value="United States"><?php esc_html_e( 'United States', 'hcp-registration' ); ?></option>
                                <option value="Canada"><?php esc_html_e( 'Canada', 'hcp-registration' ); ?></option>
                                <option value="India"><?php esc_html_e( 'India', 'hcp-registration' ); ?></option>
                                <option value="China"><?php esc_html_e( 'China', 'hcp-registration' ); ?></option>
                                <option value="Japan"><?php esc_html_e( 'Japan', 'hcp-registration' ); ?></option>
                                <option value="South Korea"><?php esc_html_e( 'South Korea', 'hcp-registration' ); ?></option>
                                <option value="Singapore"><?php esc_html_e( 'Singapore', 'hcp-registration' ); ?></option>
                                <option value="Germany"><?php esc_html_e( 'Germany', 'hcp-registration' ); ?></option>
                                <option value="France"><?php esc_html_e( 'France', 'hcp-registration' ); ?></option>
                                <option value="Ireland"><?php esc_html_e( 'Ireland', 'hcp-registration' ); ?></option>
                                <option value="South Africa"><?php esc_html_e( 'South Africa', 'hcp-registration' ); ?></option>
                                <option value="Fiji"><?php esc_html_e( 'Fiji', 'hcp-registration' ); ?></option>
                                <option value="Samoa"><?php esc_html_e( 'Samoa', 'hcp-registration' ); ?></option>
                                <option value="Tonga"><?php esc_html_e( 'Tonga', 'hcp-registration' ); ?></option>
                                <option value="Other"><?php esc_html_e( 'Other', 'hcp-registration' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
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

        <div class="hcp-field trade-conditions-block">
            <h4><?php esc_html_e( 'Customer application conditions', 'hcp-registration' ); ?></h4>

            <p><?php esc_html_e( 'The customer acknowledges that is has received, understood and is bound by the trading terms attached to this application ("MW Pharma Terms of Trade") and requests that MW Pharma Ltd t/a NUBU ("NUBU") supply goods requested by the customer upon and subject to the Trading Terms.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'The customer acknowledges that payment for goods must be made in accordance with the payment terms specified in the Trading Terms or as otherwise agreed by NUBU in writing.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'The customer authorises NUBU to make all necessary enquires to each of the referees and the bank for the purpose of obtaining such financial or other information that it may reasonably require for the purposes of assessing this application and the customer undertakes that it will authorise the referees and the banks to provide such information and documentation as NUBU may require.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'The customer undertakes to provide to NUBU such further details, documents or information concerning the customer as NUBU may reasonably require for the purposes of assessing this application.', 'hcp-registration' ); ?></p>

            <h4><?php esc_html_e( 'Section 29 returns (applicable to pharmaceutical customers only)', 'hcp-registration' ); ?></h4>

            <p><?php esc_html_e( 'In order to fulfil our obligations to the Ministry of Health, we require you to provide us with prescription data for any Section 29 Medicines that you purchase from NUBU.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'If you are placing orders with us as prescriptions come in, you will have an option to enter the Section 29 data into the HCP Community portal at the time of ordering.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( "If you order from NUBU in volumes that allow you to keep stock on hand and don't have prescription data on hand at the time of ordering, you will be unable to use this function.", 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'Instead, you will need to email your data through to hq@nubupharma.com using the excel template provided by NUBU, within 5 business days of the end of the month.', 'hcp-registration' ); ?></p>

            <p><?php esc_html_e( 'Please provide the following information:', 'hcp-registration' ); ?></p>
            <ul>
                <li><?php esc_html_e( 'Date of dispense', 'hcp-registration' ); ?></li>
                <li><?php esc_html_e( 'Patient name', 'hcp-registration' ); ?></li>
                <li><?php esc_html_e( 'Section 29 product dispensed', 'hcp-registration' ); ?></li>
                <li><?php esc_html_e( 'Number of units dispensed', 'hcp-registration' ); ?></li>
                <li><?php esc_html_e( 'Prescribing Doctor', 'hcp-registration' ); ?></li>
                <li><?php esc_html_e( 'Pharmacode', 'hcp-registration' ); ?></li>
            </ul>

            <p><?php esc_html_e( 'I certify that I am authorised to sign this application form on behalf of the customer and that the information given is true and correct to the best of my knowledge.', 'hcp-registration' ); ?></p>
        </div>

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
                <?php esc_html_e( 'I/We acknowledge that we have read and accept the terms of trade.', 'hcp-registration' ); ?>
            </label>
        </div>

        <div class="hcp-field hcp-submit-row">
            <button type="submit" class="hcp-submit-btn"><?php esc_html_e( 'Submit Trade Application', 'hcp-registration' ); ?></button>
            <span class="trade-spinner" style="display:none;"></span>
        </div>
    </form>
</div>
