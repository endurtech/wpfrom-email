<?php
/* --------------------------------------------------
Plugin Name: WPFrom Email
Plugin URI: https://endurtech.com/wpfrom-email-wordpress-plugin/
Description: Replaces default WordPress sender FROM Name and Email Address. NEW admin email options.
Author: Manny Rodrigues
Author URI: https://endurtech.com
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 5.0
Tested up to: 5.3
Version: 1.7
Text Domain: wpfrom-emails
Domain Path: /locale

TODOS:
=====================================================
- Use one database record to obtain/set values.
- Option: Disable 'E-mail address change' user email notification.
- Add different languages.

x Option: Disable 'New User' admin email notification.
---- ---- It does effect acount activation emails. These notifications must be manually created within Gravity Forms.
---- Not sure if this will effect account activation emails.
x Option: Disable 'New User' user email notification.
---- Don't disable if you provide credentials upon registration.

NOTEPAD:
=====================================================
// Prevent admin email notification for new registered users or user password changes
add_action( 'phpmailer_init', 'conditional_mail_stop' );
function conditional_mail_stop()
{
  global $phpmailer;
  $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
  $subject = array(
    sprintf( __( '[%s] New User Registration' ), $blogname ),
    sprintf( __( '[%s] Password Lost/Changed' ), $blogname )
  );
  if ( in_array( $phpmailer->Subject, $subject ) )
  {
    // empty $phpmailer class -> email cannot be sent
    $phpmailer = new PHPMailer( true );
  }
}

// Display admin backend warning that emails are being stopped
// Working. Replace existing wpfrom_dashboard_disabled_notice if users prefer this more apparent notice over the small dashboard_glance_items notice.
add_action( 'admin_notices', 'wpfrom_backend_warning' );
function wpfrom_backend_warning()
{
  echo '<div class="error"><p><strong>Emails Disabled:</strong> The FROM email is missing within the WPFrom Email plugin settings. This disables all emails from your web site. To fix, disable the plugin or provide an email address.</p></div>';
}
-------------------------------------------------- */

if( ! defined( 'ABSPATH' ) )
{
  exit(); // No direct access
}

define( 'WPF_TITLE', 'WPFrom Email' ); // Title
define( 'WPF_SETTINGS', 'WPFrom Email Settings' ); // Settings page title 

