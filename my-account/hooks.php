<?php
function nubu_my_account_enqueue_scripts() {
		wp_enqueue_script('my-account', get_stylesheet_directory_uri() . '/js/my-account.js', array('jquery'), '0.0.1', true);
		wp_enqueue_style('my-account', get_stylesheet_directory_uri() . '/css/my-account.css', array(), '0.0.2' );
		wp_enqueue_style( 'dashicons' );
}
add_action( 'wp_enqueue_scripts', 'nubu_my_account_enqueue_scripts' );

function nubu_get_order_product_images($order) {
    if (!$order) {
        return;
    }
	
	$images = array();
	$count 	= 0;
    // Loop through order items
    foreach ($order->get_items() as $item_id => $item) {
		if ( $count >= 2 ) break;
        // Get the product object
        $product = $item->get_product();

        if (!$product) {
            continue;
        }

        // Get product image URL (thumbnail)
        $image_id 	= $product->get_image_id();
        $image 		= wp_get_attachment_image($image_id, 'thumbnail');
		array_push( $images, $image );
		$count++;
    }
	
	return $images;
}

function nubu_my_account_endpoints() {
	add_rewrite_endpoint( 'licence', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'section-29', EP_ROOT | EP_PAGES );
	add_rewrite_rule('^pharmacy-licence/?$', 'index.php?pharmacy_licence=1', 'top');
	add_rewrite_rule('^section-29-data/?$', 'index.php?section_29_data=1', 'top');
	add_rewrite_endpoint( 'reports', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'nubu_my_account_endpoints' );

function nubu_my_account_query_vars( $vars ) {
	//if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) ) ) {
	    $vars[] = 'licence';
		$vars[] = 'pharmacy_licence';
		$vars[] = 'section_29_data';
//	}
	$vars[] = 'reports';
	$vars[] = 'filter';
	$vars[] = 'date';
	return $vars;
}
add_filter( 'query_vars', 'nubu_my_account_query_vars' );

function nubu_woocommerce_account_menu_items( $items, $endpoints ) {
	if ( is_user_logged_in() && ( current_user_can( 'trade_account' ) || current_user_can( 'administrator' ) ) ) {
		$items = array(
			'dashboard'       	=> esc_html__( 'Dashboard', 'woocommerce' ),
			'orders'          	=> esc_html__( 'Orders', 'woocommerce' ),
			'edit-address'    	=> _n( 'Address', 'Addresses', ( 1 + (int) wc_shipping_enabled() ), 'woocommerce' ),
			'edit-account'    	=> esc_html__( 'Account details', 'woocommerce' ),
			'section-29'	 	=> esc_html__( 'Section 29', 'divi' ),
			'licence'	 		=> esc_html__( 'Licence', 'divi' ),
			'reports'	 		=> esc_html__( 'Reports', 'divi' ),
			'standing-order'	=> esc_html__( 'Standing order', 'divi' ),
			'customer-logout' 	=> esc_html__( 'Log out', 'woocommerce' ),
		);
	} else {
		$items = array(
			'dashboard'       	=> esc_html__( 'Dashboard', 'woocommerce' ),
			'orders'          	=> esc_html__( 'Orders', 'woocommerce' ),
			'edit-address'    	=> _n( 'Address', 'Addresses', ( 1 + (int) wc_shipping_enabled() ), 'woocommerce' ),
			'edit-account'    	=> esc_html__( 'Account details', 'woocommerce' ),
			'reports'	 		=> esc_html__( 'Reports', 'divi' ),
			'standing-order'	=> esc_html__( 'Standing order', 'divi' ),
			'customer-logout' 	=> esc_html__( 'Log out', 'woocommerce' ),
		);	
	}
	
	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'nubu_woocommerce_account_menu_items', 10, 2 );

function nubu_woocommerce_before_account_navigation() {
	$current_user 	= wp_get_current_user();
	$display_name 	= $current_user->display_name;
    $avatar 		= get_avatar( $current_user->ID, 60 );
	?>
	<div class="nubu_myaccount_navigation_wrapper">
		<div class="nubu_myaccount_user">
			<span class="nubu_user_profile_picture"><?php echo wp_kses_post( $avatar );  ?></span>
			<span class="nubu_user_welcome_message">
				<?php esc_html_e( 'Hello,', 'divi' ) ?>
				<span class="nubu_user_display_name"><?php echo esc_html( $display_name ); ?></span>
			</span>
			
		</div>
	<?php
}
add_action( 'woocommerce_before_account_navigation', 'nubu_woocommerce_before_account_navigation' );

function nubu_woocommerce_after_account_navigation() {
	?>
	</div>
	<?php
}
add_action( 'woocommerce_after_account_navigation', 'nubu_woocommerce_after_account_navigation' );

function nubu_woocommerce_account_orders_columns( $columns ) {
	$columns = array(
		'order-date'    => esc_html__( 'Order Date', 'woocommerce' ),
		'order-number'  => esc_html__( 'Order Number', 'woocommerce' ),
		'order-status'  => esc_html__( 'Order Status', 'woocommerce' ),
		'order-total'   => esc_html__( 'Total Amount', 'woocommerce' ),
		'order-items'	=> esc_html__( 'Items', 'woocommerce' ),
		'order-actions' => esc_html__( 'Actions', 'woocommerce' ),
	);
	return $columns;
}
add_filter( 'woocommerce_account_orders_columns', 'nubu_woocommerce_account_orders_columns' );

function nubu_woocommerce_my_account_my_orders_actions( $actions, $order ) {
	if ( $order->has_status( array('completed', 'processing') ) ) {
        $actions['order-again'] = array(
            'url' => wp_nonce_url( add_query_arg( 'order_again', $order->get_id(), wc_get_cart_url() ), 'woocommerce-order_again' ),
            'name' => esc_html__( 'Re-Order', 'woocommerce' ),
        );
    }
	$actions['view'] = array(
		'url' => $order->get_view_order_url(),
		'name' => esc_html__( 'Order Details', 'woocommerce' ),
	);
	
	if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) ) {
        return array(); // Remove all actions
    }
	
    return $actions;
}
add_filter( 'woocommerce_my_account_my_orders_actions', 'nubu_woocommerce_my_account_my_orders_actions', 10, 2 );

function nubu_woocommerce_valid_order_statuses_for_order_again( $statuses ) {
	$statuses = array(
		'processing',
		'completed'
	);
	return $statuses;
}
add_filter( 'woocommerce_valid_order_statuses_for_order_again', 'nubu_woocommerce_valid_order_statuses_for_order_again' );

function nubu_woocommerce_my_account_my_orders_column_order_items($order) {
	$images = nubu_get_order_product_images($order);
	$total_images 	= count( $images );
	$total_items 	= count( $order->get_items() );
	?>
	<span class="nubu_woocommerce_orders_table_cell_header"><?php echo wp_kses_post( sprintf( _n( '%1$s Item', '%1$s Items', $total_items, 'woocommerce' ), $total_items ) ); ?></span>
	<?php
	foreach( $images as $image ) {
		echo $image;
	}
	if ( $total_images < $total_items ) {
		$remaining = $total_items - $total_images;
		echo '<span class="nubu_woocommerce_orders_remaining_items_wrapper"><span class="nubu_woocommerce_orders_remaining_items">+'. $remaining .'</span></span>';
	}
}
add_action( 'woocommerce_my_account_my_orders_column_order-items', 'nubu_woocommerce_my_account_my_orders_column_order_items' );

