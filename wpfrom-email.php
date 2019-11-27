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
Version: 1.6.2
Text Domain: wpfrom-emails
Domain Path: /locale

TODOS:
- Use one database table to obtain/set values.
- Option: Disable 'New User' admin email notification.
---- Not sure if this will effect account activation emails.
x Option: Disable 'New User' user email notification.
---- Don't disable if you provide credentials upon registration.
- Option: Disable 'Password changed' admin email notification.
x Option: Disable 'Notify postauthor' email.
x Option: Disable 'Notify moderator' email.
- Option: Disable 'E-mail address change' user email notification.
- Add different languages.
COMPLETED:
- Disable 'Password reset' admin email notification.
- Disable 'Password changed' user email notification.
NOTEPAD:
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
// Gravity Forms User Registration Add-on and Custom Notifications?
// This will Disable the Default WordPress Admin and User Notifications.
if ( ! function_exists( 'gf_new_user_notification' ) )
{
  function gf_new_user_notification( $user_id, $plaintext_pass = '', $notify = '' )
  {
    return;
  }
}
// Display admin backend warning that emails are being stopped
add_action( 'admin_notices', array( $this, 'wpfrom_backend_warning' ) );
function wpfrom_backend_warning()
{
		echo "\n<div class='error'><p><strong>' . esc_html_e( 'Emails Disabled', 'wpfrom-email' ) . ':</strong> ';
      esc_html_e( 'The FROM email is missing within the WPFrom Email plugin settings. This disables all emails from your web site. To fix, disable the plugin or provide an email address.', 'wpfrom-email' );
		echo '</p></div>';
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
}*/

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
  delete_option( 'wpfrom_mail_sender_id' ); // since 1.0, now junk
  delete_option( 'wpfrom_mail_sender_name_id' );
  delete_option( 'wpfrom_kill_email_id' ); // since 1.4.2, now junk
  delete_option( 'wpfrom_pwd_admin_email_id' );
  delete_option( 'wpfrom_pwd_user_email_id' );
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

// Replace default WordPress emails
add_filter( 'wp_mail', 'wpfrom_custom_sender_init' );
function wpfrom_custom_sender_init()
{
  $custom_sender_init = get_option( 'wpfrom_custom_sender_id' );
  if( $custom_sender_init == '1' )
  {
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

// Disable WordPress "Password Rest" admin email
$pwd_admin_email_init = get_option( 'wpfrom_pwd_admin_email_id' );
if( $pwd_admin_email_init == '1' )
{
  if ( ! function_exists( 'wp_password_change_notification' ) )
  {
    function wp_password_change_notification( $user )
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

/*
// Disable WordPress New User Notification admin email
if ( ! function_exists ( 'wp_new_user_notification' ) )
{
  function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' )
  {
    global $wpdb, $wp_hasher;
    $user = get_userdata( $user_id );

    // blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

    // Generate something random for a password reset key.
    $key = wp_generate_password( 20, false );

    // This action is documented in wp-login.php
    do_action( 'retrieve_password_key', $user->user_login, $key );

    // Now insert the key, hashed, into the DB
    if ( empty( $wp_hasher ) )
    {
      $wp_hasher = new PasswordHash( 8, true );
    }
    $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user->user_login ) );

    $switched_locale = switch_to_locale( get_user_locale( $user ) );

    $message = sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
    $message .= __( 'To set your password, visit the following address:' ) . "\r\n\r\n";
    $message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user->user_login ), 'login' ) . ">\r\n\r\n";

    $message .= wp_login_url() . "\r\n";

    wp_mail( $user->user_email, sprintf( __( '[%s] Your username and password info' ), $blogname ), $message );
  }
};
*/