/*
* Load plugin textdomain
*
* @since 1.0

add_action( 'init', 'wpfrom_mail_load_textdomain' );
function wpfrom_mail_load_textdomain()
{
  load_plugin_textdomain( 'wpfrom-mail', false, basename( dirname( __FILE__ ) ) . '/locale' );
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
add_filter( 'dashboard_glance_items', 'wpfrom_dashboard_disabled_notice' );
function wpfrom_dashboard_disabled_notice( $glances )
{
  $email_killed_init = get_option( 'wpfrom_mail_sender_email_id' );
  $custom_email = get_option( 'wpfrom_custom_sender_id' );
  if( $email_killed_init == '' && $custom_email == '1' )
  {
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
  delete_option( 'wpfrom_mail_sender_name_id' );
  delete_option( 'wpfrom_pwd_admin_email_id' );
  delete_option( 'wpfrom_new_user_admin_email_id' );
  delete_option( 'wpfrom_pwd_user_email_id' );
  delete_option( 'wpfrom_mail_sender_id' ); // since 1.0, now junk
  delete_option( 'wpfrom_kill_email_id' ); // since 1.4.2, now junk
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
  // Disable WordPress "Password Rest" admin email
  add_settings_field( 'wpfrom_pwd_admin_email_id', __('User Password Reset', 'wpfrom-mail'), 'wpfrom_pwd_admin_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_pwd_admin_email_id' );
  // Disable WordPress "New User Registration" admin email
  add_settings_field( 'wpfrom_new_user_admin_email_id', __('New User Registration', 'wpfrom-mail'), 'wpfrom_new_user_admin_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_new_user_admin_email_id' );
  // Disable WordPress "Password Changed" user email
  add_settings_field( 'wpfrom_pwd_user_email_id', __('User Password Changed', 'wpfrom-mail'), 'wpfrom_pwd_user_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_pwd_user_email_id' );
}

// Enable WPFrom Custom Sender checkbox
function wpfrom_custom_sender()
{
  $custom_email = get_option( 'wpfrom_custom_sender_id' );
  if( ! isset ( $custom_email ) || $custom_email == '' )
  {
    delete_option( 'wpfrom_custom_sender_id' );
  }
  else
  {
    $custom_email = 'checked="checked" ';
  }
  echo '<input name="wpfrom_custom_sender_id" id="wpfrom_custom_sender_id" type="checkbox" value="1" '.$custom_email.'/> <label for="wpfrom_custom_sender_id">Enable custom WordPress emails?</em></label>';
}

// FROM Email field
function wpfrom_mail_sender_email()
{
  $sender_email = get_option( 'wpfrom_mail_sender_email_id' );
  if( $sender_email == '' )
  {
    delete_option( 'wpfrom_mail_sender_email_id' );
  }
  echo '<input name="wpfrom_mail_sender_email_id" id="wpfrom_mail_sender_email_id" type="email" placeholder="wordpress@yourdomain.com" class="regular-text" value="' . $sender_email . '" /><br /><span style="font-size:12px; padding-left:10px;">Want to Disable ALL WordPress emails? Leave this blank!</span>';
}

// FROM Name field
function wpfrom_mail_sender_name()
{
  $sender_name = get_option( 'wpfrom_mail_sender_name_id' );
  if( $sender_name == '' )
  {
    delete_option( 'wpfrom_mail_sender_name_id' );
  }
  echo '<input name="wpfrom_mail_sender_name_id" id="wpfrom_mail_sender_name_id" type="text" placeholder="WordPress" class="regular-text" value="' . $sender_name . '" /><br /><span style="font-size:12px; padding-left:10px;">Do not use commas and/or other special characters.</span>';
}

// Disable WordPress "Password Rest" admin email checkbox
function wpfrom_pwd_admin_email()
{
  $pwd_admin_email = get_option( 'wpfrom_pwd_admin_email_id' );
  if( ! isset ( $pwd_admin_email ) || $pwd_admin_email == '' )
  {
    delete_option( 'wpfrom_pwd_admin_email_id' );
  }
  else
  {
    $pwd_admin_email = 'checked="checked" ';
  }
  echo '<input name="wpfrom_pwd_admin_email_id" id="wpfrom_pwd_admin_email_id" type="checkbox" value="1" '.$pwd_admin_email.'/> <label for="wpfrom_pwd_admin_email_id">Disable <strong>Admin email notice</strong> upon user password reset?</em></label>';
}

// Disable WordPress "New User Registration" admin email checkbox
function wpfrom_new_user_admin_email()
{
  $new_user_admin_email = get_option( 'wpfrom_new_user_admin_email_id' );
  if( ! isset ( $new_user_admin_email ) || $new_user_admin_email == '' )
  {
    delete_option( 'wpfrom_new_user_admin_email_id' );
  }
  else
  {
    $new_user_admin_email = 'checked="checked" ';
  }
  echo '<input name="wpfrom_new_user_admin_email_id" id="wpfrom_new_user_admin_email_id" type="checkbox" value="1" '.$new_user_admin_email.'/> <label for="wpfrom_new_user_admin_email_id">Disable <strong>Admin email notice</strong> upon "New User Registration"?</em></label><br /><span style="font-size:12px; padding-left:10px;">Only for use with Gravity Forms User Registration Add-On. <a href="https://endurtech.com/wpfrom-email-wordpress-plugin/#using-wpfrom-email-wordpress-plugin" target="_blank" title="Opens in New Window">Read more</a></span>';
}

// Disable WordPress "Password Changed" user email checkbox
function wpfrom_pwd_user_email()
{
  $pwd_user_email = get_option( 'wpfrom_pwd_user_email_id' );
  if( ! isset ( $pwd_user_email ) || $pwd_user_email == '' )
  {
    delete_option( 'wpfrom_pwd_user_email_id' );
  }
  else
  {
    $pwd_user_email = 'checked="checked" ';
  }
  echo '<input name="wpfrom_pwd_user_email_id" id="wpfrom_pwd_user_email_id" type="checkbox" value="1" '.$pwd_user_email.'/> <label for="wpfrom_pwd_user_email_id">Disable <strong>User email notice</strong> upon user password change?</em></label>';
}

// Page description
function wpfrom_mail_sender_text()
{
  echo '<p>Replaces the default WordPress FROM <strong>Email</strong> and <strong>Name</strong>. Enable and set Senders Email (<strong><em>otherwise all mail is disabled</em></strong>).</p>
  <p>Did <a href="https://wordpress.org/plugins/wpfrom-email/" target="_blank" title="Opens New Window">this plugin</a> save you time and add value? <a href="https://endurtech.com/give-thanks/" target="_blank" title="Opens New Window"><strong>Share your appreciation</strong></a> and support future improvements.</p>';
}

// Settings form
function wpfrom_mail_sender_output()
{
  echo '<div style="padding:20px 0px 20px 20px;">
    <img src="' . plugins_url( 'wpfrom-logo.png', __FILE__ ) . '" style="max-width:80px; width:100%; height:auto; float:left;" />
    <h1 style="float:left; padding:20px 0px 0px 20px;">' . WPF_TITLE . '</h1>
    <div style="clear:both;"></div>
  </div>
  <div class="wrap" style="padding:20px; border-top:1px solid #cccccc; border-right:1px solid #cccccc; border-bottom:2px solid #cccccc; border-left:2px solid #cccccc; border-radius:10px; background:#ffffff;">
    <form method="post" action="options.php">';
      settings_fields( 'wpfrom_mail_sender_section' );
      do_settings_sections( 'wpfrom_mail_sender' );
      submit_button();
  echo '</form>
  </div>';
}

// Replace default WordPress emails with Custom Sender
add_filter( 'wp_mail', 'wpfrom_custom_sender_init' );
function wpfrom_custom_sender_init()
{
  $custom_sender_init = get_option( 'wpfrom_custom_sender_id' );
  if( $custom_sender_init == '1' )
  {
    // Replace the default FROM Email: wordpress@yourdomain.com
    add_filter( 'wp_mail_from', 'wpfrom_new_mail_from' );
    if( ! function_exists( 'wpfrom_new_mail_from' ) )
    {
      function wpfrom_new_mail_from()
      {
        return get_option( 'wpfrom_mail_sender_email_id' );
      }
    }
    // Replace the default FROM Name: WordPress
    add_filter( 'wp_mail_from_name', 'wpfrom_new_mail_from_name' );
    if( ! function_exists( 'wpfrom_new_mail_from_name' ) )
    {
      function wpfrom_new_mail_from_name()
      {
        return get_option( 'wpfrom_mail_sender_name_id' );
      }
    }
  }
}

// Disable WordPress "Password Rest" admin email
$pwd_admin_email_init = get_option( 'wpfrom_pwd_admin_email_id' );
if( $pwd_admin_email_init == '1' )
{
  if( ! function_exists( 'wp_password_change_notification' ) )
  {
    function wp_password_change_notification( $user )
    {
      return;
    }
  }
}

// Disable WordPress "New User Registration" admin email
$new_user_admin_email_init = get_option( 'wpfrom_new_user_admin_email_id' );
if( $new_user_admin_email_init == '1' )
{
  /*
  * Disable Gravity Forms "New User Registration" notification emails
  * There are two email notifications:
  *   1. user notification  - email the user receives upon registration with link to set their password.
  *   2. admin notification - email the site admin receives upon new user registration: "New User Registration"
  * This function disables BOTH!
  * You won't be able to use the option to email the new user their autogenerated password since that email is now disabled.
  * Rather, you will have to create a Gravity Forms notification within your form to send out the user password setup email.
  */
  if( ! function_exists( 'gf_new_user_notification' ) )
  {
    function gf_new_user_notification( $user_id, $plaintext_pass = '', $notify = '' )
    {
      return;
    }
  }
}

// Disable WordPress "Password Changed" user email
$pwd_user_email_init = get_option( 'wpfrom_pwd_user_email_id' );
if( $pwd_user_email_init == '1' )
{
  add_filter( 'send_password_change_email', '__return_false' );
}