function nubu_licence_endpoint_content() {
	if ( ! is_user_logged_in() || ( ! current_user_can('trade_account') && ! current_user_can('administrator') ) ) {
		wp_safe_redirect( home_url() );
	    exit;
	}
	$user_id 			= get_current_user_id();
	$pharmacy_licence 	= get_user_meta($user_id, 'pharmacy_licence', true);
	$licence_expiry 	= get_user_meta($user_id, 'pharmacy_licence_expiry', true);
    //$file_url 		= $attachment_id ? wp_get_attachment_url($attachment_id) : '';
    ?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( 'Licence to operate pharmacy', 'divi' ); ?></h4>
		</div>
	</div>
	<div class="nubu_woocommerce_pharmacy_licence_wrapper">
		<div class="nubu_woocommerce_pharmacy_licence">
			<h5><?php esc_html_e( 'Upload your pharmacy licence', 'divi' ); ?></h5>
			<p><?php esc_html_e( 'Please upload a valid copy of your pharmacy licence for verification. This is required to insure compliance with regulatory guidelines before processing medical product orders.' ); ?></p>
			<form method="post" enctype="multipart/form-data">
				<div class="nubu_woocommerce_custom_uploader">
					<div class="nubu_woocommerce_custom_upload_dropzone">
						<p><?php esc_html_e( 'Click to Upload or drag and drop', 'divi' ); ?></p>
						<em><?php esc_html_e( 'File Supported: PDF, JPEG and PNG', 'divi' ); ?></em>
					</div>
					<input type="file" class="nubu_woocommerce_custom_upload" name="nubu_woocommerce_custom_upload" style="display: none;" accept=".pdf,.jpg,.jpeg,.png" />
					<p class="nubu_woocommerce_current_uploaded_file">
						<span class="nubu_woocommerce_selected_file_label"><?php esc_html_e( 'Selected file: ', 'divi'); ?></span>
						<span class="nubu_woocommerce_selected_file_name"></span>
					</p>
					<?php if ($pharmacy_licence){ ?>
						<p class="nubu_woocommerce_current_saved_file">
							<span class="nubu_woocommerce_current_saved_file_label"><?php esc_html_e( 'Current licence: ', 'divi'); ?></span>
							<span class="nubu_woocommerce_current_saved_file_link">
								<a href="<?php echo home_url('pharmacy-licence'); ?>" target="_blank"><?php esc_html_e( 'View Pharmacy Licence', 'divi' ); ?></a>
							</span>
						</p>
						<p class="nubu_woocommerce_saved_license_expiry_date">
							<span class="nubu_woocommerce_current_license_expiry_label"><?php esc_html_e( 'Current licence expiry date: ', 'divi'); ?></span>
							<span class="nubu_woocommerce_current_license_expiry_date"><?php echo esc_html( $licence_expiry ); ?></span>
						</p>
					<?php } ?>
				</div>
				<div class="nubu_woocommerce_license_expiry_wrapper">
					<p class="nubu_woocommerce_license_expiry">
						<span class="nubu_woocommerce_license_expiry_label"><?php esc_html_e( 'Licence Expiry Date: ', 'divi'); ?></span>
						<span class="nubu_woocommerce_license_expiry_date_wrapper"><input type="date" name="nubu_woocommerce_license_expiry_date" class="nubu_woocommerce_license_expiry_date" /></span>
					</p>
				</div>
				<button type="submit" name="nubu_woocommerce_licence_submit" class="nubu_woocommerce_licence_submit">
						<?php esc_html_e( 'Submit', 'divi' ); ?></button>
			</form>
		</div>
		<script>
        	jQuery(document).ready(function($) {
    			let $dropzoneWrapper 	= $('.nubu_woocommerce_custom_uploader'),
					$dropzone 			= $dropzoneWrapper.find('.nubu_woocommerce_custom_upload_dropzone'),
					$fileInput 			= $dropzoneWrapper.find('.nubu_woocommerce_custom_upload'),
					$fileNameDisplay 	= $dropzoneWrapper.find('.nubu_woocommerce_current_uploaded_file');
	
				function updateFileNameDisplay($file) {
					if ( $file ) {
						$fileNameDisplay.find('.nubu_woocommerce_selected_file_name').html($file.name);
						$fileNameDisplay.fadeIn('fast');
					} else {
						$fileNameDisplay.find('.nubu_woocommerce_selected_file_name').html('');
						$fileNameDisplay.fadeOut('fast');
					}
				}

				// Click to open file picker
				$dropzone.on('click', function() {
					$fileInput.trigger('click');
				});

				// Drag over styling
				$dropzone.on('dragover', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$dropzone.css('border-color', '#000');
				});

				// Drag leave styling
				$dropzone.on('dragleave', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$dropzone.css('border-color', '#ccc');
				});

				// File drop
				$dropzone.on('drop', function(e) {
					e.preventDefault();
					e.stopPropagation();
					$dropzone.css('border-color', '#ccc');

					let $files = e.originalEvent.dataTransfer.files;
					if ( $files.length > 0 ) {
						// Create a DataTransfer to simulate file selection
						let $dataTransfer = new DataTransfer();
						$dataTransfer.items.add( $files[0] ); // only 1 file
						$fileInput[0].files = $dataTransfer.files;

						// Manually trigger change
						$fileInput.trigger('change');
					}
				});

				// On file change (manual or drag)
				$fileInput.on('change', function() {
					var $file = this.files[0];
					updateFileNameDisplay($file);
				});
				
				$('form').on('submit', function(e) {
					var fileInput = $('.nubu_woocommerce_custom_upload');
					var licenseExpiry = $('.nubu_woocommerce_license_expiry_date').val();
					if ( fileInput.length && fileInput[0].files.length === 0 ) {
						alert('Please select a file before uploading.');
						e.preventDefault();
						return false;
					}
					if ( '' === licenseExpiry ) {
						alert('Please select a date for license expiry.');
						e.preventDefault();
						return false;
					}
				});
			});
    	</script>
	</div>
	<?php
}
add_action( 'woocommerce_account_licence_endpoint', 'nubu_licence_endpoint_content' );

