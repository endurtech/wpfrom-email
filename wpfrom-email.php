<?php
/* --------------------------------------------------
Plugin Name: WPFrom Email
Plugin URI: https://endurtech.com/wpfrom-wordpress-plugin-change-default-from-email-and-name/
Description: Replaces the default WordPress email FROM Name and Email: WordPress &lt;wordpress@yourdomain.com&gt;
Author: Manny Rodrigues
Author URI: https://endurtech.com
Requires PHP: 7.0
Requires WP: 5.0+
Tested to: 5.2.2
Version: 1.4
License: GPLv2 or later
Domain Path: languages
Text Domain: wpfrom-emails
-------------------------------------------------- */

if ( ! defined( 'ABSPATH' ) ) {
  exit(); // Exit if accessed directly.
}

define( 'WPF_TITLE', 'WPFrom Email' ); // Title
define( 'WPF_SETTINGS', 'WPFrom Email Settings' ); // Settings page title 

/*
* Load plugin textdomain.
*
* @since 1.0
add_action( 'init', 'wpfrom_mail_load_textdomain' );
function wpfrom_mail_load_textdomain()
{
  load_plugin_textdomain( 'wpfrom-mail', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
*/

// Add link to plugin settings from "Settings" menu
add_action( 'admin_menu', 'wpfrom_mail_sender_menu' );
function wpfrom_mail_sender_menu()
{
	add_options_page( __(WPF_SETTINGS), __(WPF_TITLE), 'manage_options', 'wpfrom-email', 'wpfrom_mail_sender_output' );
}

// Add link to plugin settings from "Plugins" page
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'wpfrom_email_settings_link' );
function wpfrom_email_settings_link( $links )
{
	$links[] = '<a href="' . admin_url( 'options-general.php?page=wpfrom-email' ) . '">' . __('Settings') . '</a>';
	return $links;
}

// Add notice to admin dashboard of disabled emails
add_filter( 'dashboard_glance_items', 'dashboardStatus' );
function dashboardStatus( $glances )
{
  $email_killer_init = get_option( 'wpfrom_kill_email_id' );
  if( $email_killer_init == '1' ) {
    $glances[] = '<li style="float:none;"><i class="dashicons dashicons-email" aria-hidden="true" style="color:#e14d43;"></i> WordPress Emails <strong>Disabled</strong></li>';
	}
	return $glances;
}

// WPFrom Plugin Deactivation Database Cleanup, You're welcome!
register_deactivation_hook( __FILE__, 'wpfrom_deactivation_cleaner' );
function wpfrom_deactivation_cleaner()
{
  delete_option( 'wpfrom_custom_sender_id' );
  delete_option( 'wpfrom_mail_sender_email_id' );
  delete_option( 'wpfrom_mail_sender_id' ); // since 1.0, now junk
  delete_option( 'wpfrom_mail_sender_name_id' );
  delete_option( 'wpfrom_kill_email_id' );
}

