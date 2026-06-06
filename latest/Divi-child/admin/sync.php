<?php
require_once __DIR__ . '/../unleashed/api.php';
require_once __DIR__ . '/../unleashed/sync.php';

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'perform_sync') {
        $unleashedApi = new UnleashedApi();
        $sync_process = new SyncProcess(
            $unleashedApi->get_products(), 
            $unleashedApi->get_product_prices(), 
            $unleashedApi->get_stock()
        );
        $sync_process->run();
    }
    
    /*
    if ($action == 'bulk_email_users') {
        $users = get_users();
        foreach ($users as $user) {
            $user_data = get_userdata($user->ID);
            if ($user_data) {
                $user_login = $user_data->user_login;
                $user_email = $user_data->user_email;

                if($user_login != "admin" && $user_email != "carl+localAug@digitalmates.co.nz" && $user_email != "will.douglas@nubupharma.com"){
                    $key = get_password_reset_key($user_data);
        
                    if (!is_wp_error($key)) {
                        // Generate the reset password URL
                        $reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
                        
                        // Compose the email
                        $subject = __('NUBU Healthcare Professional Education Portal New Password Request');
                        $message = "NUBU has today launched its new website and Healthcare Professional education portal. You can check out the new website here: www.nubupharma.com.\n\n";
                        $message .= "Our records show you’re a previous user of our portal. To access the new portal which includes a range of educational resources & product information, you will need to create a new password for the following account:\n";
                        $message .= sprintf(__('Username: %s'), $user_login) . "\n\n";
                        $message .= "If this was a mistake, just ignore this email and nothing will happen.\n\n";
                        $message .= "To reset your password, visit the following address:\n\n";
                        $message .= '<' . $reset_url .">\n\n";
                        $message .= "Please reach out to the NUBU team if you have any questions regarding this at info@nubupharma.com\n\nKind regards\nNUBU";
                        
                        // Send email
                        wp_mail($user_email, $subject, $message);
                    }
                }
            }
        }

    }

    if($action == "bulk_import_users"){
        $url = $_POST['url_to_csv'];
        // Fetch the content from the URL
        $response = wp_remote_get($url);

        // Check if the request was successful
        if (is_wp_error($response)) {
            echo "Failed to fetch the CSV. Error: " . $response->get_error_message();
            exit;
        }
        // Get CSV data from the response
        $csv_data = wp_remote_retrieve_body($response);
        // Convert CSV data into an array of lines
        $lines = explode("\n", $csv_data);
        // Remove the header line
        array_shift($lines);


        $default_catalog = carbon_get_theme_option('default_catalog');
        
        // Loop through each line
        foreach ($lines as $line) {
            // Parse CSV line into an array
            $data = str_getcsv($line, ",");
            
            if (count($data) >= 3) {  // Ensure there are enough columns in the line
                // Map CSV columns to variables
                $firstName = $data[0];
                $lastName  = $data[1];
                $email     = $data[2];

                // Check if user already exists
                if (!email_exists($email)) {
                    // Create a new user without specifying a password
                    wp_create_user($email, null, $email);

                    // Update the user's first and last names
                    $user_id = email_exists($email);
                    if ($user_id) {
                        wp_update_user([
                            'ID' => $user_id,
                            'first_name' => $firstName,
                            'last_name' => $lastName
                        ]);
                    }

                    if($default_catalog && count($default_catalog) > 0){
                        foreach($default_catalog[0] as $key => $value){
                            update_user_meta($user_id, "_hcp_catalog|||0|$key",$value);
                        }
                    }
                }
            }
        }
    }
    */
}


?>
<div class="wrap">
    <h1>Manually Trigger Sync</h1>
    <form method="post" action="">
        <p>This will force a sync from unleashed</p>
        <button type="submit" name="action" value="perform_sync" class="button button-primary">Perform Sync</button>
        <?php 
        /*
        <p>This will send a retrieve password to all users of the site.</p>
        <button type="submit" name="action" value="bulk_email_users" class="button button-primary">Bulk Email Users</button>
        
        <p>This will import users, the structure must be titled columns; firstName, lastName, email</p>
        <input type="text" name="url_to_csv" placeholder="URL to CSV" />
        <button type="submit" name="action" value="bulk_import_users" class="button button-primary">Bulk Create Users</button>
        */ 
        ?>
    </form>
</div>