function nubu_section_29_endpoint_content() {
	if ( ! is_user_logged_in() || ( ! current_user_can('trade_account') && ! current_user_can('administrator') ) ) {
		wp_safe_redirect( home_url() );
	    exit;
	}
	$user_id = get_current_user_id();
	
	$currentTime = current_time('timestamp'); // Gets timestamp in WP site's timezone
	$months = [];
	for ($i = 2; $i >= 0; $i--) {
		// Subtract months using strtotime with WP-localized timestamp
		$timestamp = strtotime("-$i month", $currentTime);
		$months[] = date_i18n('F Y', $timestamp);
	}
	?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( 'Add Section 29 Data', 'divi' ); ?></h4>
		</div>
	</div>
	<div class="nubu_woocommerce_section_29_wrapper">
		<div class="nubu_woocommerce_section_29">
			<h5><?php esc_html_e( 'Upload Your Section 29 File', 'divi' ); ?></h5>
			<p><?php printf( 'Please upload your Section 29 data file in the format specified. If you are unsure about the format, you can download the <a href="%1$s" target="_blank">template</a> to see the required structure. You should be able to extract this directly from your prescribing software as a CSV, CLS, XLSX.', esc_url( home_url('/wp-content/uploads/2025/05/NUBU-Section-29-Form.xlsx') ) ); ?></p>
			<p><?php printf( 'More information on our collective responsibilities when supplying Section 29 medicines can be found <a href="%1$s" target="_blank">here</a>.', esc_url( 'https://www.medsafe.govt.nz/profs/riss/unapp.asp#:~:text=.-,Section%2029%20of%20the%20Medicines%20Act%201981,efficacy%20of%20an%20unapproved%20medicine%20should%20be%20requested%20from%20the%20importer.,-Further%20information%20on' ) ); ?></p>
			<form method="post" enctype="multipart/form-data">
				<div class="nubu_woocommerce_section_29_group">
					<div class="nubu_woocommerce_section_29_row">
						<div class="nubu_woocommerce_section_29_month_wrapper">
							<em><?php esc_html_e( 'Select the month', 'divi' ); ?></em>
							<div class="nubu_dropdown nubu_woocommerce_section29_month_select">
								<div class="nubu_dropdown_toggle"><?php esc_html_e( 'Select', 'divi' ); ?></div>
								<ul class="nubu_dropdown_menu">
									<?php
									foreach( $months as $month ) {
										?><li data-value="<?php echo esc_attr( sanitize_text_field($month) ); ?>"><?php echo esc_html( $month ); ?></li><?php
									}
									?>
								</ul>
							</div>
							<input type="hidden" class="nubu_woocommerce_section29_month" name="nubu_woocommerce_section29_month[]" />
						</div>
						<div class="nubu_woocommerce_custom_uploader">
							<em><?php esc_html_e( 'File Supported: CSV, XLS and XLSX', 'divi' ); ?></em>
							<div class="nubu_woocommerce_custom_upload_dropzone">
								<p><?php esc_html_e( 'Click to Upload or drag and drop', 'divi' ); ?></p>
								<p class="nubu_woocommerce_selected_file_name"></p>
							</div>
							<input type="file" class="nubu_woocommerce_custom_upload" name="nubu_woocommerce_section_29_upload[]" style="display: none;" accept=".csv,.xls,.xlsx" />
						</div>
						<div class="nubu_woocommerce_section_29_actions">
							<a href="#" class="nubu_woocommerce_add_section_29_row">+</a>
						</div>
					</div>
				</div>
				<button type="submit" name="nubu_woocommerce_section_29_data_submit" class="nubu_woocommerce_section_29_submit">
						<?php esc_html_e( 'Submit', 'divi' ); ?></button>
			</form>
		</div>
		<script>
        	jQuery(document).ready(function($) {
    			let sectionClass = '.nubu_woocommerce_section_29_row';

				// Event delegation: handle click to open file picker
				$(document).on('click', '.nubu_woocommerce_custom_upload_dropzone', function () {
					const $row = $(this).closest(sectionClass);
					const $fileInput = $row.find('.nubu_woocommerce_custom_upload');
					$fileInput.trigger('click');
				});

				// Event delegation: handle dragover
				$(document).on('dragover', '.nubu_woocommerce_custom_upload_dropzone', function (e) {
					e.preventDefault();
					e.stopPropagation();
					$(this).css('border-color', '#000');
				});

				// Event delegation: handle dragleave
				$(document).on('dragleave', '.nubu_woocommerce_custom_upload_dropzone', function (e) {
					e.preventDefault();
					e.stopPropagation();
					$(this).css('border-color', '#ccc');
				});

				// Event delegation: handle drop
				$(document).on('drop', '.nubu_woocommerce_custom_upload_dropzone', function (e) {
					e.preventDefault();
					e.stopPropagation();
					const $dropzone = $(this).css('border-color', '#ccc');
					const $row = $dropzone.closest(sectionClass);
					const $fileInput = $row.find('.nubu_woocommerce_custom_upload');

					const files = e.originalEvent.dataTransfer.files;
					if (files.length > 0) {
						const dataTransfer = new DataTransfer();
						dataTransfer.items.add(files[0]); // one file only
						$fileInput[0].files = dataTransfer.files;

						$fileInput.trigger('change');
					}
				});

				// On file input change — update file name display
				$(document).on('change', '.nubu_woocommerce_custom_upload', function () {
					const $input = $(this);
					const $row = $input.closest(sectionClass);
					const $file = this.files[0];

					const $fileNameDisplay = $row.find('.nubu_woocommerce_selected_file_name');
					if ($file && $fileNameDisplay.length) {
						$fileNameDisplay.text($file.name);
						$fileNameDisplay.addClass('uploaded');
					} else if ($fileNameDisplay.length) {
						$fileNameDisplay.text('');
						$fileNameDisplay.removeClass('uploaded');
					}
				});
				
				/*
				// Optional: validate at least one file on submit
				$('form').on('submit', function (e) {
					let valid = true;

					$(sectionClass).each(function () {
						const $fileInput = $(this).find('.nubu_woocommerce_custom_upload');
						if (!$fileInput[0].files.length) {
							valid = false;
							return false; // break
						}
					});

					if (!valid) {
						alert('Please select a file for each row before uploading.');
						e.preventDefault();
					}
				});
				*/
				
				
				let sectionSelector = '.nubu_woocommerce_section_29_row';
				let container = $('.nubu_woocommerce_section_29_group'); // Update this selector

				// Function to get all selected months
				function getSelectedMonths() {
					const months = [];
					$('.nubu_woocommerce_section29_month').each(function () {
						const val = $(this).val();
						if (val && val !== '0') {
							months.push(val);
						}
					});
					return months;
				}

				// Function to get all possible months
				function getAllMonthValues() {
					const all = [];
					$('.nubu_dropdown_menu li[data-value]').each(function () {
						const val = $(this).data('value');
						if (val !== '0' && !all.includes(val)) {
							all.push(val);
						}
					});
					return all;
				}

				// Function to update row controls
				function updateRowControls() {
					const allRows = $(sectionSelector);
					const selectedMonths = getSelectedMonths();
					const allMonths = getAllMonthValues();
					const availableMonths = allMonths.filter(m => !selectedMonths.includes(m));

					allRows.each(function () {
						const $row = $(this);
						const $actions = $row.find('.nubu_woocommerce_section_29_actions');

						// Manage ADD button
						if (availableMonths.length === 0) {
							$actions.find('.nubu_woocommerce_add_section_29_row').remove();
						} else if ($actions.find('.nubu_woocommerce_add_section_29_row').length === 0) {
							$actions.prepend('<a href="#" class="nubu_woocommerce_add_section_29_row">+</a>');
						}

						// Manage REMOVE button
						if (allRows.length === 1) {
							$actions.find('.nubu_woocommerce_remove_section_29_row').remove();
						} else if ($actions.find('.nubu_woocommerce_remove_section_29_row').length === 0) {
							$actions.append('<a href="#" class="nubu_woocommerce_remove_section_29_row">-</a>');
						}

						// Filter out selected months from dropdown
						const currentVal = $row.find('.nubu_woocommerce_section29_month').val();
						$row.find('.nubu_dropdown_menu li[data-value]').each(function () {
							const val = $(this).data('value');
							if (val === '0') return;
							if (selectedMonths.includes(val) && val !== currentVal) {
								$(this).hide();
							} else {
								$(this).show();
							}
						});
					});
				}

				// Event delegation for ADD button
				$(document).on('click', '.nubu_woocommerce_add_section_29_row', function (e) {
					e.preventDefault();

					const allSelectedMonths = getSelectedMonths();
					const allMonths = getAllMonthValues();
					const availableMonths = allMonths.filter(m => !allSelectedMonths.includes(m));

					if (availableMonths.length === 0) return;

					const $currentRow = $(this).closest(sectionSelector);
					const $clone = $currentRow.clone(true, true);

					// Reset fields in the clone
					$clone.find('.nubu_woocommerce_section29_month').val('');
					$clone.find('.nubu_dropdown_toggle').text('Select');
					$clone.find('input[type="file"]').val('');
					$clone.find('.nubu_woocommerce_selected_file_name').text('');
					$clone.find('.nubu_woocommerce_selected_file_name').removeClass('uploaded');

					container.append($clone);
					updateRowControls();
				});

				// Event delegation for REMOVE button
				$(document).on('click', '.nubu_woocommerce_remove_section_29_row', function (e) {
					e.preventDefault();
					$(this).closest(sectionSelector).remove();
					updateRowControls();
				});

				// Event delegation for dropdown selection
				$(document).on('click', '.nubu_dropdown_menu li', function () {
					const $li = $(this);
					const value = $li.data('value');
					const $row = $li.closest(sectionSelector);

					$li.siblings().removeClass('selected');
					$li.addClass('selected');

					$row.find('.nubu_dropdown_toggle').text($li.text());
					$row.find('.nubu_woocommerce_section29_month').val(value);

					updateRowControls();
				});

				// Initial call to set up controls
				updateRowControls();
				/*
				$('form').on('submit', function (e) {
					e.preventDefault(); // For testing

					let results = [];

					$(sectionClass).each(function () {
						const month = $(this).find('.nubu_woocommerce_section29_month').val();
						const fileInput = $(this).find('input[type="file"]')[0];
						const file = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

						results.push({
							month: month,
							file: file
						});
					});

					console.log(results); // You can replace this with actual form handling
				});
				*/
				$('form').on('submit', function (e) {
    let isValid = true;

    $('.nubu_woocommerce_section_29_row').each(function () {
        const $row = $(this);
        const month = $row.find('.nubu_woocommerce_section29_month').val();
        const fileInput = $row.find('.nubu_woocommerce_custom_upload')[0];

        if (!month || month === '0' || !fileInput.files || fileInput.files.length === 0) {
            isValid = false;
            return false; // exit each loop
        }
    });

    if (!isValid) {
        e.preventDefault();
        alert('Please select a month and upload a file for every row.');
        return false;
    }
});
			});
    	</script>
	</div>
	<?php
}
add_action( 'woocommerce_account_section-29_endpoint', 'nubu_section_29_endpoint_content' );

