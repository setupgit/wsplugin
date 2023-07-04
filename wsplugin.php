<?php
/*
Plugin Name: Custom Plugin Updater
Plugin URI: https://example.com
Description: Notifies the user when a new update is available for the custom plugin.
Version: 2.0
Author: Your Name
Author URI: https://example.com
*/


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
    $current_version = '2.0'; // Replace with your current plugin version

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

    $update_url = admin_url('update-core.php');

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
    $current_version = '2.0'; // Replace with your current plugin version

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

