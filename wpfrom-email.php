<?php
/*
Plugin Name: WPFrom Email
Plugin URI: https://endurtech.com/wpfrom-wordpress-plugin-change-default-from-email-and-name/
Description: Easily change the default 'from' wordpress@ email address and name.
Author: Manny Rodrigues
Author URI: https://endurtech.com
Donate link: https://endurtech.com/give-thanks/
Version: 1.0
Requires at least: 5.0
Tested up to: 5.2.2
Requires PHP: 7.0
Domain Path: languages
Text Domain: wpfrom-emails
License: GPLv2 or later
*/

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

// WordPress plugin registration
add_action( 'admin_init', 'wpfrom_mail_sender_register' );
function wpfrom_mail_sender_register()
{
	add_settings_section( 'wpfrom_mail_sender_section', WPF_SETTINGS, 'wpfrom_mail_sender_text', 'wpfrom_mail_sender' );

	add_settings_field( 'wpfrom_mail_sender_email_id', __('WordPress Sender\'s Email', 'wpfrom-mail'), 'wpfrom_mail_sender_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );

	add_settings_field( 'wpfrom_mail_sender_id', __('WordPress Sender\'s Name', 'wpfrom-mail'), 'wpfrom_mail_sender_function', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
	register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_id' );

	register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_email_id' );
}

// Page description
function wpfrom_mail_sender_text()
{
  echo '<p>This plugin will update the default WordPress sender name and email address, <strong>WordPress < wordpress@ ></strong></p>
  <p>To ensure delivery, use an email address that exists and associated with your present website domain.</p>
  <p>Clear these fields and resave this form to return WordPress to its default email sending settings.</p>';
}

// Email field
function wpfrom_mail_sender_email()
{
  $sender_email = get_option( 'wpfrom_mail_sender_email_id' );
  if( $sender_email == '' ) {
    delete_option( 'wpfrom_mail_sender_email_id' );
  }
  echo '<input name="wpfrom_mail_sender_email_id" id="wpfrom_mail_sender_email_id" type="email" placeholder="wordpress@mywebsite.com" class="regular-text" value="' . $sender_email . '" />';
}

// From field
function wpfrom_mail_sender_function()
{
  $sender_name = get_option( 'wpfrom_mail_sender_id' );
  if( $sender_name == '' ) {
    delete_option( 'wpfrom_mail_sender_id' );
  }
  echo '<input name="wpfrom_mail_sender_id" id="wpfrom_mail_sender_id" type="text" placeholder="WordPress" class="regular-text" value="' . $sender_name . '" />';
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
  // Donate
  echo '<div style="padding:50px;">
  <p>Did this Plugin Add Value to You? <a href="https://paypal.me/endurtechnology" target="_blank" title="Opens New Window"><strong>Share Your Appreciation</strong></a>!</p>
  </div>';
}

// Add plugin settings page link under Settings menu
add_action( 'admin_menu', 'wpfrom_mail_sender_menu' );
function wpfrom_mail_sender_menu()
{
	add_options_page( __(WPF_SETTINGS), __(WPF_TITLE), 'manage_options', 'wpfrom-email', 'wpfrom_mail_sender_output' );
}

// Add link to plugin settings from Plugins page
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'wpfrom_email_settings_link' );
function wpfrom_email_settings_link( $links )
{
	$links[] = '<a href="' . admin_url( 'options-general.php?page=wpfrom-email' ) . '">' . __('Settings') . '</a>';
	return $links;
}

// Replace the default from wordpress@ email address
add_filter( 'wp_mail_from', 'wpfrom_new_mail_from' );
function wpfrom_new_mail_from( $old )
{
  return get_option( 'wpfrom_mail_sender_email_id' );  
}

// Replace the default from wordpress@ name
add_filter( 'wp_mail_from_name', 'wpfrom_new_mail_from_name' );
function wpfrom_new_mail_from_name( $old )
{
  return get_option( 'wpfrom_mail_sender_id' );
}