/* Save to custom directory */
function handle_licence_upload() {
	/*
	if ( ! is_user_logged_in() || ( ! current_user_can('trade_account') && ! current_user_can('administrator') ) ) {
		wp_safe_redirect( home_url() );
	    exit;
	}
	*/
	
    if ( ! is_user_logged_in() || !is_account_page() || ! isset( $GLOBALS['wp']->query_vars['licence'] )) {
        return;
    }

    if (!isset($_POST['nubu_woocommerce_licence_submit']) || empty($_FILES['nubu_woocommerce_custom_upload']['name'])) {
        //wc_add_notice('Please select a file before submitting.', 'error');
        return;
    }
	
	if ( empty( $_POST['nubu_woocommerce_license_expiry_date'] ) ) {
        wc_add_notice('Please select license expiry date before submitting.', 'error');
        return;
    }

    $user_id = get_current_user_id();
    $file = $_FILES['nubu_woocommerce_custom_upload'];

    // Validate file type
    require_once ABSPATH . 'wp-admin/includes/file.php';
    $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

    if (empty($check['ext']) || !in_array($check['ext'], $allowed)) {
        wc_add_notice('Invalid file type. Only PDF, JPG, and PNG files are allowed.', 'error');
        return;
    }

    // Set custom upload dir
    add_filter('upload_dir', function($dirs) use ($user_id) {
        $custom_subdir = '/user-documents/' . $user_id . '/pharmacy-licence';
        $dirs['subdir'] = $custom_subdir;
        $dirs['path'] = $dirs['basedir'] . $custom_subdir;
        $dirs['url'] = $dirs['baseurl'] . $custom_subdir;
        return $dirs;
    });

    $uploaded = wp_handle_upload($file, ['test_form' => false]);

    remove_all_filters('upload_dir');

    if (!empty($uploaded['error'])) {
        wc_add_notice('Upload failed: ' . $uploaded['error'], 'error');
        return;
    }

    // Save file path in user meta (not attachment ID)
    update_user_meta($user_id, 'pharmacy_licence', $uploaded['file']);
	$date = sanitize_text_field( wp_unslash( $_POST['nubu_woocommerce_license_expiry_date'] ) );
	update_user_meta($user_id, 'pharmacy_licence_expiry', $date);
	
	$user = get_user_by( 'ID', $user_id );
	$to = array( 'hello@thedivicompany.com', 'supply@nubupharma.com', 'hq@nubupharma.com' );
    $subject = 'Pharmacy License Uploaded';
    $message = "Hi! {$user->display_name} {id: $user_id} have just uploaded the pharmacy license.";
    
    // Optional headers
    $headers = array('Content-Type: text/html; charset=UTF-8', 'From: NubuPharma <supply@nubupharma.com>');

    // Send the email
    $sent = wp_mail($to, $subject, $message, $headers);

    wc_add_notice('File uploaded successfully.', 'success');
}
add_action('template_redirect', 'handle_licence_upload');


/* Save to custom directory */
function handle_section_29_upload() {
	/*
	if ( ! is_user_logged_in() || ( ! current_user_can('trade_account') && ! current_user_can('administrator') ) ) {
		wp_safe_redirect( home_url() );
	    exit;
	}
	*/
	
    if ( ! is_user_logged_in() || !is_account_page() || ! isset( $GLOBALS['wp']->query_vars['section-29'] )) {
        return;
    }

    if (!isset($_POST['nubu_woocommerce_section_29_data_submit']) || empty($_FILES['nubu_woocommerce_section_29_upload'])) {
        //wc_add_notice('Please select a file before submitting.', 'error');
        return;
    }

    $user_id = get_current_user_id();
    $months = $_POST['nubu_woocommerce_section29_month'];
    $files  = $_FILES['nubu_woocommerce_section_29_upload'];
	
	/*
	add_filter('upload_dir', function($dirs) use ($user_id) {
        $custom_subdir = '/user-documents/' . $user_id . '/section-29';
        $dirs['subdir'] = $custom_subdir;
        $dirs['path'] = $dirs['basedir'] . $custom_subdir;
        $dirs['url'] = $dirs['baseurl'] . $custom_subdir;
        return $dirs;
    });
	
	
	remove_all_filters('upload_dir');
	*/
	// Paths
    $base_dir = wp_upload_dir()['basedir'] . "/user-documents/{$user_id}/section-29";

    // Ensure base directory exists
    if ( ! file_exists($base_dir) ) {
        mkdir($base_dir, 0755, true);
    }
	
	foreach ( $months as $index => $month ) {
        if ( ! isset( $files['name'][$index] ) || empty( $files['name'][$index] ) ) {
            continue;
        }

        $file_name = $files['name'][$index];
        $tmp_name  = $files['tmp_name'][$index];

        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ( ! in_array($ext, ['csv', 'xls', 'xlsx'] ) ) {
			wc_add_notice('Invalid file type. Only csv, xls and xlsx files are allowed.', 'error');
	        return;
            //continue; // skip invalid file
        }
		
		$month = sanitize_title_with_dashes($month);
		$month_dir = $base_dir . "/{$month}";

        // Skip if folder for user + month already exists
        if ( ! file_exists($month_dir) ) {
            mkdir($month_dir, 0755, true);
        }
		
		/*
		add_filter('upload_dir', function($dirs) use ($user_id) {
			$custom_subdir = '/user-documents/' . $user_id . '/section-29/' . $month;
			$dirs['subdir'] = $custom_subdir;
			$dirs['path'] = $dirs['basedir'] . $custom_subdir;
			$dirs['url'] = $dirs['baseurl'] . $custom_subdir;
			return $dirs;
		});
		*/
		
		//$month_dir = wp_upload_dir()['basedir'] . "/user-documents/{$user_id}/section-29/{$month}";
		$destination = $month_dir . "/" . basename($file_name);
        move_uploaded_file($tmp_name, $destination);

    	//remove_all_filters('upload_dir');
		
		/*
        $month_dir = $base_dir . "/{$month}";

        // Skip if folder for user + month already exists
        if ( ! file_exists($month_dir) ) {
            mkdir($month_dir, 0755, true);
        } else {
            continue; // Don't overwrite existing
        }

        // Save the file
        $destination = $month_dir . "/" . basename($file_name);
        move_uploaded_file($tmp_name, $destination);
		*/
    }
	
	$user = get_user_by( 'ID', $user_id );
	$to = array( 'hello@thedivicompany.com', 'supply@nubupharma.com', 'hq@nubupharma.com' );
    $subject = 'Section 29 Data Uploaded';
    $message = "Hi! {$user->display_name} {id: $user_id} have just uploaded the section 29 data.";
    
    // Optional headers
    $headers = array('Content-Type: text/html; charset=UTF-8', 'From: NubuPharma <supply@nubupharma.com>');

    // Send the email
    $sent = wp_mail($to, $subject, $message, $headers);

    wc_add_notice('File uploaded successfully.', 'success');
}
add_action('template_redirect', 'handle_section_29_upload');

function nubu_download_pharmacy_licence() {
    if (get_query_var('pharmacy_licence') != 1) return;

    if (!is_user_logged_in()) {
        wp_die('Unauthorized');
    }

    $user_id = get_current_user_id();

    // Admins can optionally download any file by ?uid=X
    if (current_user_can('manage_options') && isset($_GET['uid'])) {
        $user_id = intval($_GET['uid']);
    }

    $file_path = get_user_meta($user_id, 'pharmacy_licence', true);

    if (!$file_path || !file_exists($file_path)) {
        wp_die('File not found.');
    }

    $mime_type = mime_content_type($file_path);
	header('Content-Type: ' . $mime_type);
	// Force download
    //header('Content-Description: File Transfer');
    //header('Content-Type: application/octet-stream');
    header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    flush();
    readfile($file_path);
    exit;
}
add_action('template_redirect', 'nubu_download_pharmacy_licence' );

function nubu_download_section_29_data() {
    if (get_query_var('section_29_data') != 1) return;

    if ( ! is_user_logged_in() ) {
        wp_redirect( get_bloginfo('url') );
    }
	
	if ( ! isset( $_GET['user'], $_GET['month'], $_GET['file'] ) ) {
		wp_redirect( get_bloginfo('url') );
	}

    $user_id = get_current_user_id();

    // Admins can optionally download any file by ?uid=X
    if ( current_user_can('manage_options') && isset( $_GET['user'], $_GET['month'], $_GET['file'] ) ) {
        $user_id = intval( $_GET['user'] );
		$month = sanitize_text_field( $_GET['month'] );
		$file_name = sanitize_text_field( $_GET['file'] );
		
		$file_path = WP_CONTENT_DIR . "/uploads/user-documents/{$user_id}/section-29/{$month}/{$file_name}";
		if (!$file_path || !file_exists($file_path)) {
			wp_die('File not found.');
		}
		
		$mime_type = mime_content_type($file_path);
		header('Content-Type: ' . $mime_type);
		// Force download
		//header('Content-Description: File Transfer');
		//header('Content-Type: application/octet-stream');
		header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
		header('Content-Length: ' . filesize($file_path));
		flush();
		readfile($file_path);
	    exit;
    }
}
add_action('template_redirect', 'nubu_download_section_29_data' );

