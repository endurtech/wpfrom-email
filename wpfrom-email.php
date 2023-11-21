<?php
/* --------------------------------------------------
Plugin Name: WPFrom Email
Plugin URI: https://endurtech.com/wpfrom-email-wordpress-plugin/
Description: Replaces default WordPress sender FROM Name and Email Address. NEW admin email options.
Author: WP Gear Pro
Author URI: https://wpgearpro.com
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 5.4
Tested up to: 6.4
Version: 1.9.2
Text Domain: wpfrom-emails
Domain Path: /locale

TODOS:
=====================================================
- Use one database record to obtain/set values.
- Option: Disable 'E-mail address change' user email notification.

NOTEPAD:
=====================================================
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
  $email_killed_init = esc_html( get_option( 'wpfrom_mail_sender_email_id' ) );
  $custom_email = get_option( 'wpfrom_custom_sender_id' );
  if( $email_killed_init == '' && $custom_email == '1' )
  {
    $glances[] = '<li style="float:none;"><i class="dashicons dashicons-email" aria-hidden="true" style="color:#e14d43;"></i> WordPress Emails <strong>Disabled</strong></li>';
	}
	return $glances;
}

// WPFrom Plugin Deactivation Database Cleanup, you're welcome!
register_deactivation_hook( __FILE__, 'wpfrom_deactivation_cleaner' );
function wpfrom_deactivation_cleaner()
{
  delete_option( 'wpfrom_custom_sender_id' );
  delete_option( 'wpfrom_mail_sender_email_id' );
  delete_option( 'wpfrom_mail_sender_name_id' );
  delete_option( 'wpfrom_admin_verify_email_id' );
  delete_option( 'wpfrom_pwd_admin_email_id' );
  delete_option( 'wpfrom_pwd_user_email_id' );
  delete_option( 'wpfrom_new_user_admin_email_id' );
  
  delete_option( 'wpfrom_autoupdate_core_email_id' );
  delete_option( 'wpfrom_autoupdate_plugin_email_id' );
  delete_option( 'wpfrom_autoupdate_theme_email_id' );

  delete_option( 'wpfrom_mail_sender_id' ); // since 1.0, now junk
  delete_option( 'wpfrom_kill_email_id' ); // since 1.4.2, now junk
}

// WordPress plugin registration
add_action( 'admin_init', 'wpfrom_mail_sender_register' );
function wpfrom_mail_sender_register()
{
  // Settings Section
  //add_settings_section( 'wpfrom_mail_sender_section', WPF_SETTINGS, 'wpfrom_mail_intro_text', 'wpfrom_mail_sender' );
  add_settings_section( 'wpfrom_mail_sender_section', '', 'wpfrom_mail_intro_text', 'wpfrom_mail_sender' );

  // Enable WordPress Custom Emails
  add_settings_field( 'wpfrom_custom_sender_id', __('WPFrom Custom Sender', 'wpfrom-mail'), 'wpfrom_custom_sender', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_custom_sender_id' );
  // Custom WordPress Sender Email
	add_settings_field( 'wpfrom_mail_sender_email_id', __('Custom Senders Email', 'wpfrom-mail'), 'wpfrom_mail_sender_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_email_id' );
  // Custom WordPress Sender Name
	add_settings_field( 'wpfrom_mail_sender_name_id', __('Custom Senders Name', 'wpfrom-mail'), 'wpfrom_mail_sender_name', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_mail_sender_name_id' );

  // Disable WordPress AutoUpdate Admin Email Notifications - Core
  add_settings_field( 'wpfrom_autoupdate_core_email_id', __('AutoUpdate Core Email', 'wpfrom-mail'), 'wpfrom_autoupdate_core_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_autoupdate_core_email_id' );
  // Disable WordPress AutoUpdate Admin Email Notifications - Plugin
  add_settings_field( 'wpfrom_autoupdate_plugin_email_id', __('AutoUpdate Plugin Email', 'wpfrom-mail'), 'wpfrom_autoupdate_plugin_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_autoupdate_plugin_email_id' );
  // Disable WordPress AutoUpdate Admin Email Notifications - Theme
  add_settings_field( 'wpfrom_autoupdate_theme_email_id', __('AutoUpdate Theme Email', 'wpfrom-mail'), 'wpfrom_autoupdate_theme_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_autoupdate_theme_email_id' );

  // Disable WordPress "Admin Email Verification" prompt
  add_settings_field( 'wpfrom_admin_verify_email_id', __('Admin Email Verification', 'wpfrom-mail'), 'wpfrom_admin_verify_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_admin_verify_email_id' );
  // Disable WordPress "Password Rest" admin email
  add_settings_field( 'wpfrom_pwd_admin_email_id', __('User Password Reset', 'wpfrom-mail'), 'wpfrom_pwd_admin_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_pwd_admin_email_id' );
  // Disable WordPress "Password Changed" user email
  add_settings_field( 'wpfrom_pwd_user_email_id', __('User Password Changed', 'wpfrom-mail'), 'wpfrom_pwd_user_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_pwd_user_email_id' );
  // Disable WordPress "New User Registration" admin email
  add_settings_field( 'wpfrom_new_user_admin_email_id', __('New User Registration', 'wpfrom-mail'), 'wpfrom_new_user_admin_email', 'wpfrom_mail_sender', 'wpfrom_mail_sender_section' );
  register_setting( 'wpfrom_mail_sender_section', 'wpfrom_new_user_admin_email_id' );
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
  echo '<input name="wpfrom_custom_sender_id" id="wpfrom_custom_sender_id" type="checkbox" value="1" ' . $custom_email . '/> <label for="wpfrom_custom_sender_id">Enable custom WordPress emails?</label><br /><span style="font-size:12px; padding-left:10px;">If enabled, set Custom Senders Email/Name below.</span>';
}



// FROM Email field
function wpfrom_mail_sender_email()
{
  $sender_email = esc_html( get_option( 'wpfrom_mail_sender_email_id' ) );
  if( $sender_email == '' )
  {
    delete_option( 'wpfrom_mail_sender_email_id' );
  }
  echo '<input name="wpfrom_mail_sender_email_id" id="wpfrom_mail_sender_email_id" type="email" placeholder="wordpress@yourdomain.com" class="regular-text" value="' . $sender_email . '" /><br /><span style="font-size:12px; padding-left:10px;">Want to Disable ALL WordPress emails? Leave this blank!</span>';
}

// FROM Name field
function wpfrom_mail_sender_name()
{
  $sender_name = esc_html( get_option( 'wpfrom_mail_sender_name_id' ) );
  if( $sender_name == '' )
  {
    delete_option( 'wpfrom_mail_sender_name_id' );
  }
  echo '<input name="wpfrom_mail_sender_name_id" id="wpfrom_mail_sender_name_id" type="text" placeholder="WordPress" class="regular-text" value="' . $sender_name . '" /><br /><span style="font-size:12px; padding-left:10px;">Do not use commas and/or other special characters.</span>
  <p>&nbsp;</p>';
}



// Disable WordPress AutoUpdate Admin Email Notifications - Core
function wpfrom_autoupdate_core_email()
{
  //init vars
  $wpfrom_autoupdate_core_email_prompt = get_option( 'wpfrom_autoupdate_core_email_id' );

  $wpc = '';
  $wpc1 = '';
  $wpc2 = '';

  if( ! isset ( $wpfrom_autoupdate_core_email_prompt ) || $wpfrom_autoupdate_core_email_prompt == '' )
  {
    delete_option( 'wpfrom_autoupdate_core_email_id' );
    $wpc = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_core_email_prompt == '1' )
  {
    $wpc1 = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_core_email_prompt == '2' )
  {
    $wpc2 = ' selected="selected"';
  }

  echo '<select name="wpfrom_autoupdate_core_email_id" id="wpfrom_autoupdate_core_email_id">
    <option value=""'. $wpc .'>Default, Send Core AutoUpdate Notices</option>
    <option value="1"'. $wpc1 .'>Send Core AutoUpdate Failure Notices</option>
    <option value="2"'. $wpc2 .'>Disable ALL Core AutoUpdate Notices</option>
  </select> <br /><span style="font-size:12px; padding-left:10px;">Controls the <strong>WordPress Core</strong> AutoUpdate Admin Email Notification.</span>';
}

// Disable WordPress AutoUpdate Admin Email Notifications - Plugin
function wpfrom_autoupdate_plugin_email()
{
  //init vars
  $wpfrom_autoupdate_plugin_email_prompt = get_option( 'wpfrom_autoupdate_plugin_email_id' );

  $wpp = '';
  $wpp1 = '';
  $wpp2 = '';

  if( ! isset( $wpfrom_autoupdate_plugin_email_prompt ) || $wpfrom_autoupdate_plugin_email_prompt == '' )
  {
    delete_option( 'wpfrom_autoupdate_plugin_email_id' );
    $wpp = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_plugin_email_prompt == '1' )
  {
    $wpp1 = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_plugin_email_prompt == '2' )
  {
    $wpp2 = ' selected="selected"';
  }

  echo '<select name="wpfrom_autoupdate_plugin_email_id" id="wpfrom_autoupdate_plugin_email_id">
    <option value=""'. $wpp .'>Default, Send Plugin AutoUpdate Notices</option>
    <option value="1"'. $wpp1 .'>Send Plugin AutoUpdate Failure Notices</option>
    <option value="2"'. $wpp2 .'>Disable ALL Plugin AutoUpdate Notices</option>
  </select> <br /><span style="font-size:12px; padding-left:10px;">Controls the <strong>WordPress Plugin</strong> AutoUpdate Admin Emails.</span>';
}

// Disable WordPress AutoUpdate Admin Email Notifications - Theme
function wpfrom_autoupdate_theme_email()
{
  //init vars
  $wpfrom_autoupdate_theme_email_prompt = get_option( 'wpfrom_autoupdate_theme_email_id' );

  $wpt = '';
  $wpt1 = '';
  $wpt2 = '';

  if( ! isset( $wpfrom_autoupdate_theme_email_prompt ) || $wpfrom_autoupdate_theme_email_prompt == '' )
  {
    delete_option( 'wpfrom_autoupdate_theme_email_id' );
    $wpt = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_theme_email_prompt == '1' )
  {
    $wpt1 = ' selected="selected"';
  }
  elseif( $wpfrom_autoupdate_theme_email_prompt == '2' )
  {
    $wpt2 = ' selected="selected"';
  }

  echo '<select name="wpfrom_autoupdate_theme_email_id" id="wpfrom_autoupdate_theme_email_id">
    <option value=""'. $wpt .'>Default, Send Theme AutoUpdate Notices</option>
    <option value="1"'. $wpt1 .'>Send Theme AutoUpdate Failure Notices</option>
    <option value="2"'. $wpt2 .'>Disable ALL Theme AutoUpdate Notices</option>
  </select> <br /><span style="font-size:12px; padding-left:10px;">Controls the <strong>WordPress Theme</strong> AutoUpdate Admin Emails.</span>
  <p>&nbsp;</p>';
}



// Disable WordPress "Admin Email Verification" prompt checkbox
function wpfrom_admin_verify_email()
{
  $admin_verify_email_prompt = get_option( 'wpfrom_admin_verify_email_id' );
  if( ! isset ( $admin_verify_email_prompt ) || $admin_verify_email_prompt == '' )
  {
    delete_option( 'wpfrom_admin_verify_email_id' );
  }
  else
  {
    $admin_verify_email_prompt = 'checked="checked" ';
  }
  echo '<input name="wpfrom_admin_verify_email_id" id="wpfrom_admin_verify_email_id" type="checkbox" value="1" '.$admin_verify_email_prompt.'/> <label for="wpfrom_admin_verify_email_id">Disable WordPress <strong>Admin Email Verification</strong> prompt?</label><br /><span style="font-size:12px; padding-left:10px;">Stop WordPress from periodically requesting admin email verification.</span>';
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
  echo '<input name="wpfrom_pwd_admin_email_id" id="wpfrom_pwd_admin_email_id" type="checkbox" value="1" '.$pwd_admin_email.'/> <label for="wpfrom_pwd_admin_email_id">Disable <strong>Admin email notice</strong> upon user password reset?</label><br /><span style="font-size:12px; padding-left:10px;">Stop notifications when a user resets their password.</span>';
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
  echo '<input name="wpfrom_pwd_user_email_id" id="wpfrom_pwd_user_email_id" type="checkbox" value="1" '.$pwd_user_email.'/> <label for="wpfrom_pwd_user_email_id">Disable <strong>User email notice</strong> upon user password change?</label><br /><span style="font-size:12px; padding-left:10px;">Stop notifications to users when their password is changed.</span>';
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
  echo '<input name="wpfrom_new_user_admin_email_id" id="wpfrom_new_user_admin_email_id" type="checkbox" value="1" '.$new_user_admin_email.'/> <label for="wpfrom_new_user_admin_email_id">Disable <strong>Admin email notice</strong> upon "New User Registration"?</label><br /><span style="font-size:12px; padding-left:10px;">Only for use with Gravity Forms User Registration Add-On. <a href="https://endurtech.com/wpfrom-email-wordpress-plugin/#using-wpfrom-email-wordpress-plugin" target="_blank" title="Opens in New Window">Read more</a></span>';
}

// Page description
function wpfrom_mail_intro_text()
{
  /*echo '<p>Replaces the default WordPress FROM <strong>Email</strong> and <strong>Name</strong>. Enable and set Senders Email (<strong><em>otherwise all mail is disabled</em></strong>).</p>
  <p>Did <a href="https://wordpress.org/plugins/wpfrom-email/" target="_blank" title="Opens New Window">this plugin</a> save you time and add value? <a href="https://endurtech.com/give-thanks/" target="_blank" title="Opens New Window"><strong>Share your appreciation</strong></a> and support future improvements.</p>';*/
}
// Page exit statement
function wpfrom_mail_exit_text()
{
  echo '<p>Did <a href="https://wordpress.org/plugins/wpfrom-email/" target="_blank" title="Opens New Window">this plugin</a> save you time? <a href="https://endurtech.com/give-thanks/" target="_blank" title="Opens New Window"><strong>Share your appreciation</strong></a> and support future improvements.</p>';
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
    <form method="post" action="options.php" accept-charset="utf-8">';
      settings_fields( 'wpfrom_mail_sender_section' );
      do_settings_sections( 'wpfrom_mail_sender' );
      echo wpfrom_mail_exit_text();
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
        return esc_html( get_option( 'wpfrom_mail_sender_email_id' ) );
      }
    }
    // Replace the default FROM Name: WordPress
    add_filter( 'wp_mail_from_name', 'wpfrom_new_mail_from_name' );
    if( ! function_exists( 'wpfrom_new_mail_from_name' ) )
    {
      function wpfrom_new_mail_from_name()
      {
        return esc_html( get_option( 'wpfrom_mail_sender_name_id' ) );
      }
    }
  }
}




