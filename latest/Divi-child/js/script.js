(function( $ ) {
	$('body').on('input', '.nubu_csc_client_number', function () {
		let cleaned = $(this).val().replace(/[^0-9]/g, '').slice(0, 11);
		$(this).val(cleaned);
	});
	
	$(document.body).on('should_send_ajax_request.adding_to_cart', function(event, $button ){		
		if ( 'yes' === $button.data('section_29_required') && 'no' === $button.data('approved') ) {
			return false;
		} else {
			return true;
		}
		
	});
	
	$( document.body ).on( 'ajax_request_not_sent.adding_to_cart', function(event, flag1, flag2, $button) {
		let productId = 0,
			productName = '',
			qty = 1,
			section29Required = 'no';
		
		if ( 'yes' === $button.data('section_29_required') && 'no' === $button.data('approved') ) {
			if ( $button.hasClass('single_add_to_cart_button') ) {
				productId = parseInt( $button.attr('value') );
				productName = $button.data('product_name');
				qty = parseInt( $button.closest('form').find('.qty').val() );
			} else if ( $button.hasClass('add_to_cart_button') ) {
				productId = parseInt( $button.data('product_id') );
				productName = $button.data('product_name');
				qty = parseInt( $button.data('quantity') );
			}
			$('body').find('#nubu_prescription_modal').fadeIn('show',function(){
				$(this).find('.nubu_prescription_form').data('product_id', productId);
				$(this).find('.nubu_product_prescribed').val(productName);
				$(this).find('.nubu_units_prescribed').val(qty);
				//$(this).find('.nubu_units_prescribed').attr('max',qty);
				return false;
			});
		}
	});
	
	$( document.body ).on( 'added_to_cart', function( event, fragments, cart_hash, $button ){
		if ( $button.closest('form.cart').length > 0 ) {
			$button.closest('form.cart').data('approved','no');
		}
		$button.data('approved','no');
	});
	
	let pendingForm = '';
	$(document.body).on( 'submit', 'form.cart', function(e) {
		e.preventDefault();
		let form = $(this);

        // allow if already approved
        if ( 'yes' === form.data('approved') ) {
            return true;
        }

        e.preventDefault();
        e.stopImmediatePropagation();
		
		let product_id 	= form.data('product_id'),
			productName = form.data('product_name'),
			qty = parseInt( form.find('.qty').val() );

        pendingForm = form;

        $('body').find('#nubu_prescription_modal').fadeIn('show',function(){
			$(this).find('.nubu_prescription_form').data('product_id', product_id);
			$(this).find('.nubu_product_prescribed').val(productName);
			$(this).find('.nubu_units_prescribed').val(qty);
			//$(this).find('.nubu_units_prescribed').attr('max',qty);
		});
	} );

	$('body').on('click', '.nubu_modal_popup_close_icon', function() {
		$('body').find('.nubu_modal').fadeOut('fast');
	});
	
	/* Close modal on outside click */
	$('body').on('click', '.nubu_modal', function(e){
		if ( $(e.target).is( $(this) ) ) {
			$('body').find('.nubu_modal').fadeOut('fast');
		}
	});

	$('body').on('click', '.nubu_custom_checkout', function(e){
		e.preventDefault();
		let btn = $(this);
        btn.prop('disabled', true).text('Processing...');

        $('body').find('form.woocommerce-checkout').trigger('submit'); // Let WooCommerce validation run
		//$('body').find('form.woocommerce-checkout').submit();
	});

	$(document.body).on('checkout_error', function(){
        $('body').find('.nubu_custom_checkout').prop('disabled', false).text('Place Order');
    });

	$('body').on('submit', '.coupon-form-row .nubu_apply_coupon', function(e){
		e.preventDefault();
		var code = $(this).find('input[name="coupon_code"]').val();
		if (!code) {
			alert("Please enter a coupon code.");
			return false;
		}
		$('form.woocommerce-cart-form input[name="coupon_code"]').val(code);
		// Trigger click on original Apply Coupon button (uses WooCommerce AJAX)
		$('form.woocommerce-cart-form button[name="apply_coupon"]').trigger('click');
	});
	
	$('body').find('.nubu_announcement_slider').each(function() {
		let $this 		= $(this),
			data 		= $this.data(),
			$orderClass = $this.prop('class').match(/nubu_announcement_slider_\d+/)[0],
			autoplay_param = false;

		if ( data.autoplay ) {
			autoplay_param =  {
				delay: 2000,
				disableOnInteraction: ( 'on' === data.pause_on_hover ) ? true : false,
			};
		}

		let swiperSlider = new Swiper( '.' + $orderClass , {
			slidesPerView: parseInt( data.slides_per_view ),
			autoplay: autoplay_param,
			spaceBetween: parseInt( data.space_between_slides ),
			effect: 'slide',
			loop: data.loop,
			pagination: false,
			navigation: {    
				nextEl: '.' + $orderClass + ' .swiper-button-next',
				prevEl: '.' + $orderClass + ' .swiper-button-prev',
			},
			grabCursor: true,
			breakpoints: {
				1080: {
					slidesPerView: parseInt( data.slides_per_view ),
					spaceBetween: parseInt( data.space_between_slides ),
				},
				767: {
					slidesPerView: parseInt( data.slides_per_view_tablet ),
					spaceBetween: parseInt( data.space_between_slides_tablet ),
				},
				0: {
					slidesPerView: parseInt( data.slides_per_view_phone ),
					spaceBetween: parseInt( data.space_between_slides_phone ),
				}
			}
		} );

		if ( data.autoplay && data.pause_on_hover ) {
			$( '.' + $orderClass + ' .swiper-wrapper' ).on( 'mouseleave', function(e) {
				if ( typeof swiperSlider.autoplay.start === "function" ) {
					swiperSlider.autoplay.start();
				}
			} );
			$( '.' + $orderClass + ' .swiper-wrapper' ).on( 'mouseenter', function(e) {
				if ( typeof swiperSlider.autoplay.stop === "function" ) {
					swiperSlider.autoplay.stop();
				}
			} );
		}
		if ( ! data.loop ) {
			swiperSlider.on( 'reachEnd', function() {
				swiperSlider.autoplay = false;
			} );
		}
	});
	
	$('body').on('click', '.nubu_announcement_icon', function (e) {
        e.preventDefault();
		$(this).next('.nubu_announcement_dropdown').slideToggle();
    });

    // Close dropdown when clicking outside
    $(document).on("click", function (e) {
		if ( ! $(e.target).closest('.nubu_announcement_menu_item').length && ! $(e.target).closest('.nubu_announcement_dropdown').length ) {
			$('.nubu_announcement_dropdown').slideUp(300);
		}
    });
	
	$('body').on('click', '.nubu_mark_all_as_read', function(){
		let $this = $(this);
        $.ajax({
            type: 'POST',  
            url: nubuData.ajaxurl,
            data: {
                action: 'nubu_mark_all_announcement_as_read', 
                nonce:  nubuData.ajaxnonce
            },
            success: function(response) {
				$this.closest('.nubu_announcement_dropdown_wrapper').removeClass('has_unread_announcement');
				$this.fadeOut('fast', function(){
					$this.remove();
				});
            },
            error: function(){
               
            }
        });
    });
	
	setTimeout(function(){
		if ( $('body').find('.nubu_out_of_stock_product_modal_popup').length > 0 ) {
			$('body').find('.nubu_out_of_stock_product_modal_popup').fadeIn('fast');
		}
	}, 1000);
	
	$('body').on('click', '.nubu_out_of_stock_product_modal_popup_close_icon', function() {
		$('body').find('.nubu_out_of_stock_product_modal_popup').fadeOut('fast');
	});
	
	/* Close modal on outside click */
	$('body').on('click', '.nubu_out_of_stock_product_modal_popup', function(e){
		if ( $(e.target).is( $(this) ) ) {
			$('body').find('.nubu_out_of_stock_product_modal_popup').fadeOut('fast');
		}
	});

	/* Close modal on esc key after its fully rendered */
	$(document).on('keyup', function(e){
		if ( e.keyCode === 27 ) {
			$('body').find('.nubu_out_of_stock_product_modal_popup').fadeOut('fast');
		}
	});
	
    $('.product-filter').on('change', function(){
        let $this = $(this),
            $data = {};
            
        $this.closest('.product-filters-wrap').find('.product-filter').each(function(){
            let taxonomy    = $(this).data('taxonomy'),
                term        = $(this).val();
            
            $data[name] = term;
        });
            
        $.ajax({
            type: 'POST',  
            url: nubuData.ajaxurl,
            data: {
                action: 'nubu_alter_products', 
                nonce:  nubuData.ajaxnonce,
                filters: $data
            },
            success: function(response) {
                console.log(response);
            },
            error: function(){
               
            }
        });
    });
	
	$(document).on('submit', 'form.woocommerce-cart-form', function(e) {
        let valid = true;

        $(this).find('input.qty').each(function() {
            const minQty = parseInt($(this).data('min'));
            const currentQty = parseInt($(this).val());

            if (minQty && currentQty < minQty) {
                $(this).val(minQty); // Fix the quantity before form submits
                valid = false;
            }
        });

        // Optional: alert or show error if needed
        if (!valid) {
            alert('One or more products had minimum quantity limits. Quantities were adjusted.');
        }

    });
	
	$(document).on('click', '.nubu_custom_prices_wrapper label', function(){
		let qty = parseInt( $(this).data('qty') );
		$(this).closest('.quantity').find('input.qty').val(qty).trigger('change');
	});
	
	$(document).on('click', '.nubu_quantity_input_wrapper .nubu_plus_quantity, .nubu_quantity_input_wrapper .nubu_minus_quantity', function() {
        var $qty    = $(this).closest('.nubu_quantity_input_wrapper').find('.qty');
        var current = parseFloat($qty.val());
        var max     = parseFloat($qty.attr('max'));
        var min     = parseFloat($qty.attr('min'));
        var step    = parseFloat($qty.attr('step')) || 1;

        if ( $(this).hasClass('nubu_plus_quantity') ) {
            if ( !max || current < max ) {
                $qty.val(current + step).trigger('change');
            }
        } else {
            if ( current > min ) {
                $qty.val(current - step).trigger('change');
            }
        }
		
		$qty.closest('.nubu_quantity_input_wrapper').find('input').trigger('change');
    });
	
	$(document).on('input', 'input.qty', function () {
		let minQty = $(this).data('min');
      	if (this.validity.rangeUnderflow) {
        	this.setCustomValidity('Minimum quantity for this product is ' + minQty);
      	} else {
        	this.setCustomValidity('');
      	}
    });
	
	function nubu_update_price() {
		let quantity 		= parseInt($(this).val(), 10);
		let defaultPrice 	= $(this).data('default_price');
		let priceTiers;

		try {
			priceTiers = JSON.parse($(this).attr('data-prices'));
		} catch (e) {
			console.error('Invalid JSON in data-prices');
			return;
		}

		// Sort priceTiers by minqty descending so we can pick the highest eligible tier
		priceTiers.sort((a, b) => b.MinQty - a.MinQty);

		let matchedPrice = null;

		for (const tier of priceTiers) {
			if (quantity >= tier.MinQty) {
				matchedPrice = tier.Price;
				break;
			}
		}

		if (matchedPrice !== null) {
			$('.single-product').find('.et_pb_wc_price .price .woocommerce-Price-amount').html(`<bdi><span class="woocommerce-Price-currencySymbol">$</span>${matchedPrice.toFixed(2)}</bdi>`);
		} else {
			$('.single-product').find('.et_pb_wc_price .price .woocommerce-Price-amount').html(`<bdi><span class="woocommerce-Price-currencySymbol">$</span>${defaultPrice}</bdi>`);
		}
	}
	
	$(window).load(function(){
		$(document).on('change', 'input.qty', function() {
			var minQty = $(this).data('min'); // Get the minimum quantity set in the data attribute
			var currentQty = parseInt($(this).val()); // Get the current quantity input value

			// If the quantity is less than the minimum, set it back to the minimum
			if (currentQty < minQty) {
				$(this).val(minQty); // Reset to minimum
				$(this)[0].reportValidity();
			}
		});
		
		$('.single-product').find('input.qty').on('input change', nubu_update_price);
		
		$('body').find('.nubu_announcement_slider').each(function() {
			let $this 		= $(this),
				data 		= $this.data(),
				$orderClass = $this.prop('class').match(/nubu_announcement_slider_\d+/)[0],
				autoplay_param = false;

			if ( data.autoplay ) {
				autoplay_param =  {
					delay: 2000,
					disableOnInteraction: ( 'on' === data.pause_on_hover ) ? true : false,
				};
			}

			let swiperSlider = new Swiper( '.' + $orderClass , {
				slidesPerView: parseInt( data.slides_per_view ),
				autoplay: autoplay_param,
				spaceBetween: parseInt( data.space_between_slides ),
				effect: 'slide',
				loop: data.loop,
				pagination: false,
				navigation: {    
					nextEl: '.' + $orderClass + ' .swiper-button-next',
					prevEl: '.' + $orderClass + ' .swiper-button-prev',
				},
				grabCursor: true,
				breakpoints: {
					1080: {
						slidesPerView: parseInt( data.slides_per_view ),
						spaceBetween: parseInt( data.space_between_slides ),
					},
					767: {
						slidesPerView: parseInt( data.slides_per_view_tablet ),
						spaceBetween: parseInt( data.space_between_slides_tablet ),
					},
					0: {
						slidesPerView: parseInt( data.slides_per_view_phone ),
						spaceBetween: parseInt( data.space_between_slides_phone ),
					}
				}
			} );

			if ( data.autoplay && data.pause_on_hover ) {
				$( '.' + $orderClass + ' .swiper-wrapper' ).on( 'mouseleave', function(e) {
					if ( typeof swiperSlider.autoplay.start === "function" ) {
						swiperSlider.autoplay.start();
					}
				} );
				$( '.' + $orderClass + ' .swiper-wrapper' ).on( 'mouseenter', function(e) {
					if ( typeof swiperSlider.autoplay.stop === "function" ) {
						swiperSlider.autoplay.stop();
					}
				} );
			}
			if ( ! data.loop ) {
				swiperSlider.on( 'reachEnd', function() {
					swiperSlider.autoplay = false;
				} );
			}
		});
	});

	$(document).on('nfFormSubmitResponse', function () {
        $.ajax({
            url: nubuData.ajaxurl,
            type: 'POST',
            data: { action: 'nubu_set_ninja_form_submitted', 'nonce' : nubuData.ajaxnonce }
        });
    });
	
	$(document).ready(function() {
		
		$(document).on('click', '.nubu_yes_s29_data', function() {
			$(this).closest('.nubu_prescription_form_confirmation_wrapper').fadeOut('fast', function(){
				$('#nubu_prescription_form').fadeIn('fast');
			});
		});
		
		
		$(document).on('click', '.nubu_no_s29_data', function() {
			$(this).closest('.nubu_prescription_form_confirmation_wrapper').find('button').prop('disabled',true);
			$(this).closest('.nubu_prescription_form_confirmation_action_wrapper').append('<span class="loader"></span>');
			let orderId = parseInt( $(this).data('order_id') );
			$.ajax({
				type: 'POST',  
				url: nubuData.ajaxurl,
				data: {
					action: 'nubu_send_order_data', 
					nonce:  nubuData.ajaxnonce,
					order_id: orderId
				},
				success: function(response) {
					$('.et_pb_wc_checkout_payment_info .nubu_prescription_form_confirmation_wrapper').fadeOut('fast',function(){
						$('.et_pb_wc_checkout_payment_info .woocommerce-thankyou-order-received, .et_pb_wc_checkout_payment_info .woocommerce-order-overview, .et_pb_wc_checkout_payment_info .woocommerce-order-details, .et_pb_wc_checkout_payment_info .woocommerce-customer-details, .et_pb_wc_checkout_payment_info .et_pb_ab_shop_conversion ').fadeIn('fast', function(){
							$( 'html, body' ).animate({
								scrollTop: ( $('.et_pb_wc_checkout_payment_info .woocommerce-order').offset().top - 50 )
							});

						});
					});
				},
				error: function(){
					console.log('error');
				}
			});
		});
		
		function checkFormFields( $element ) {
			let allFilled = true,
				$patient = $element.closest('.nubu_patient_details');

			// Check all required fields in the form
			$patient.find('.nubu_prescription_form_field[required]').each(function() {
				if ($(this).val() === '') {
					allFilled = false;
				}
			});

			// Toggle the visibility of the "Add Patient" button based on form completion
			if (allFilled) {
				$('.nubu_add_patient').show(); // Show button if all fields are filled
			} else {
				$('.nubu_add_patient').hide(); // Hide button if not all fields are filled
			}
		}

		// Set current date to the date input field in dd-mm-yyyy format
		function setCurrentDate() {
			var currentDate = new Date();
			var day = currentDate.getDate();
			var month = currentDate.getMonth() + 1; // Months are zero-based, so add 1
			var year = currentDate.getFullYear();

			// Ensure day and month are always two digits
			day = (day < 10) ? '0' + day : day;
			month = (month < 10) ? '0' + month : month;

			var formattedDate = day + '-' + month + '-' + year;

			// Set the value of the date input field to the formatted date
			$('input[type="text"].date_prescribed').val(formattedDate);
		}

		function nubuDisableFields($section) {
			$section.find(':input').each(function () {
				if ($(this).prop('required')) {
					$(this).attr('data-was-required', 'true');
				}

				$(this)
					.prop('required', false)
					.prop('disabled', true);
			});
		}

		function nubuEnableFields($section) {
			$section.find(':input').each(function () {
				$(this).prop('disabled', false);

				if ($(this).attr('data-was-required') === 'true') {
					$(this).prop('required', true);
				}
			});
		}

		$('body').on('change', '.nubu_patient_order_type .nubu_number_of_patients', function(e) {
			let numberOfPatients = $(this).val();

			let $form = $(this).closest('.nubu_prescription_form');
			let $wrapper = $(this).closest('.nubu_patient_details_wrapper');

			if ('single' === numberOfPatients) {
				$form.find('.nubu_add_patient').fadeOut('fast');

				$form.find('.nubu_units_prescribed, .nubu_product_prescribed').prop('required', false);

				if ($wrapper.find('.nubu_patient_details').length > 1) {
					let $firstPatient = $wrapper.find('.nubu_patient_details:nth-child(2)');
					let $hiddenPatients = $wrapper.find('.nubu_patient_details').not(':nth-child(2)');

					nubuEnableFields($firstPatient);
					nubuDisableFields($hiddenPatients);

					$hiddenPatients.fadeOut('fast');
					$firstPatient.find('.nubu_patient_details_body').fadeIn('fast');
					$firstPatient.find('.nubu_patient_details_header').fadeOut('fast');
				}

				let $productFieldWrap = $wrapper.find('.nubu_product_prescribed').closest('.nubu_field_wrap');

				$productFieldWrap.fadeOut('fast');
				nubuDisableFields($productFieldWrap);

			} else {
				$form.find('.nubu_add_patient').fadeIn('fast');

				$form.find('.nubu_units_prescribed, .nubu_product_prescribed').prop('required', true);

				if ($wrapper.find('.nubu_patient_details').length > 1) {
					let $allPatients = $wrapper.find('.nubu_patient_details');

					nubuEnableFields($allPatients);

					$allPatients.fadeIn('fast');
					$allPatients.not(':last-child').find('.nubu_patient_details_body').fadeOut('fast');
					$allPatients.not(':last-child').find('.nubu_patient_details_header').fadeIn('fast');
				}

				let $productFieldWrap = $wrapper.find('.nubu_product_prescribed').closest('.nubu_field_wrap');

				$productFieldWrap.fadeIn('fast');
				nubuEnableFields($productFieldWrap);
			}
		});

		// Set the current date when the page loads
		//setCurrentDate();
		let counter = 1;

		// Add a new section when the "Add Section" button is clicked
		$(document).on('click', '.nubu_add_patient', function() {
			
			var isValid = true;
			var error = '';
			var addPatientBtn = $(this);
			var patientCheck = false;
			
			$(this).prev('.nubu_form_error').text('');
			$(this).closest('.nubu_prescription_form').find('label').removeClass('error');
			
			addPatientBtn.closest('.nubu_prescription_form').find('.nubu_number_of_patients').each(function(){
				if ( $(this).prop('checked') ) {
					patientCheck = true;
				}
			});
			
			if ( ! patientCheck ) {
				addPatientBtn.prev('.nubu_form_error').text('Fields in red are required fields');
				addPatientBtn.closest('.nubu_prescription_form').find('.nubu_patient_order_type > label').addClass('error');
				return false;
			}

			// Validate the fields
			$('.nubu_patient_details:last-child').find('.nubu_prescription_form_field').each(function(index) {
				if ( $(this).prop('required') ) {
					if ( $(this).val().trim() === '') {
						if ( $(this).prev('label').length > 0 ) {
							$(this).prev('label').addClass('error');
						} else {
							$(this).closest('.nubu_field_inner_wrap').prev('.nubu_field_inner_wrap').find('label').addClass('error');
						}
						
						isValid = false;
						error = 'Fields in red are required fields';
						//$('#nubu_form_error_msg_' + (index + 1)).text('This field is required');
					}					
				}

				if (/<script.*?>.*?<\/script>/gi.test($(this).val())) {
					if ( $(this).prev('label').length > 0 ) {
						$(this).prev('label').addClass('error');
					} else {
						$(this).closest('.nubu_field_inner_wrap').prev('.nubu_field_inner_wrap').find('label').addClass('error');
					}
					isValid = false;
					error = 'Invalid Input';
					//$('#nubu_form_error_msg_' + (index + 1)).text('Invalid input (no scripts allowed)');
				}
			});

			if ( ! isValid ) {
				$(this).prev('.nubu_form_error').text(error);
				return false;
			}
			
			//var totalSection = $('.nubu_patient_details').length;
			var sectionCount = counter;

			// Clone the first section, and update IDs and names
			var newSection = $('.nubu_patient_details:last-child').clone().attr('id', 'nubu_patient_' + (sectionCount + 1));
			newSection.find('.nubu_patient_name').html('');
			newSection.find('.nubu_product_name').html('');

			// Update all IDs and names to reflect the new section count
			newSection.find('input, textarea, select').each(function() {
				//var newId = $(this).attr('id').replace('_'+sectionCount, '_' + (sectionCount + 1));
				//var newName = $(this).attr('name').replace('[]', '[' + sectionCount + ']');	
				
				var currentId = $(this).attr('id');
        		// Check if the id matches the pattern a_b_c_<number>
		        var newId = currentId.replace(/(_\d+)$/, `_${(sectionCount + 1)}`);
				$(this).attr('id', newId);
				
				var currentName = $(this).attr('name');
		        // Replace any name like 'prefix[]' or 'prefix[1]' with 'prefix[$index]'
        		var newName = currentName.replace(/([a-zA-Z0-9_]+)\[\d*\]/g, '$1[$index]');
				// Replace '$index' with the actual value of the variable
				$(this).attr('name', newName.replace('$index', sectionCount));
				
				//$(this).attr('id', newId);
				//$(this).attr('id', newId).attr('name', newName);
				if ( 'nubu_required_prescription_form' === addPatientBtn.closest('.nubu_prescription_form').attr('id') ) {
					if ( ! $(this).hasClass('nubu_prescription_date') && ! $(this).hasClass('nubu_dispensing_pharmacy') && ! $(this).hasClass('nubu_order_number') && ! $(this).hasClass('nubu_product_prescribed') && ! $(this).hasClass('nubu_csc_client_number') ) {
						$(this).val(''); // Clear the values of the new section	
					}
				} else {
					if ( ! $(this).hasClass('nubu_prescription_date') && ! $(this).hasClass('nubu_dispensing_pharmacy') && ! $(this).hasClass('nubu_order_number') ) {
						$(this).val(''); // Clear the values of the new section	
					}
				}
			});
			
			newSection.find('.nubu_patient_details_body').show();

			// Insert the new section before the submit button
			$('.nubu_patient_details_wrapper').append(newSection);
			/*
			let fname 	= $('#nubu_patient_'+sectionCount).find('.nubu_patient_first_name').val(),
				lname 	= $('#nubu_patient_'+sectionCount).find('.nubu_patient_last_name').val(),
				product = $('#nubu_patient_'+sectionCount).find('.nubu_product_prescribed').val(),
				units 	= $('#nubu_patient_'+sectionCount).find('.nubu_units_prescribed').val(),	
				name  	= sectionCount +'. ' + fname + ' ' + lname,
				prodData = product + ' x ' + units;

			$('#nubu_patient_'+sectionCount).find('.nubu_patient_name').html(name);
			$('#nubu_patient_'+sectionCount).find('.nubu_product_name').html(prodData);
			*/
			
			//let sectionNumber = parseInt($('.nubu_patient_details').length) - 1;
		
			let fname 	= newSection.prev('.nubu_patient_details').find('.nubu_patient_first_name').val(),
				lname 	= newSection.prev('.nubu_patient_details').find('.nubu_patient_last_name').val(),
				product = newSection.prev('.nubu_patient_details').find('.nubu_product_prescribed').val(),
				units 	= newSection.prev('.nubu_patient_details').find('.nubu_units_prescribed').val(),	
				name  	= fname + ' ' + lname,
				prodData = product + ' x ' + units;

			newSection.prev('.nubu_patient_details').find('.nubu_patient_name').html(name);
			newSection.prev('.nubu_patient_details').find('.nubu_product_name').html(prodData);
			/*
			$('#nubu_patient_'+sectionCount).find('.nubu_patient_details_body').hide('fast',function(){
				$('.nubu_patient_details_header').show('fast');	
			});
			*/
			
			$('.nubu_patient_details_header').show('fast', function(){
				newSection.prev('.nubu_patient_details').find('.nubu_patient_info').show('fast');
				newSection.prev('.nubu_patient_details').find('.nubu_patient_details_body').hide('fast');
				newSection.prev('.nubu_patient_details').find('.nubu_edit_patient').show('fast');
				newSection.prev('.nubu_patient_details').find('.nubu_save_patient').hide('fast');
				newSection.find('.nubu_save_patient').hide('fast');
				newSection.find('.nubu_edit_patient').hide('fast');
			});
			
			if ( $('.nubu_patient_details').length > 1 ) {
				$('.nubu_remove_patient').show();
			}
			
			newSection.find('.nubu_patient_first_name').focus();
			
			counter++;
			
			$( 'html, body' ).animate({
				scrollTop: ( newSection.prev().offset().top - 50 )
			});

			// Set the current date for the newly added section
			//setCurrentDate();
		
			//$('.nubu_remove_patient').show();
			//$('.nubu_add_patient').hide();
		});
		
		/*
		$(document).on('click', '.nubu_edit_patient', function() {
			let $this = $(this);
			$this.closest('.nubu_patient_details').find('.')
		});
		*/

		// Delete the section
		$(document).on('click', '.nubu_remove_patient', function() {
			var totalSections = $('.nubu_patient_details').length;
			if (totalSections > 1) {
				$(this).closest('.nubu_patient_details').remove();
				//checkFormFields( $('.nubu_prescription_form_field') );
			}

			// Hide the delete button if there is only one section left
			if ( $('.nubu_patient_details').length === 1) {
				$('.nubu_remove_patient').hide();
				//$('.nubu_edit_patient').show();
				//checkFormFields( $('.nubu_prescription_form_field') );
			}
		});

		// Edit the section
		$(document).on('click', '.nubu_edit_patient', function() {
			let $this = $(this);
			$this.fadeOut('fast',function(){
				$this.closest('.nubu_patient_details_header').find('.nubu_patient_info').fadeOut('fast', function(){
					$this.closest('.nubu_patient_details').find('.nubu_patient_details_body').fadeIn('fast', function() {
						$this.next('.nubu_save_patient').fadeIn('fast');
					});
				});
			});
		});

		// Edit the section
		$(document).on('click', '.nubu_save_patient', function() {
			let $this = $(this),
				fname 	= $this.closest('.nubu_patient_details').find('.nubu_patient_first_name').val(),
				lname 	= $this.closest('.nubu_patient_details').find('.nubu_patient_last_name').val(),
				product = $this.closest('.nubu_patient_details').find('.nubu_product_prescribed').val(),
				units 	= $this.closest('.nubu_patient_details').find('.nubu_units_prescribed').val(),	
				name  	= fname + ' ' + lname,
				prodData = product + ' x ' + units;

			$this.closest('.nubu_patient_details').find('.nubu_patient_name').html(name);
			$this.closest('.nubu_patient_details').find('.nubu_product_name').html(prodData);
			$this.fadeOut('fast',function(){
				$this.closest('.nubu_patient_details').find('.nubu_patient_details_body').fadeOut('fast',function(){
					$this.closest('.nubu_patient_details_header').find('.nubu_patient_info').fadeIn('fast', function(){
						$this.prev('.nubu_edit_patient').fadeIn('fast');
					});
				});
			});
		});

		/*
		$('#nubu_prescription_form').on('input change', '.nubu_prescription_form_field', function() {
			checkFormFields( $(this) );
		});
		
		checkFormFields( $('.nubu_prescription_form_field'));
*/
		// Handle form submission
		$('#nubu_prescription_form').on('submit', function(e) {
			e.preventDefault();
			let $this = $(this);
			//$(this).find('.nubu_patient_details_wrapper').fadeTo( '300', 0.2, function() {
				$this.addClass('nubu_form_processing');
				$this.find('.nubu_prescription_form_submit').prop('disabled',true);
				$this.find('.nubu_add_patient').prop('disabled',true);
				$this.append('<span class="loader"></span>');

				// Clear previous errors
				$('.error').text('');

				var isValid = true;
				var isSingle 		= false;
				var patientCheck 	= false;
			
				$this.find('.nubu_patient_order_type .nubu_number_of_patients').each(function(){
					if ( $(this).prop('checked') ) {
						if ( 'single' === $(this).val() ) {
							isSingle = true;
						}
						patientCheck = true;
					}
				});
				
				if ( ! patientCheck ) {
					$this.find('.nubu_form_error').text('Fields in red are required fields');
					$this.find('.nubu_patient_order_type > label').addClass('error');
					$this.find('.loader').remove();
					$this.removeClass('nubu_form_processing');
					$this.find('.nubu_prescription_form_submit').prop('disabled',false);
					$this.find('.nubu_add_patient').prop('disabled',false);
					return false;
				}

				if ( patientCheck && ! isSingle ) {
					if ( $this.find('.nubu_patient_details').length < 2 ) {
						$this.find('.nubu_form_error').text('Please add more patients');
						$this.find('.loader').remove();
						$this.removeClass('nubu_form_processing');
						$this.find('.nubu_prescription_form_submit').prop('disabled',false);
						$this.find('.nubu_add_patient').prop('disabled',false);
						return false;
					}
				}

				$this.find('.nubu_patient_details').each(function(){
					$(this).find('.nubu_prescription_form_field').each(function(index) {
						if ( $(this).prop('required') ) {
							if ( $(this).val().trim() === '') {
								isValid = false;
								//$('#error_msg_' + (index + 1)).text('This field is required');
							}					
						}

						if (/<script.*?>.*?<\/script>/gi.test($(this).val())) {
							isValid = false;
							//$('#error_msg_' + (index + 1)).text('Invalid input (no scripts allowed)');
						}
					});
				});

				if (!isValid) {
					$this.removeClass('nubu_form_processing');
					$this.find('.loader').remove();
					$this.find('.nubu_prescription_form_submit').prop('disabled',false);
					$this.find('.nubu_add_patient').prop('disabled',false);
					//$this.find('.nubu_patient_details_wrapper').fadeTo( '300', 1);
					return false;
				}

				var formData = $this.serializeArray();
			
				if ( isSingle ) {
					formData = formData.filter(item => {
						return item.name.includes('[]') && !item.name.match(/\[\d+\]/);
					});
				}

				// Create the array of patients
				var patientsData = [];
				var patientFields = [
					'date_prescribed', 'order_number', 'patient_first_name', 'patient_last_name', 'product_prescribed', 
					'units_prescribed', 'dispensing_pharmacy', 'prescribing_doctor_first_name', 
					'prescribing_doctor_last_name', 'prescribing_doctor_clinic', 'indication_prescribed_for', 'dosage'
				];

				// Get the number of patients
				var patientCount = formData.filter(function(item) {
					return item.name.includes('date_prescribed'); // Only consider fields related to patients
				}).length;
				
				var indexes = [];
				$this.find('.nubu_patient_details').each(function(){
					indexes.push( $(this).prop('id').replace("nubu_patient_", "") );
				})

				// Collect data for each patient
				/*
				for (var i = 0; i < patientCount; i++) {
					var patientDetails = {};
					patientFields.forEach(function(field) {
						var fieldValue = formData.filter(function(item) {
							if ( i == 0 ) {
								i = '';
							}
							return item.name === field + '[' + i + ']'; // Match the right index for patient fields
						}).map(function(item) {
							return item.value;
						}).join('');
						patientDetails[field] = fieldValue;
					});
					patientsData.push(patientDetails); // Add patient details to the array
				}
				*/
				$.each(indexes, function(key,val) {
					var i = parseInt(val) - 1;
           			var patientDetails = {};
					patientFields.forEach(function(field) {
						var fieldValue = formData.filter(function(item) {
							if ( i == 0 ) {
								i = '';
							}
							return item.name === field + '[' + i + ']'; // Match the right index for patient fields
						}).map(function(item) {
							return item.value;
						}).join('');
						patientDetails[field] = fieldValue;
					});
					patientsData.push(patientDetails); // Add patient details to the array
				});
			
				var filteredPatientsData = patientsData.filter(obj => {
					return Object.values(obj).some(value => value !== "");
				});
			
				$.ajax({
					type: 'POST',  
					url: nubuData.ajaxurl,
					data: {
						action: 'nubu_send_prescription_data', 
						nonce:  nubuData.ajaxnonce,
						form_type: 'optional',
						patients: filteredPatientsData,
						single: isSingle ? 'yes' : 'no'
					},
					success: function(response) {
						$('.et_pb_wc_checkout_payment_info .nubu_prescription_form_wrapper').fadeOut('fast',function(){
							$('.et_pb_wc_checkout_payment_info .woocommerce-thankyou-order-received, .et_pb_wc_checkout_payment_info .woocommerce-order-overview, .et_pb_wc_checkout_payment_info .woocommerce-order-details, .et_pb_wc_checkout_payment_info .woocommerce-customer-details, .et_pb_wc_checkout_payment_info .et_pb_ab_shop_conversion ').fadeIn('fast', function(){
								$( 'html, body' ).animate({
									scrollTop: ( $('.et_pb_wc_checkout_payment_info .woocommerce-order').offset().top - 50 )
								});

							});
						});
					},
					error: function(){
						$this.removeClass('nubu_form_processing');
						$this.find('.loader').remove();
						//$this.find('.nubu_patient_details_wrapper').fadeTo( '300', 1);
						$this.find('.nubu_prescription_form_submit').prop('disabled',false);
						$this.find('.nubu_add_patient').prop('disabled',false);
					}
				});
			//});
		});
		
		$('#nubu_required_prescription_form').on('submit', function(e) {
			e.preventDefault();
			let $this = $(this);
			let product_id = $this.data('product_id');

			//$(this).find('.nubu_patient_details_wrapper').fadeTo( '300', 0.2, function() {
				$this.addClass('nubu_form_processing');
				$this.find('.nubu_form_error').text('');
				$this.find('.nubu_prescription_form_submit').prop('disabled',true);
				$this.find('.nubu_add_patient').prop('disabled',true);
				$this.append('<span class="loader"></span>');

				// Clear previous errors
				$('.error').text('');

				var isValid = true;
				var cscNumberInvalid = false;
				var totalQuantity = 1;
				var filledQuantity = 0;
			
				if ( $('body').find('form.cart[data-product_id='+ product_id +']').length > 0 ) {
					totalQuantity = parseInt( $('body').find('form.cart[data-product_id='+ product_id +']').find('.qty').val() );
				} else if ( $('body').find('.products .product.post-'+product_id+' .add_to_cart_button').length > 0 ) {
					totalQuantity = parseInt( $('body').find('.products .product.post-'+product_id+' .add_to_cart_button').data('quantity') );
				}
	
				$this.find('.nubu_patient_details').each(function(){
					$(this).find('.nubu_prescription_form_field').each(function(index) {
						if ( $(this).prop('required') ) {
							if ( $(this).val().trim() === '') {
								isValid = false;
								//$('#error_msg_' + (index + 1)).text('This field is required');
							}
							if ( $(this).hasClass('nubu_units_prescribed') ) {
								filledQuantity = parseInt(filledQuantity) + parseInt($(this).val());
							}
							if ( $(this).hasClass('nubu_csc_client_number') ) {
								if (!/^\d{11}$/.test($(this).val())) {
									cscNumberInvalid = true;
									isValid = false;
								}
							}
						}

						if (/<script.*?>.*?<\/script>/gi.test($(this).val())) {
							isValid = false;
							//$('#error_msg_' + (index + 1)).text('Invalid input (no scripts allowed)');
						}
					});
				});
				
				if ( filledQuantity !== totalQuantity ) {
					$this.find('.nubu_form_error').text('The quantity added to the cart doesn’t match the total units prescribed.');
					isValid = false;
				}
			
				if ( cscNumberInvalid ) {
					$this.find('.nubu_form_error').text('The CSC Client Number is not valid.');
				}

				if (!isValid) {
					$this.removeClass('nubu_form_processing');
					$this.find('.loader').remove();
					$this.find('.nubu_prescription_form_submit').prop('disabled',false);
					$this.find('.nubu_add_patient').prop('disabled',false);
					return false;
				}

				var formData = $this.serializeArray();

				// Create the array of patients
				var patientsData = [];
				var patientFields = [
					'date_prescribed', 'order_number', 'patient_first_name', 'patient_last_name', 'csc_client_number', 'product_prescribed', 
					'units_prescribed', 'dispensing_pharmacy', 'prescribing_doctor_first_name', 
					'prescribing_doctor_last_name', 'prescribing_doctor_clinic', 'indication_prescribed_for', 'dosage'
				];

				// Get the number of patients
				var patientCount = formData.filter(function(item) {
					return item.name.includes('date_prescribed'); // Only consider fields related to patients
				}).length;
				
				var indexes = [];
				$this.find('.nubu_patient_details').each(function(){
					indexes.push( $(this).prop('id').replace("nubu_patient_", "") );
				})
			
				$.each(indexes, function(key,val) {
					var i = parseInt(val) - 1;
           			var patientDetails = {};
					patientFields.forEach(function(field) {
						var fieldValue = formData.filter(function(item) {
							if ( i == 0 ) {
								i = '';
							}
							return item.name === field + '[' + i + ']'; // Match the right index for patient fields
						}).map(function(item) {
							return item.value;
						}).join('');
						patientDetails[field] = fieldValue;
					});
					patientsData.push(patientDetails); // Add patient details to the array
				});
				
				$.ajax({
					type: 'POST',  
					url: nubuData.ajaxurl,
					data: {
						action: 'nubu_send_prescription_data', 
						nonce:  nubuData.ajaxnonce,
						patients: patientsData,
						form_type: 'required',
						required_form: 'yes',
						product_id: product_id
					},
					success: function(response) {
						nubuResetPrescriptionForm($this);
						$this.removeClass('nubu_form_processing');
						$this.find('.loader').remove();
						$this.find('.nubu_prescription_form_submit').prop('disabled',false);
						$this.find('.nubu_add_patient').prop('disabled',false);
						$this.closest('.nubu_modal').find('.nubu_modal_popup_close_icon').trigger('click');
						if ( $('body').find('form.cart[data-product_id='+ product_id +']').length > 0 ) {
							$('body').find('form.cart[data-product_id='+ product_id +']').data('approved', 'yes');
							$('body').find('form.cart[data-product_id='+ product_id +']').trigger('submit');
						} else if ( $('body').find('.products .product.post-'+product_id+' .add_to_cart_button').length > 0 ) {
							$('body').find('.products .product.post-'+product_id+' .add_to_cart_button').data('approved', 'yes');
							$('body').find('.products .product.post-'+product_id+' .add_to_cart_button').trigger('click');
						}
					},
					error: function(){
						$this.removeClass('nubu_form_processing');
						$this.find('.loader').remove();
						//$this.find('.nubu_patient_details_wrapper').fadeTo( '300', 1);
						$this.find('.nubu_prescription_form_submit').prop('disabled',false);
						$this.find('.nubu_add_patient').prop('disabled',false);
					}
				});
			//});
		});

	});
    
	function nubuResetPrescriptionForm(form){
		/* ==========================
		   REMOVE EXTRA PATIENTS
		========================== */
		form.find('.nubu_patient_details')
			.not('#nubu_patient_1')
			.remove();

		/* ==========================
		   RESET FIRST PATIENT
		========================== */
		const first = form.find('#nubu_patient_1');
		first.find('.nubu_patient_details_header').remove();
		first.find('.nubu_patient_details_body').fadeIn('fast');
		first.find('input').each(function(){

			if($(this).attr('type') === 'hidden'){
				return;
			}

			if($(this).hasClass('nubu_prescription_date')){
				return; // keep default date
			}

			$(this).val('');
		});
	}
	
	function customTagStyles(){
        $('.archive .nubu_product_tag_wrapper, .product.single-product .nubu_product_tag_wrapper, .single-product .nubu_product_tag_wrapper').each(function(index, element){
            var product = $(this);
            var saleBadge = product.find('.nubu_custom_tag_onsale');
            //var impcolor = saleBadge.attr('class').split(/\s+/);
            if(saleBadge.length){
                saleBadge.each(function(index,element) {
                    var classes = product.attr('class').split(/\s+/);
                    $.each(classes, function(index, className){
                        if(className.startsWith('nubu_cust_')){
                            product.css('display','flex');
                            var parts = className.split('_');

                            if(parts.length >= 4){
                                var property = parts[2]; 
                                if('bgcolor' === property){
                                    var color = parts.slice(3).join('_'); 
                                    product.css('background-color', color);
                                }
                                if('bgcolorimp' === property){
                                    var color = parts.slice(3).join('_'); 
                                    if(0 !== color.length){
                                        product.css('background-color', color);    
                                    }
                                }
                                if('show' === property){
                                    var show = parts.slice(3).join('_'); 
                                    if( 'no' === show ){
                                        product.css('display', 'none');    
                                    }
                                }
                                if('color' === property){
                                    var textColor = parts.slice(3).join('_'); 
                                    product.css('color', textColor);
                                }
								/*
                                if('size' === property){
                                    var size = parts.slice(3).join('_');
                                    product.css('font-size', size+'px');
                                }
								*/
                                if('pos' === property || 'shape' === property){
                                    var value = parts.slice(3).join('_'); 
                                }

                                if(property == 'pos' && value === 'top-right'){
                                    product.css('left', 'unset');
                                    product.css('right', '1rem');
                                    product.css('top', '1rem');
                                } else if( property == 'pos' && value === 'top-left' ){
                                    product.css('right', 'unset');
                                    product.css('left', '1rem');
                                    product.css('top', '1rem');
                                }
                                if(property == 'shape' && value === 'circle'){
                                    //element.style.setProperty('border-radius', '50% ', 'important');
									//element.style.setProperty('min-height', '80px ', 'important');
									//element.style.setProperty('width', '80px ', 'important');
									fitText($(element));
                                } else if( property == 'shape' && value === 'rectangle' ){
                                    product.css('border-radius', '0 ');
                                }
                            }
                        }
                    });
                });
            }
        });
    }
    customTagStyles();
	function fitText($el) {
		let maxFont = 18;
		let minFont = 6;

		let $parent = $el.parent();
		
		let safeFactor = 0.75;
		let availableWidth = $parent.innerWidth() * safeFactor;
		let availableHeight = $parent.innerHeight() * safeFactor;

		$el.css('font-size', maxFont + 'px');
		while (
			($el[0].scrollWidth > availableWidth ||
			 $el[0].scrollHeight > availableHeight) &&
			maxFont > minFont
		) {
			maxFont--;
			$el.css('font-size', maxFont + 'px');
		}
	}
    $(document).on('et_pb_after_init_modules load', customTagStyles);

	if(document.getElementById("donutChart-" + nubuData.post_id)){
        let $this = $("#donut-" + nubuData.post_id);
        let ingredients = $this.data('title'),
            descs = $this.data('description'),
            colors = $this.data('color');
            quant = $this.data('quant');
        const originalColors = colors;

        var ctx = document.getElementById("donutChart-" + nubuData.post_id).getContext('2d');

        var donutChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ingredients,
                datasets: [{    
                    data: quant,
                    backgroundColor: colors,
                    borderWidth:0,
                    descriptions: descs,
                }]
            },         
            options: {
                responsive: true,
                maintainAspectRatio: true,
                layout: {
                    padding: 0
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        enabled: false,
                        external: externalTooltipHandler
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },

                onHover: (event, activeElements, chart) => {
                    const dataset = chart.data.datasets[0];

                    if (activeElements.length) {
                        const hoveredIndex = activeElements[0].index;

                        dataset.backgroundColor = originalColors.map((color, index) => {
                            return index === hoveredIndex
                                ? color
                                : color + '4D';
                        });
                    } else {
                        dataset.backgroundColor = [...originalColors];
                    }

                    chart.update({
                        duration: 200,
                        easing: 'easeOutQuad'
                    });
                }
            }
        });
        donutChart.canvas.addEventListener('mouseleave', () => {
            donutChart.data.datasets[0].backgroundColor = [...originalColors];
            donutChart.setActiveElements([]);
            donutChart.update({ duration: 150 });
        });

        function externalTooltipHandler(context) {
            const { chart, tooltip } = context;

            let tooltipEl = document.getElementById('chartjs-custom-tooltip');

            if (!tooltipEl) {
                tooltipEl = document.createElement('div');
                tooltipEl.id = 'chartjs-custom-tooltip';
                tooltipEl.className = 'chartjs-custom-tooltip';

                tooltipEl.innerHTML = `
                    <div class="chartjs-tooltip-title"></div>
                    <div class="chartjs-tooltip-desc"></div>
                    <div class="chartjs-tooltip-percent"></div>
                `;

                document.body.appendChild(tooltipEl);
            }

            if (tooltip.opacity === 0) {
                tooltipEl.classList.remove('show');
                return;
            }

            const dataPoint = tooltip.dataPoints[0];
            const dataset = chart.data.datasets[dataPoint.datasetIndex];

            //Title
            const title = dataPoint.label;

            //Content
            const description = dataset.descriptions
                ? dataset.descriptions[dataPoint.dataIndex]
                : '';

            const percent = dataPoint.raw+'%';

            tooltipEl.querySelector('.chartjs-tooltip-title').innerHTML = title;
            tooltipEl.querySelector('.chartjs-tooltip-desc').innerHTML = description;
            tooltipEl.querySelector('.chartjs-tooltip-percent').innerHTML = percent;

            // Positioning
            const canvasRect = chart.canvas.getBoundingClientRect();

            tooltipEl.style.position = 'absolute';
            tooltipEl.style.left =
                canvasRect.left + window.scrollX + tooltip.caretX + 'px';

            tooltipEl.style.top =
                canvasRect.top + window.scrollY + tooltip.caretY + 'px';

            tooltipEl.classList.add('show');
        }


        
        function fadeColor(color, alpha = '4D') {
            if (color.length === 9) return color;
            return color + alpha;
        }
        
        function renderTwoColumnLegend(chart, containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';

            const labels = chart.data.labels;
            const colors = chart.data.datasets[0].backgroundColor;

            const total = labels.length;
            const splitIndex = Math.ceil(total / 2);

            const col1 = labels.slice(0, splitIndex);
            const col2 = labels.slice(splitIndex);

            const createColumn = (items, startIndex) => {
                const col = document.createElement('div');
                col.className = 'legend-column';

                items.forEach((label, i) => {
                    const index = startIndex + i;

                    const item = document.createElement('div');
                    item.className = 'legend-item';
                    item.innerHTML = `
                        <span class="legend-color" style="background:${colors[index]}"></span>
                        <span class="legend-text">${label}</span>
                    `;

                    item.onmouseenter = () => {
                        chart.setActiveElements([{ datasetIndex: 0, index }]);
                        chart.data.datasets[0].backgroundColor =
                        originalColors.map((c, i) =>
                            i === index ? c : fadeColor(c)
                        );
                        chart.update();
                    };

                    item.onmouseleave = () => {
                        chart.setActiveElements([]);
                        chart.data.datasets[0].backgroundColor = [...originalColors];
                        chart.update();
                    };

                    col.appendChild(item);
                });

                return col;
            };

            container.appendChild(createColumn(col1, 0));
            container.appendChild(createColumn(col2, splitIndex));
        }
        
        renderTwoColumnLegend(
            donutChart,
            'legend-' + nubuData.post_id
        );
    }
    
}( jQuery ));