function nubu_reports_endpoint_content( $current_page ) {
	$user_id 	= get_current_user_id();
	$order_args = array(
        'customer_id' 	=> $user_id,
        'return'      	=> 'ids', // return only order IDs to count them
		'limit'			=> -1,
        'status'      	=> array( 'wc-completed', 'wc-processing' ), // adjust as needed
    );
	$filtered_date = get_query_var('date');
	if ( ! empty( $filtered_date ) && 'all' !== $filtered_date ) {
		$order_args['date_created']	= sanitize_text_field( $filtered_date );
	}
	$orders 		= wc_get_orders( $order_args );
    $order_count 	= count( $orders );
    //$order_count = $orders->total;
	
	$total_products 		= 0;
	$order_count 			= 0;
	$average_order_value 	= 0;
	$total_value 			= 0;
	$net_spent 				= 0;
    foreach ( $orders as $order_id ) {
        $order = wc_get_order( $order_id );
        
		/*
        foreach ( $order->get_items() as $item ) {
            $total_products += $item->get_quantity();
        }
		*/
		$total_products += count( $order->get_items() );
		
		$subtotal       = (float) $order->get_subtotal();
    	$discount       = (float) $order->get_total_discount();
		$refunded 		= (float) $order->get_total_refunded();
    	$refunded_tax   = (float) $order->get_total_tax_refunded();
		
		// Including tax
		// $total     		= (float) $order->get_total();
		// $net_spent 		+= $total - $refunded;

		// Excluding Tax
		$total 			=	$subtotal - $discount;
		$net_spent 		+= ($subtotal - $discount) - ($refunded - $refunded_tax);
		$total_value 	+= (float) $total;
        $order_count++;
    }
	if ( $order_count > 0 ) {
        $average_order_value = $total_value / $order_count;
	}
	
	$timezone = new DateTimeZone(wp_timezone_string());
	$lm_start_date 	= ( new DateTime('first day of last month', $timezone) )->format('Y-m-d');
	$lm_last_date	= ( new DateTime('last day of last month', $timezone) )->format('Y-m-d');
	$last_month 	= $lm_start_date . '...' . $lm_last_date;
	
	$cm_start_date 	= ( new DateTime('first day of this month', $timezone) )->format('Y-m-d');
	$cm_last_date	= ( new DateTime('last day of this month', $timezone) )->format('Y-m-d');
	$current_month 	= $cm_start_date . '...' . $cm_last_date;
	
	$cy_start_date 	= ( new DateTime('first day of January this year', $timezone) )->format('Y-m-d');
    $cy_end_date 	= ( new DateTime('last day of December this year', $timezone) )->format('Y-m-d');
	$current_year 	= $cy_start_date . '...' . $cy_end_date;
	
	$ly_start_date 	= ( new DateTime('first day of January last year', $timezone) )->format('Y-m-d');
    $ly_end_date 	= ( new DateTime('last day of December last year', $timezone) )->format('Y-m-d');
	$last_year 		= $ly_start_date . '...' . $ly_end_date;
	
	$ptm_start_date = ( new DateTime('first day of -3 months', $timezone) )->format('Y-m-d');
	$ptm_end_date 	= ( new DateTime('last day of last month', $timezone) )->format('Y-m-d');
	$past_3_months 	= $ptm_start_date . '...' . $ptm_end_date;
	
	$filtered_label = get_query_var('filter');
	$selected_label = ! empty( $filtered_label ) ? esc_html( ucwords( str_replace( '_',' ', $filtered_label ) ) ) : esc_html__( 'All', 'divi' );
    ?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php esc_html_e( 'Reports', 'divi' ); ?></h4>
			<span class="nubu_woocommerce_my_account_endpoint_counter"><?php echo wp_kses_post( sprintf( _n( '%1$s Order', '%1$s Orders', $order_count, 'woocommerce' ), $order_count ) ) ?></span>
		</div>
		<div class="nubu_woocommerce_my_account_endpoint_action">
			<div class="nubu_dropdown nubu_woocommerce_reports_date_filter">
				<div class="nubu_dropdown_toggle"><?php echo $selected_label; ?></div>
				<ul class="nubu_dropdown_menu">
					<li class="nubu_report_date_range" data-value="">
						<span class="nubu_woocommerce_from_date_wrapper">
							<em>From</em>
							<input type="date" class="nubu_woocommerce_report_from_date" />
						</span>
						<span class="nubu_woocommerce_to_date_wrapper">
							<em>To</em>
							<input type="date" class="nubu_woocommerce_report_to_date" />
						</span>
						<span class="nubu_woocommerce_date_range_action"><?php esc_html_e( 'Go', 'divi' ); ?></span>
					</li>
					<li data-value="<?php echo esc_attr( $current_month ); ?>" data-label="this_month"><?php esc_html_e( 'This Month', 'divi' ); ?></li>
					<li data-value="<?php echo esc_attr( $last_month ); ?>" data-label="past_month"><?php esc_html_e( 'Past Month', 'divi' ); ?></li>
					<li data-value="<?php echo esc_attr( $past_3_months ); ?>" data-label="past_3_months"><?php esc_html_e( 'Past 3 Months', 'divi' ); ?></li>
					<li data-value="<?php echo esc_attr( $current_year ); ?>" data-label="this_year"><?php esc_html_e( 'This Year', 'divi' ); ?></li>
					<li data-value="<?php echo esc_attr( $last_year ); ?>" data-label="past_year"><?php esc_html_e( 'Past Year', 'divi' ); ?></li>
					<li data-value="all" data-label="all"><?php esc_html_e( 'All', 'divi' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="nubu_woocommerce_reports_wrapper">
		<div class="nubu_woocommerce_reports_data_wrapper">
			<div class="nubu_woocommerce_report_data_box">
				<div class="nubu_woocommerce_report_data_label">
					<?php esc_html_e( 'Total Orders', 'divi' ); ?>
				</div>
				<div class="nubu_woocommerce_report_data_value">
					<?php echo $order_count; ?>
				</div>
			</div>
			<div class="nubu_woocommerce_report_data_box">
				<div class="nubu_woocommerce_report_data_label">
					<?php esc_html_e( 'Total Products Ordered', 'divi' ); ?>
				</div>
				<div class="nubu_woocommerce_report_data_value">
					<?php echo $total_products; ?>
				</div>
			</div>
			<div class="nubu_woocommerce_report_data_box">
				<div class="nubu_woocommerce_report_data_label">
					<?php esc_html_e( 'Average Order Value', 'divi' ); ?>
				</div>
				<div class="nubu_woocommerce_report_data_value">
					<?php echo wc_price( $average_order_value ); ?>
				</div>
			</div>
			<div class="nubu_woocommerce_report_data_box">
				<div class="nubu_woocommerce_report_data_label">
					<?php esc_html_e( 'Net Spent', 'divi' ); ?>
				</div>
				<div class="nubu_woocommerce_report_data_value">
					<?php echo wc_price( $net_spent ); ?>
				</div>
			</div>
		</div>
		<?php
		if ( $order_count > 0 ) {
		?>
		<div class="nubu_woocommerce_report_orders_wrapper">
			<div class="nubu_woocommerce_report_orders_headers">
				<div class="nubu_woocommerce_report_orders_header"><?php esc_html_e( 'Order Date', 'divi' ); ?></div>
				<div class="nubu_woocommerce_report_orders_header"><?php esc_html_e( 'Order Number', 'divi' ); ?></div>
				<div class="nubu_woocommerce_report_orders_header"><?php esc_html_e( 'Total Items', 'divi' ); ?></div>
				<div class="nubu_woocommerce_report_orders_header"><?php esc_html_e( 'Subtotal', 'divi' ); ?></div>
			</div>
			<div class="nubu_woocommerce_report_orders">
				<?php
				$current_page   = empty( $current_page ) ? 1 : absint( $current_page );
				$order_args = array(
					'customer_id' 	=> $user_id,
					'status'      	=> array( 'wc-completed', 'wc-processing' ), // adjust as needed
					'limit'			=> -1,
					'page'			=> $current_page,
					'paginate'		=> true,
				);
				if ( ! empty( $filtered_date ) && 'all' !== $filtered_date ) {
					$order_args['date_created']	= sanitize_text_field( $filtered_date );
				}
				$orders 		= wc_get_orders( $order_args );
				foreach ( $orders->orders as $order ) {
					//$order = wc_get_order( $order_id );
					$view_order_url = wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) );
					?>
					<div class="nubu_woocommerce_report_order">
						<div class="nubu_woocommerce_report_order_value"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></div>
						<div class="nubu_woocommerce_report_order_value"><a href="<?php echo esc_attr( $view_order_url ); ?>" target="_blank"><?php echo esc_html( '#' . $order->get_order_number() ); ?></a></div>
						<div class="nubu_woocommerce_report_order_value"><?php echo esc_html( count( $order->get_items() ) ); ?></div>
						<div class="nubu_woocommerce_report_order_value"><?php echo wc_price( $order->get_subtotal() ); ?></div>
					</div>
					<?php
				}
				if ( 1 < $orders->max_num_pages ) {
				?>
				<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
				<?php if ( 1 !== $current_page ) { ?>
					<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'reports', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php }
				if ( intval( $orders->max_num_pages ) !== $current_page ) {
			?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'reports', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php } ?>
			</div>
				<?php
				}
				?>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	<?php
}
add_action( 'woocommerce_account_reports_endpoint', 'nubu_reports_endpoint_content' );