// WordPress plugin registration
add_action( 'admin_init', 'wpfrom_mail_sender_register' );
function wpfrom_mail_sender_register()
{
  // Settings Section
  add_settings_section( 'wpfrom_mail_sender_section', WPF_SETTINGS, 'wpfrom_mail_sender_text', 'wpfrom_mail_sender' );
  // Enable WordPress Custom Emails
  add_settings_field( 'wpfrom_custom_sender_id', __('WPFrom Custom Sender', 'wpfrom-mail'), 'wpfrom_custom_sender', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_custom_sender_id' );
  // Custom WordPress Sender Email
	add_settings_field( 'wpfrom_mail_sender_email_id', __('Custom Senders Email', 'wpfrom-mail'), 'wpfrom_mail_sender_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_email_id' );
  // Custom WordPress Sender Name
	add_settings_field( 'wpfrom_mail_sender_name_id', __('Custom Senders Name', 'wpfrom-mail'), 'wpfrom_mail_sender_name', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_name_id' );
  // Disable WordPress Emails ALL
  add_settings_field( 'wpfrom_kill_email_id', __('WordPress Emails', 'wpfrom-mail'), 'wpfrom_mail_killer', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_kill_email_id' );
}

// Enable WPFrom Custom Sender checkbox
function wpfrom_custom_sender()
{
  $custom_email = get_option( 'wpfrom_custom_sender_id' );
  if( ! isset ( $custom_email ) || $custom_email == '' ) {
    delete_option( 'wpfrom_custom_sender_id' );
  } else {
    $custom_email = 'checked="checked" ';
  }
  echo '<input name="wpfrom_custom_sender_id" id="wpfrom_custom_sender_id" type="checkbox" value="1" '.$custom_email.'/> <label for="wpfrom_custom_sender_id">Enable Custom WordPress Emails?</em></label>';
}

// FROM Email field
function wpfrom_mail_sender_email()
{
  $sender_email = get_option( 'wpfrom_mail_sender_email_id' );
  if( $sender_email == '' ) {
    delete_option( 'wpfrom_mail_sender_email_id' );
  }
  echo '<input name="wpfrom_mail_sender_email_id" id="wpfrom_mail_sender_email_id" type="email" placeholder="wordpress@mywebsite.com" class="regular-text" value="' . $sender_email . '" />';
}

// FROM Name field
function wpfrom_mail_sender_name()
{
  $sender_name = get_option( 'wpfrom_mail_sender_name_id' );
  if( $sender_name == '' ) {
    delete_option( 'wpfrom_mail_sender_name_id' );
  }
  echo '<input name="wpfrom_mail_sender_name_id" id="wpfrom_mail_sender_name_id" type="text" placeholder="WordPress" class="regular-text" value="' . $sender_name . '" />';
}

// Disable WordPress Emails checkbox
function wpfrom_mail_killer()
{
  $email_killer = get_option( 'wpfrom_kill_email_id' );
  if( ! isset ( $email_killer ) || $email_killer == '' ) {
    delete_option( 'wpfrom_kill_email_id' );
  } else {
    $email_killer = 'checked="checked" ';
  }
  echo '<input name="wpfrom_kill_email_id" id="wpfrom_kill_email_id" type="checkbox" value="1" '.$email_killer.'/> <label for="wpfrom_kill_email_id">Disable ALL WordPress Emails? Kills wp_mail(), <em>slowly.</em></label>';
}

// Page description
function wpfrom_mail_sender_text()
{
  echo '<p>Replaces the default WordPress email FROM <strong>Name</strong> and <strong>Email</strong>: <strong>WordPress &lt;wordpress@yourdomain.com&gt;</strong></p>
  <p>Enable and set your FROM Email (<em>required</em>) and Name. To aid email delivery, use a real address on your server.</p>
  <p>Checking "Disable" option will disable ALL emails. <strong>WARNING!</strong> Fully test it on your website to confirm operation.</p>
  <p>Did <a href="https://endurtech.com/wpfrom-wordpress-plugin-to-change-the-default-from-email-and-name/" target="_blank" title="Opens New Window">our plugin</a> save you time and add value? <a href="https://paypal.me/endurtechnology" target="_blank" title="Opens New Window"><strong>Share your appreciation</strong></a> while supporting future improvements.</p>';
}

// Settings form
function wpfrom_mail_sender_output()
{
  echo '<div class="wrap">
    <form method="post" action="options.php">';
      do_settings_sections( 'wpfrom_mail_sender' );
      settings_fields( 'wpfrom_mail_sender_section' );
      submit_button();
  echo '</form>
  </div>';
}

// Replace default WordPress emails
add_filter( 'wp_mail', 'wpfrom_custom_sender_init' );
function wpfrom_custom_sender_init()
{
  $custom_sender_init = get_option( 'wpfrom_custom_sender_id' );
  if( $custom_sender_init == '1' ) {
    add_filter( 'wp_mail_from', 'wpfrom_new_mail_from' );
    add_filter( 'wp_mail_from_name', 'wpfrom_new_mail_from_name' );
  }
}

// Replace the default FROM Email: wordpress@yourdomain.com
function wpfrom_new_mail_from()
{
  return get_option( 'wpfrom_mail_sender_email_id' );  
}

// Replace the default FROM Name: WordPress
function wpfrom_new_mail_from_name()
{
  return get_option( 'wpfrom_mail_sender_name_id' );
}

// Disable all WordPress email. Hook into phpmailer class, ClearAllRecipients.
// Thanks: https://wordpress.stackexchange.com/users/111056/kirillrocks
add_filter( 'wp_mail', 'wpfrom_email_killer' );
function wpfrom_email_killer( $args )
{
  $email_killer_init = get_option( 'wpfrom_kill_email_id' );
  if( $email_killer_init == '1' ) {
    add_action( 'phpmailer_init', 'kickout_recipients' );
  }
}
function kickout_recipients( $phpmailer )
{
  $phpmailer->ClearAllRecipients();
}