// Disable WordPress AutoUpdate Admin Email Notifications - Core
$wpfrom_autoupdate_core_email_prompt = get_option( 'wpfrom_autoupdate_core_email_id' );
if( $wpfrom_autoupdate_core_email_prompt == '2' )
{
  // Disable ALL Core Email
  add_filter( 'auto_core_update_send_email', '__return_false' );
}
elseif( $wpfrom_autoupdate_core_email_prompt == '1' )
{
  // Only Send Core Email Errors
  add_filter( 'auto_core_update_send_email', 'wpfrom_autoupdate_core_email_onerror', 10, 4 );
  function wpfrom_autoupdate_core_email_onerror($send,$type,$core_update,$result)
  {
    if( ! empty( $type ) && $type == 'success' )
    {
      return false;
    }
    else
    {
      return true;
    }
  }
}

// Disable WordPress AutoUpdate Admin Email Notifications - Plugin
$wpfrom_autoupdate_plugin_email_prompt = get_option( 'wpfrom_autoupdate_plugin_email_id' );
if( $wpfrom_autoupdate_plugin_email_prompt == '2' )
{
  add_filter( 'auto_plugin_update_send_email', 'wpfrom_disable_autoupdate_plugin_email', 10, 2 );
  function wpfrom_disable_autoupdate_plugin_email()
  {
    return false;
  }
}
elseif( $wpfrom_autoupdate_plugin_email_prompt == '1' )
{
  add_filter( 'auto_plugin_update_send_email', 'wpfrom_disable_autoupdate_failed_plugin_email', 10, 1 );
  function wpfrom_disable_autoupdate_failed_plugin_email( $true, $update_results = null )
  {
    if( is_array( $update_results ) )
    {
      foreach( $update_results as $update_result )
      {
        if( true !== $update_result->result )
        {
          return $true;
        }
      }
    }
    return false;
  }
}