/*
function nubu_script_list_endpoint_content( $wishlist_id ) {
	$user_id 	= get_current_user_id();
	$page_title = esc_html__( 'My Script List', 'divi' );
	$action 	= '';
	$counter 	= '';
	if ( $wishlist_id ) {
		$limit 					= 15;
		$args 					= array(
			'wishlist_id' 	=> intval( $wishlist_id ),
			'user_id' 		=> nubu_wishlist()->get( 'current_user' ),
			'limit' 		=> intval( $limit )
		);
		$current_wishlist 		= nubu_wishlist()->get( 'wishlist_data', $args );
		$wishlisted_products 	= nubu_wishlist()->get( 'products', $args );
		$total_products 		= intval( nubu_wishlist()->get( 'products_count', $args ) );
		$total_pages 			= $total_products > 1 ? intval( ceil( $total_products / $limit ) ) : 1;
		$page_title 			= esc_html( $current_wishlist[0]->name );
		if ( $total_products > 0 ) {
			$action 			= '<a href="#" class="nubu_add_all_to_cart">' . esc_html__( 'Add All to Cart', 'divi' ) . '</a>';	
		}
		$counter 				= wp_kses_post( sprintf( _n( '%1$s Product', '%1$s Products', $total_products, 'woocommerce' ), $total_products ) );
	} else {
		if ( nubu_wishlist()->is_multiple_wishlist_enabled() ) {
			$limit 					= 10;
			$args 					= array(
				'user_id' 		=> nubu_wishlist()->get( 'current_user' ),
				'limit' 		=> intval( $limit )
			);
			$wishlists 				= nubu_wishlist()->get( 'wishlists', $args );
			$total_wishlists 		= intval( nubu_wishlist()->get( 'wishlists_count', $args ) );
			$page_title = esc_html__( 'My Script Lists', 'divi' );
			$action = '<div class="nubu_create_new_wishlist_filter"><a href="#" class="nubu_create_new_wishlist">' . sprintf(
				esc_html__( '%s', 'nubu-wishlist' ),
				nubu_wishlist()->get_single_option( 'create_wishlist_text', esc_html__( '+ Create New List', 'nubu-wishlist' ) )
			) .'</a></div>';
			$counter 				= wp_kses_post( sprintf( _n( '%1$s List', '%1$s Lists', $total_wishlists, 'woocommerce' ), $total_wishlists ) );
		} else {
			$limit 					= 15;
			$args 					= array(
				'user_id' 	=> nubu_wishlist()->get( 'current_user' ),
				'limit' 	=> $limit
			);
			$wishlisted_products 	= nubu_wishlist()->get( 'products', $args );
			$total_products 		= nubu_wishlist()->get( 'products_count', $args );
			$total_pages 			= $total_products > 1 ? intval( ceil( $total_products / $limit ) ) : 1;
			if ( $total_products > 0 ) {
				$action 			= '<a href="#" class="nubu_add_all_to_cart">' . esc_html__( 'Add All to Cart', 'divi' ) . '</a>';	
			}
			$counter 				= wp_kses_post( sprintf( _n( '%1$s Product', '%1$s Products', $total_products, 'woocommerce' ), $total_products ) );
		}
		
	}
	?>
	<div class="nubu_woocommerce_my_account_endpoint_header">
		<div class="nubu_woocommerce_my_account_endpoint_heading_wrapper">
			<h4 class="nubu_woocommerce_my_account_endpoint_heading"><?php echo esc_html( $page_title); ?></h4>
			<span class="nubu_woocommerce_my_account_endpoint_counter"><?php echo $counter; ?></span>
		</div>
		<div class="nubu_woocommerce_my_account_endpoint_action nubu_woocommerce_script_list_endpoint_action">
			<?php echo $action; ?>
		</div>
	</div>
	<div class="nubu_woocommerce_script_lists_wrapper">
		<?php
		if ( $wishlist_id ) {
			if ( isset( $wishlisted_products ) && $wishlisted_products && ! is_null( $wishlisted_products ) ) {
				?>
				<div class="nubu_wishlisted_products_wrapper">
					<?php
						nubu_get_wishlisted_products_html( $wishlisted_products );
					?>
				</div>
				<?php
			}
		} else {
			if ( nubu_wishlist()->is_multiple_wishlist_enabled() ) {
				if ( $wishlists && ! is_null( $wishlists ) ) {
				?><div class="nubu_wishlists_wrapper"><?php
					foreach( $wishlists as $wishlist ) {
						$wishlisted_products = nubu_wishlist()->get( 'products', array( 
							'user_id' 		=> nubu_wishlist()->get( 'current_user' ),
							'wishlist_id' 	=> intval( $wishlist->id ),
						) );
						$product_count = 0;
						if ( $wishlisted_products && ! empty( $wishlisted_products ) && ! is_null( $wishlisted_products ) ) {
							$product_count = count( $wishlisted_products );
						}
						$current_url 		= rtrim( get_permalink(), '/' );
						$view_wishlist_url	= "{$current_url}/script-list/" . intval( $wishlist->id );
						?>
						<div class="nubu_wishlist" data-wishlist_id="<?php echo intval( $wishlist->id ); ?>">
							<div class="nubu_wishlist_name_wrapper">
								<div class="nubu_wishlist_label">
									<?php esc_html_e( 'List Name', 'divi' ); ?>
								</div>
								<div class="nubu_wishlist_name_inner_wrap">
									<input type="text" class="nubu_wishlist_name" data-value="<?php echo esc_attr( $wishlist->name ); ?>" value="<?php echo esc_html( $wishlist->name ); ?>" disabled />
									<?php
											//include plugin_dir_path( __DIR__ ) . 'assets/images/check.svg';
											//include plugin_dir_path( __DIR__ ) . 'assets/images/cross.svg';
									?>
								</div>
							</div>
							<div class="nubu_wishlist_product_count_wrapper">
								<div class="nubu_wishlist_label">
									<?php esc_html_e( 'Products', 'divi' ); ?>
								</div>
								<div class="nubu_wishlist_product_count"><?php echo intval( $product_count ); ?></div>
							</div>
							<div class="nubu_wishlist_actions">
								<a href="<?php echo esc_url( $view_wishlist_url ); ?>" class="nubu_view_wishlist"><?php esc_html_e( 'View', 'nubu-wishlist' ); ?></a>
								<a href="javascript:void(0)" class="nubu_edit_wishlist"><?php esc_html_e( 'Edit', 'nubu-wishlist' ); ?></a>
								<?php
										if ( '1' !== $wishlist->is_default ) {
								?>
								<a href="javascript:void(0)" class="nubu_set_default_wishlist"><?php esc_html_e( 'Make default', 'nubu-wishlist' ); ?></a>
								<a href="javascript:void(0)" class="nubu_delete_wishlist" ><?php esc_html_e( 'Delete', 'nubu-wishlist' ); ?></a>
								<?php
										}
								?>
							</div>
						</div>
						<?php
					}
				?></div><?php
				}
				nubu_create_wishlist_popup();
			} else {
				if ( isset( $wishlisted_products ) && $wishlisted_products && ! is_null( $wishlisted_products ) ) {
					?>
					<div class="nubu_wishlisted_products_wrapper">
						<?php
							nubu_get_wishlisted_products_html( $wishlisted_products );
						?>
					</div>
					<?php
				}
			}
		}
		?>
	</div>
	<?php
}
add_action( 'woocommerce_account_script-list_endpoint', 'nubu_script_list_endpoint_content' );

function nubu_get_wishlisted_products_html( $wishlisted_products ) {
	foreach( $wishlisted_products as $wishlisted_product ) {
		add_filter( 'woocommerce_product_add_to_cart_text', 'nubu_woocommerce_product_add_to_cart_text', 10, 2 );
		$_product = wc_get_product( $wishlisted_product->product_id );
		if ( $_product ) {
			$add_to_cart 	= do_shortcode('[add_to_cart id="' . $wishlisted_product->product_id . '" style="" show_price="false"]');
			$stock_status 	= nubu_wishlist()->is_product_in_stock( $_product ) ? '<span class="nubu_panel_product_in_stock">In stock</span>' : '<span class="nubu_panel_product_out_of_stock">Out of stock</span>';
			wp_kses_post(
				printf(
					'<div data-product_id="%8$s" data-wishlist_id="%9$s" class="nubu_wishlisted_product">
							<div class="nubu_wishlisted_product_image"><a href="%3$s" target="_blank">%1$s</a></div>
							<div class="nubu_wishlisted_product_content">
								<h4 class="nubu_wishlisted_product_title"><a href="%3$s" target="_blank">%2$s</a></h4>
								<div class="nubu_wishlisted_product_current_price">%4$s</div>
								<div class="nubu_wishlisted_product_stock_status">%5$s</div>
							</div>
							<div class="nubu_wishlisted_product_actions">
								%6$s
								<a href="#" class="nubu_remove_wishlisted_product">%7$s</a>
							</div>
						</div>',
					wp_kses_post( $_product->get_image( 'woocommerce_thumbnail', array(), true ) ),
					esc_html( get_the_title( $wishlisted_product->product_id ) ),
					esc_url( get_permalink( $wishlisted_product->product_id ) ),
					wp_kses_post( $_product->get_price_html() ),
					wp_kses_post( $stock_status ),
					wp_kses_post( $add_to_cart ),
					esc_html__( 'Delete', 'divi' ),
					intval( $wishlisted_product->product_id ),
					intval( $wishlisted_product->wishlist_id )
				)
			);
		}
		remove_filter( 'woocommerce_product_add_to_cart_text', 'nubu_woocommerce_product_add_to_cart_text', 10, 2 );
	}
}

function nubu_woocommerce_product_add_to_cart_text( $text, $product ) {
	 return $product->is_purchasable() && $product->is_in_stock() ? __( 'Move to Cart', 'woocommerce' ) : __( 'Read more', 'woocommerce' );
}

function nubu_create_wishlist_popup() {
	
	?>
	<div id="nubu_create_wishlist_modal" class="nubu_wishlist_modal">
		<div class="nubu_wishlist_modal_wrapper">
			<div class="nubu_wishlist_modal_content">
				<div class="nubu_create_wishlist_modal_heading_wrapper">
					<h4 class="nubu_create_wishlist_modal_heading"><?php esc_html_e( 'Create Wishlist', 'nubu-wishlist' ); ?></h4>
				</div>
				<div class="nubu_field nubu_create_new_wishlist_wrapper">
					<input id="nubu_wishlist_name" type="text" name="wishlist_name" class="nubu_form_value nubu_textfield" placeholder="<?php echo esc_attr__( 'Enter Your Wishlist Name', 'nubu-wishlist' ); ?>" />
					<span class="nubu_create_wishlist_message"></span>
				</div>
				<a href="#" class="nubu_create_wishlist"><?php esc_html_e( 'Create Wishlist', 'nubu-wishlist' ); ?></a>
			</div>
			<!--<div class="nubu_wishlist_modal_close_icon"><?php echo wp_kses( $close, nubu_wishlist()->get('allowed_html_tags') ); ?></div>-->
		</div>
	</div>
	<?php
}
*/

