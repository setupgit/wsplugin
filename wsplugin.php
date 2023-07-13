<?php
/*
Plugin Name: Custom Plugin Updater
Plugin URI: https://github.com/setupgit/wsplugin
Description: Notifies the user when a new update is available for the custom plugin.
Version: 3.0
Author: Your Name
Author URI: https://example.com
*/




// Add authorization header with access token
$headers = array(
    'Authorization: Bearer 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'
);

$api_url = 'https://api.github.com/repos/' . GITHUB_USERNAME . '/' . GITHUB_REPOSITORY . '/releases/latest';

// Initialize cURL
$ch = curl_init();

// Set the API URL
curl_setopt($ch, CURLOPT_URL, $api_url);

// Set the authorization header
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Set other cURL options as needed
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Execute the cURL request
$response = curl_exec($ch);

// Check for cURL errors or handle the response

// Close cURL
curl_close($ch);




//*************************************************Update Checker Code Start *************************************************/
/*Make sure to replace 
'your-github-username' and 'your-github-repository' 
with your actual GitHub username and repository name in the code.*/


// Define the plugin slug
define('CUSTOM_PLUGIN_SLUG', 'wsplugin');

// GitHub repository details
define('GITHUB_USERNAME', 'setupgit');
define('GITHUB_REPOSITORY', 'wsplugin');



// Add custom plugin update check
function custom_plugin_check_for_updates()
{
    // Get the stored version of the plugin
    $stored_version = get_option('custom_plugin_version');

    // Current version of the plugin
    $current_version = '3.0'; // Replace with your current plugin version

    // Check if a new update is available
    if (version_compare($current_version, $stored_version, '>')) {
        // New update is available, display a notification
        add_action('admin_notices', 'custom_plugin_display_update_notification');
    }
}
add_action('admin_init', 'custom_plugin_check_for_updates');

// Display update notification
function custom_plugin_display_update_notification()
{
    $plugin_data = get_plugin_data(__FILE__);                                                                                                           
    $plugin_name = $plugin_data['Name'];

    //$update_url = admin_url('update-core.php'); 
    $update_url = 'https://github.com/setupgit/wsplugin/blob/main/work.php';

    $message = sprintf(
        __('A new update is available for %s. Please <a href="%s">update now</a>.', 'text-domain'),
        $plugin_name,
        $update_url
    );

    echo '<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>';
}

// Store the plugin version
function custom_plugin_store_version()
{
    $current_version = '3.0'; // Replace with your current plugin version

    // Store the version of the plugin
    update_option('custom_plugin_version', $current_version);
}
register_activation_hook(__FILE__, 'custom_plugin_store_version');

// Schedule an event to check for updates daily
function custom_plugin_schedule_update_check()
{
    if (!wp_next_scheduled('custom_plugin_check_updates_event')) {
        wp_schedule_event(time(), 'daily', 'custom_plugin_check_updates_event');
    }
}
add_action('wp', 'custom_plugin_schedule_update_check');

// Perform update check when the scheduled event runs
function custom_plugin_check_updates_event()
{
    /*
    $update_data = array(
    'slug' => plugin_basename(__FILE__),
    'new_version' => $latest_version,
    'url' => 'https://github.com/setupgit/wsplugin/releases', // Replace with your GitHub repository's release page URL
    'package' => '',
    );
    */
    $plugin_data = get_plugin_data(__FILE__);
    $current_version = $plugin_data['Version'];

    $github_api_url = "https://api.github.com/repos/" . GITHUB_USERNAME . "/" . GITHUB_REPOSITORY . "/releases/latest";

    $response = wp_remote_get($github_api_url);
    if (is_wp_error($response)) {
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);

    if ($data && isset($data->tag_name)) {
        $latest_version = $data->tag_name;

        if (version_compare($latest_version, $current_version, '>')) {
            update_option('custom_plugin_version', $latest_version);
        }
    }
}
add_action('custom_plugin_check_updates_event', 'custom_plugin_check_updates_event');
//*************************************************Update Checker Code End *************************************************/