// Disable WordPress AutoUpdate Admin Email Notifications - Theme
$wpfrom_autoupdate_theme_email_prompt = get_option( 'wpfrom_autoupdate_theme_email_id' );
if( $wpfrom_autoupdate_theme_email_prompt == '2' )
{
  add_filter( 'auto_theme_update_send_email', 'wpfrom_disable_autoupdate_theme_email', 10, 2 );
  function wpfrom_disable_autoupdate_theme_email()
  {
    return false;
  }
}
elseif( $wpfrom_autoupdate_theme_email_prompt == '1' )
{
  add_filter( 'auto_theme_update_send_email', 'wpfrom_disable_autoupdate_failed_theme_email', 10, 1 );
  function wpfrom_disable_autoupdate_failed_theme_email( $true, $update_results = null )
  {
    if( is_array( $update_results ) )
    {
      foreach( $update_results as $update_result )
      {
        if( true !== $update_result->result )
        {
          return $true;
        }
      }
    }
    return false;
  }
}



// Disable WordPress "Administration Email Verification" prompt 
$admin_email_verify_prompt_init = get_option( 'wpfrom_admin_verify_email_id' );
if( $admin_email_verify_prompt_init == '1' )
{
  add_filter( 'admin_email_check_interval', '__return_false' );
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

// Disable WordPress "Password Changed" user email
$pwd_user_email_init = get_option( 'wpfrom_pwd_user_email_id' );
if( $pwd_user_email_init == '1' )
{
  add_filter( 'send_password_change_email', '__return_false' );
}

// Disable WordPress "New User Registration" admin email
$new_user_admin_email_init = get_option( 'wpfrom_new_user_admin_email_id' );
if( $new_user_admin_email_init == '1' )
{
  add_filter( 'wp_new_user_notification_email_admin', 'disable_admin_email', 10, 3 );
  function disable_admin_email( $wp_new_user_notification_email_admin, $user, $blogname )
  {
    return;
  }
}



//
// Thank you for checking out my code. Let me know how I can improve upon it!
//
?>