function nubu_woocommerce_remove_phone_field( $field, $key, $args, $value ) {
	if ( 'billing_phone' === $key ) {
		$field = '';
	}
	if ( 'billing_country' === $key ) {
		$field['label'] = 'Country';
	}
	return $field;
}

function nubu_woocommerce_save_address() {
	if ( ! is_account_page() || ! is_user_logged_in() ) {
		return;	
	}
	
	$user_id 	= get_current_user_id();
	
	if ( isset( $_POST['nubu_woocommerce_save_address'] ) ) {
		$addresses 	= get_user_meta($user_id, '_multiple_addresses', true);
	    $addresses 	= is_array( $addresses ) ? $addresses : [];
		$new_name 	= sanitize_text_field( strtolower( $_POST['billing_address_name'] ) );
		$action 	= sanitize_text_field( $_POST['address_action'] );
		$new = [
			'name'       => $new_name,
			'first_name' => sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
			'last_name'  => sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
			'address_1'  => sanitize_text_field( $_POST['billing_address_1'] ?? '' ),
			'address_2'  => sanitize_text_field( $_POST['billing_address_2'] ?? '' ),
			'city'       => sanitize_text_field( $_POST['billing_city'] ?? '' ),
			'state'      => sanitize_text_field( $_POST['billing_state'] ?? '' ),
			'postcode'   => sanitize_text_field( $_POST['billing_postcode'] ?? '' ),
			'email'  	 => sanitize_text_field( $_POST['billing_email'] ?? '' ),
			'is_default' => false,
		];
	
		$is_duplicate 	= false;
		
		foreach ( $addresses as $id => $existing ) {
			if (
				$id === $new_name &&
				'new_address' === $action
			) {
				$is_duplicate = true;
				break;
			}
		}

		if ( $is_duplicate ) {
			wc_add_notice('You already have an address with that name. Please choose a different name.', 'error');
			return;
		}
		
		if ( 'new_address' === $action ) {
			$new['is_default'] = false;
		} else {
			$new['is_default'] = $addresses[$new_name]['is_default'];
		}

		if ( ! empty( $new_name ) ) {
			$addresses[$new_name] = $new;
		}

		update_user_meta( $user_id, '_multiple_addresses', $addresses );
		wp_redirect( wc_get_account_endpoint_url('edit-address') );
		exit;
	}
	
	 // Delete address
    if ( ! empty( $_GET['delete_address'] ) ) {
		$addresses 	= get_user_meta($user_id, '_multiple_addresses', true);
	    $addresses 	= is_array( $addresses ) ? $addresses : [];
        $id = sanitize_text_field( $_GET['delete_address'] );
        unset( $addresses[$id] );
        update_user_meta( $user_id, '_multiple_addresses', $addresses );
		wp_redirect( wc_get_account_endpoint_url('edit-address') );
		exit;
    }

    // Set default billing
    if ( ! empty( $_GET['set_default_address'] ) ) {
		$addresses 	= get_user_meta($user_id, '_multiple_addresses', true);
	    $addresses 	= is_array( $addresses ) ? $addresses : [];
        $id = sanitize_text_field( $_GET['set_default_address'] );
        if ( isset( $addresses[$id] ) ) {
            $prefix = 'billing_';
            foreach( $addresses[$id] as $key => $value) {
				if ( in_array( $key, array( 'name', 'is_default' ), true ) ) {
					continue;
				}
                update_user_meta( $user_id, $prefix . $key, $value);
            }
			$addresses[$id]['is_default'] = true;
			foreach( $addresses as $key => &$address ) {
				if ( $key !== $id ) {
					$address['is_default'] = false;
				}
			}
			update_user_meta( $user_id, '_multiple_addresses', $addresses );
        }
        wp_redirect( wc_get_account_endpoint_url('edit-address') );
        exit;
    }
}
add_action('template_redirect', 'nubu_woocommerce_save_address');

function nubu_woocommerce_format_address( $address ) {
    $lines = [];
    $lines[] = esc_html( $address['first_name'] ?? '' ) . ' ' . esc_html( $address['last_name'] ?? '' );
    $lines[] = esc_html( $address['address_1'] ?? '' );
	$lines[] = esc_html( $address['address_2'] ?? '' );
    $lines[] = esc_html( $address['city'] ?? '' ) . ', ' . esc_html( $address['postcode'] ?? '' );
	$lines[] = esc_html( $address['state'] ?? '' );
    $lines[] = esc_html( $address['country'] ?? '' );

    return implode( '<br>', array_filter( $lines ) );
}

