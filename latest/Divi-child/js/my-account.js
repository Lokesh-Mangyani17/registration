(function( $ ) {
	
	function reloadWithQueryVars(params) {
		const url = new URL(window.location.href);

		// Iterate over the object and add or update each query parameter
		for (const [key, value] of Object.entries(params)) {
			url.searchParams.set(key, value);
		}

		// Redirect to the updated URL
		window.location.href = url.toString();
	}
	
	$('.nubu_woocommerce_new_address').on('submit', function(e) {
		let isValid = true;
		let requiredFields = ['billing_address_name', 'billing_first_name', 'billing_last_name', 'billing_address_1', 'billing_city', 'billing_state', 'billing_postcode', 'billing_email'];

		requiredFields.forEach(function(field) {
			let value = $('[name="' + field + '"]').val().trim();
			if ( value === '' ) {
				isValid = false;
				$('[name="' + field + '"]').css('border-color', 'red');
			} else {
				$('[name="' + field + '"]').css('border-color', '');
			}
		});

		if ( !isValid ) {
			e.preventDefault();
			alert('Please fill out all required fields before saving the address.');
		}
	});
	
	
	$('form.checkout').on('checkout_place_order', function() {
		let isChecked = $('#billing_save_new_address').is(':checked');
		let nameField = $('#billing_address_name');
		let name = nameField.val().trim();

		// Remove previous error if any
		nameField.closest('.form-row').removeClass('woocommerce-invalid woocommerce-invalid-required-field');

		if ( isChecked && name === '' ) {
			nameField.closest('.form-row').addClass('woocommerce-invalid woocommerce-invalid-required-field');
			nameField.focus();
			return false; // Prevent form submission
		}

		return true;
	});
	
	$('body').on('click', '.nubu_dropdown li:not(.nubu_report_date_range)', function() {
		let $this 			= $(this),
			selectedText 	= $this.text(),
			value 			= $this.data('value');
		$this.closest('.nubu_dropdown').find('.nubu_dropdown_toggle').text(selectedText);
		$this.closest('.nubu_dropdown_menu').find('li').not($this).removeClass('selected');
		$this.addClass('selected');
		$this.closest('.nubu_woocommerce_section_29_month_wrapper').find('.nubu_woocommerce_section29_month').val(value);
		$this.closest('.nubu_dropdown_menu').slideUp(200, function(){
			$this.closest('.nubu_dropdown').removeClass('active');
		});
	});
	
	$('body').on('click', '.nubu_woocommerce_reports_date_filter li:not(.nubu_report_date_range)', function() {
		let value 			= $(this).data('value'),
			selectedText 	= $(this).data('label');
		reloadWithQueryVars({
			filter: selectedText,
			date: value
		});
	});

	$('body').on('click', '.nubu_woocommerce_reports_date_filter .nubu_woocommerce_date_range_action', function() {
		let $this 			= $(this),
			$from 			= $this.closest('.nubu_report_date_range').find('.nubu_woocommerce_report_from_date').val(),
			$to 			= $this.closest('.nubu_report_date_range').find('.nubu_woocommerce_report_to_date').val(),
			$filterLabel	= $from + ' - ' + $to,
			$value 			= $from + '...' + $to;
		if ( '' == $from || '' === $to ) {
			alert('Please enter From and To date correctly');
			return false;
		}
		$this.closest('.nubu_dropdown').find('.nubu_dropdown_toggle').text($filterLabel);
		$this.closest('.nubu_dropdown_menu').slideUp(200, function(){
			$this.closest('.nubu_dropdown').removeClass('active');
		});
		reloadWithQueryVars({
			filter: $filterLabel,
			date: $value
		});
	});
	
	$(document).click(function(event) {
		if ( ! $(event.target).closest('.nubu_dropdown').length ) {
			$('.nubu_dropdown_menu').slideUp(200, function(){
				$(this).closest('.nubu_dropdown').removeClass('active');
			});
		}
	});
	
	$(document).ready(function() {
		function setBillingAddressNameField(value, hideAndReadonly = false) {
				let $field = $('#billing_address_name');
				$field.val(value).trigger('change');

				if (hideAndReadonly) {
					$field.prop('readonly', true);
					$field.closest('.form-row').hide(); // hide the whole row
				} else {
					$field.prop('readonly', false);
					$field.closest('.form-row').show(); // show the row for new address
				}
			}

			function fillFields(address, name = '') {
				if (!address) return;

				$('#billing_first_name').val(address.first_name || '').attr('value',address.first_name || '').trigger('change');
				$('#billing_last_name').val(address.last_name || '').attr('value',address.last_name || '').trigger('change');
				$('#billing_address_1').val(address.address_1 || '').attr('value',address.address_1 || '').trigger('change');
				$('#billing_address_2').val(address.address_2 || '').attr('value',address.address_2 || '').trigger('change');
				$('#billing_city').val(address.city || '').attr('value',address.city || '').trigger('change');
				$('#billing_state').val(address.state || '').attr('value',address.state || '').trigger('change');
				$('#billing_postcode').val(address.postcode || '').attr('value',address.postcode || '').trigger('change');

				setBillingAddressNameField(name, true); // read-only + hidden
				if ( name ) {
					$('#billing_address_action').val('edit_address').attr('value', 'edit_address');
				} else {
					$('#billing_address_action').val('new_address').attr('value','new_address');
				}

				$('body').trigger('update_checkout');

			}

			function toggleBillingFields(show) {
				$('.woocommerce-billing-fields__field-wrapper').toggle(show);
			}

			// Edit existing address
			$('.nubu_edit_address_link').on('click', function (e) {
				e.preventDefault();
				let key = $(this).data('address-name');
				let address = window.checkoutAddresses[key];

				//$('input[name="nubu_billing_select_existing"][value="' + key + '"]').prop('checked', true);
				//$('input[name="nubu_billing_select_existing"]').prop('checked',false);
				//$(this).closest('.nubu_address_option').find('input').prop('checked',true);

				$('input[name="nubu_billing_select_existing"]').prop('checked', false);
				$('input[name="nubu_billing_select_existing"][value="' + key + '"]').prop('checked', true).trigger('change');

				fillFields(address, key);
				toggleBillingFields(true);
				$('#billing_address_action').val('edit_address').attr('value', 'edit_address').trigger('change');

			});

			// Add new address
			$('#nubu_add_new_address_link').on('click', function (e) {
				e.preventDefault();
				$('input[name="nubu_billing_select_existing"]').prop('checked', false);
				fillFields({}, '');
				toggleBillingFields(true);

				setBillingAddressNameField('', false); // show + editable
				$('#billing_address_action').val('new_address').attr('value','new_address').trigger('change');
			});

			// Select existing address (non-edit)
			$('input[name="nubu_billing_select_existing"]').on('change', function () {
				let selected = $(this).val();
				let address = window.checkoutAddresses[selected];
				fillFields(address, selected);
				toggleBillingFields(false);
			});

			// On initial load
			const checked = $('input[name="nubu_billing_select_existing"]:checked').val();
			if (checked && window.checkoutAddresses[checked]) {
				fillFields(window.checkoutAddresses[checked], checked);
				toggleBillingFields(false);
			} else {
				toggleBillingFields(true);
				setBillingAddressNameField('', false);
			}
	});
	
}( jQuery ));