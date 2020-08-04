# WPFrom Email

Contributors: Manny Rodrigues  
Donations: https://endurtech.com/give-thanks/  
Tags: email, email from, outgoing mail, disable email, disable outgoing, admin email, user email, notification, password, default  
Requires WP: 5.0  
Tested to: 5.5  
Stable tag: 1.7.0  
License: GPLv3 or later  

## Description

> Introducing [WPFrom Email](https://endurtech.com/wpfrom-email-wordpress-plugin/), a free [WordPress Plugin](https://wordpress.org/plugins/wpfrom-email/) aimed at helping you quickly and easily update the default WordPress sender FROM Name and Email Address.  

> To increase brand authenticity and create name recognition among your visitors it is recommended update how WordPress sends out emails to your subscribers. Leaving the WordPress email sending settings in their default state may even result in email delivery issues. This is because the default wordpress@ emails usually trigger Internet Service Providers and end user email spam filters.  

> Did [this plugin](https://endurtech.com/wpfrom-email-wordpress-plugin/) save you time and add value? [Share your appreciation](https://endurtech.com/give-thanks/) and support future improvements.  

## Features

* Easily replace the default WordPress FROM Name and Email Address.
* Disable Admin email notification upon user password reset.
* Disable Admin email notification upon New User Registration (Gravity Forms only).
* Disable Admin email verification prompt.
* Disable User email notification upon user password change.
* Disable ALL WordPress emails by leaving the Senders Email field blank.
* Notice of Disabled WordPress emails within Dashboard At A Glance metabox.
* Upon deactivation, all associated database values are removed.

## Installation

1. Upload the plugin folder to your '/wp-content/plugins/' folder.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit the plugin settings area and adjust as needed.

## Screenshots

1. WPFrom Email Settings page located under "Settings > WPFrom Email".
2. WPFrom Admin Dashboard notice of disabled emails option triggered.

## Changelog

* 1.7.0
  * Added ability to disable admin notification email upon New User Registration (only tested against Gravity Forms User Registration Addon). This disables two email notifications. First the user notification email which the user receives upon registration with a link to set their password. Secondly, the admin notification email which the site admin receives upon new user registration: "New User Registration". You won't be able to use the option to email the new user their auto-generated password since that email is now disabled. Rather, you will have to create a Gravity Forms notification within your form to send the user the password setup email.
  * Added ability to disable the Admin Email Verification prompt.
  * Enhanced code structure.
  * Tested against WordPress 5.5
* 1.6.3
  * Tested against WordPress 5.3.1
* 1.6.2
  * Tested against WordPress 5.3
* 1.6.1
  * Fixed Dashboard At a Glance Metabox notice of WordPress Emails Disabled.
  * Added notice to not use commas (,) and other special characters within the Senders Name field.
  * Tested against WordPress 5.2.4
* 1.6.0
  * Added ability to disable admin notification email upon user password reset.
* 1.5.0
  * Added ability to disable user notification email upon user password change.
* 1.4.2
  * Updated settings page styles.
* 1.4.1
  * Updated code formatting.
  * Updated GPL licensing.
  * Renamed languages folder to locale.
* 1.4.0
  * Added database cleanup function upon plugin deactivation.
* 1.3.0
  * Added notice of disabled emails in admin dashboard at a glance metabox.
* 1.2.0
  * Added ability to disable ALL WordPress emails.
* 1.0.0
  * Initial release.

## Frequently Asked Questions

* How do I use this plugin?
  * Upon activation, visit the "Settings > WPFrom Email" section and adjust as needed.
* Emails are not going out?
  * If you check the "Enable" box, you must provide a senders email address. Otherwise no emails are sent.
* How do I improve email delivery?
  * Be sure to use a real/existing email address from your domain.