function nubu_woocommerce_billing_fields( $fields, $country ) {
	unset( $fields['billing_phone'] );
	$fields['billing_country']['label'] = 'Country';
	$fields['billing_country']['priority'] = '90';
	$fields['billing_company'] = array(
		'label'        => __( 'Company name', 'woocommerce' ),
		'class'        => array( 'form-row-wide' ),
		'autocomplete' => 'organization',
		'priority'     => 30,
		'required'     => false
	);
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'nubu_woocommerce_billing_fields', 10, 2 );

function nubu_checkout_address_selector() {
	if ( ! is_user_logged_in() ) {
		return;
	}

    $user_id 	= get_current_user_id();
    $addresses 	= get_user_meta($user_id, '_multiple_addresses', true);
    $addresses 	= is_array($addresses) ? $addresses : [];

    if ( empty($addresses) ) {
		return;
	}

    echo '<div>';
	foreach ($addresses as $key => $addr) {
        $selected = $addr['is_default'] ? 'checked' : '';
        echo '<label class="nubu_address_option">';
        echo '<input type="radio" name="nubu_billing_select_existing" value="' . esc_attr($key) . '" ' . $selected . '> ';
        echo '<span>';
		echo '<strong>' . esc_html($addr['first_name'] . ' ' . $addr['last_name']) . '</strong><br>';
        echo esc_html($addr['address_1']) . ' ' . esc_html($addr['address_2']) . ', ' . esc_html($addr['city'] . ', ' . $addr['postcode']) . '<br>';
        echo '<a class="nubu_edit_address_link" data-address-name="'. esc_attr( $key ) .'" href="' . esc_url(get_permalink(get_option('woocommerce_myaccount_page_id')) . 'edit-address/' . urlencode($key)) . '">Edit Address</a>';
		echo '</span>';
        echo '</label>';
    }
	
	echo '</div>';
	 echo '<div>';
    echo '<a href="#" id="nubu_add_new_address_link">+ Add New Address</a>';
    echo '</div>';
	
	echo '<script>window.checkoutAddresses = ' . wp_json_encode($addresses) . ';</script>';
}
add_action('woocommerce_before_checkout_billing_form', 'nubu_checkout_address_selector');


function nubu_checkout_save_address_checkbox($checkout) {
	echo '<div class="woocommerce-billing-fields__field-wrapper">';
    woocommerce_form_field('billing_save_new_address', [
        'type'    => 'checkbox',
        'class'   => ['form-row-wide'],
		'required'=> false,
        'label'   => 'Save this address to My Addresses',
        'default' => 0,
    ], $checkout->get_value('save_new_address'));
    
	woocommerce_form_field('billing_address_name', [
        'type'    => 'text',
        'class'   => ['form-row-wide'],
		'required'=> false,
        'label'   => 'New Address Name',
        'default' => '',
    ], $checkout->get_value('billing_address_name'));

	woocommerce_form_field('billing_address_action', [
        'type'    => 'hidden',
        'class'   => ['form-row-wide'],
		'required'=> false,
        'default' => '',
    ], $checkout->get_value('billing_address_action'));
    
	echo '</div>';

	
}
add_action('woocommerce_after_checkout_billing_form', 'nubu_checkout_save_address_checkbox');

function nubu_after_checkout_validation($fields, $errors) {
	if ( !is_user_logged_in() ) {
		return;
	}

    $save_new 	= ! empty( $fields['billing_save_new_address'] );
	$name     	= $fields['billing_address_name'] ?? '';
    $key     	= sanitize_text_field( strtolower( $name ) );
    $addresses 	= get_user_meta( get_current_user_id(), '_multiple_addresses', true ) ?: [];

    if ( $save_new ) {
        if ( empty($key) ) {
            $errors->add('missing_address_name', 'Please provide a name for the new address.');
        } elseif ( isset( $addresses[$key] ) ) {
            $errors->add('duplicate_address_name', "An address named '$name' already exists. Please choose another name.");
        }
    }
}
add_action('woocommerce_after_checkout_validation', 'nubu_after_checkout_validation', 10, 2);


add_filter('woocommerce_checkout_update_customer_data', '__return_false');

function nubu_save_new_checkout_address($user_id, $posted) {
    if ( ! is_user_logged_in() ) {
		return;	
	}
	
	$save_new = ! empty( $_POST['billing_save_new_address'] );
    $name     = sanitize_text_field( strtolower( $_POST['billing_address_name'] ?? '' ) );
	//$name 	  = sanitize_text_field( strtolower( $_POST['nubu_billing_select_existing'] ?? '' ) );
	$action   = sanitize_text_field( $_POST['billing_address_action'] );
	
	if ( ! $save_new || ! $name ) {
        return;
    }
	
	$user_id = intval( get_current_user_id() );

	$addresses = get_user_meta( $user_id, '_multiple_addresses', true);
    if ( ! is_array($addresses) ) {
        $addresses = [];
    }
	
    // Avoid duplicates (should also be handled by validation)
    if ( isset( $addresses[$name] ) && 'new_address' == $action ) {
		wc_add_notice( "You already have an address saved as '$name'. Please use a different name.", 'error');
        return; // Don't overwrite an existing one
    }

    $new = [
		'name'	     => $name,
        'first_name' => sanitize_text_field($_POST['billing_first_name']),
        'last_name'  => sanitize_text_field($_POST['billing_last_name']),
        'address_1'  => sanitize_text_field($_POST['billing_address_1']),
		'address_2'  => sanitize_text_field($_POST['billing_address_2']),
        'city'       => sanitize_text_field($_POST['billing_city']),
        'postcode'   => sanitize_text_field($_POST['billing_postcode']),
        'state'    	 => sanitize_text_field($_POST['billing_state']),
		'is_default' => false,
    ];
	
	if ( 'edit_address' == $action ) {
		if ( isset($addresses[$name]) ) {
            $new['is_default'] = $addresses[$name]['is_default'] ?? false;
			$new['email'] = $addresses[$name]['email'] ?? '';
            $addresses[$name] = $new;
        } else {
            wc_add_notice("Address to edit not found.", 'error');
            return;
        }
	} else {
		$addresses[$name] = $new;		
	}

    update_user_meta($user_id, '_multiple_addresses', $addresses);
	
	if ( $addresses[$name]['is_default'] ) {
		$prefix = 'billing_';
		foreach( $addresses[$name] as $key => $value) {
			if ( in_array( $key, array( 'name', 'is_default' ), true ) ) {
				continue;
			}
			update_user_meta( $user_id, $prefix . $key, $value);
		}
	}
}
add_action('woocommerce_checkout_update_user_meta', 'nubu_save_new_checkout_address', 10, 2);
/*
//add_action('woocommerce_checkout_create_order', 'nubu_force_checkout_billing_data', 20, 2);
function nubu_force_checkout_billing_data($order, $data) {
    // Force WooCommerce to use posted billing values, not user meta
    $billing_fields = [
        'first_name', 'last_name', 'company', 'address_1', 'address_2',
        'city', 'state', 'postcode', 'email', 'phone'
    ];

    foreach ($billing_fields as $field) {
        $post_key = 'billing_' . $field;
        if (!empty($_POST[$post_key])) {
            $order->{"set_billing_{$field}"}(sanitize_text_field($_POST[$post_key]));
        }
    }
}
*/
add_action( 'init', 'nubu_maybe_save_default_billing_address' );
function nubu_maybe_save_default_billing_address() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    $user_id = get_current_user_id();

    // Check if user already has billing addresses saved
    $addresses = get_user_meta( $user_id, '_multiple_addresses', true );
    if ( ! empty( $addresses ) && is_array( $addresses ) ) {
        return; // Already has addresses saved
    }
	
	$billing = array();
	
    // Load default billing fields from user meta
    $billing['home'] = [
		'name'		 => 'home',
        'first_name' => get_user_meta( $user_id, 'billing_first_name', true ),
        'last_name'  => get_user_meta( $user_id, 'billing_last_name', true ),
        'company'    => get_user_meta( $user_id, 'billing_company', true ),
        'address_1'  => get_user_meta( $user_id, 'billing_address_1', true ),
        'address_2'  => get_user_meta( $user_id, 'billing_address_2', true ),
        'city'       => get_user_meta( $user_id, 'billing_city', true ),
        'state'      => get_user_meta( $user_id, 'billing_state', true ),
        'postcode'   => get_user_meta( $user_id, 'billing_postcode', true ),
        'phone'      => get_user_meta( $user_id, 'billing_phone', true ),
        'email'      => get_user_meta( $user_id, 'billing_email', true ),
		'is_default' => true
    ];

    // Save as the initial billing_addresses entry
    update_user_meta( $user_id, '_multiple_addresses', $billing );
}
