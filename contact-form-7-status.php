<?php
/**
 * Plugin Name: Application Status
 * Plugin URI: https://larasoftbd.com/form-status-update
 * Description: Update Student, Event, Customer form / appliation update. (Contact Form 7 required)
 * Version: 1.0.0
 * Author: larasoft
 * Author URI: https://larasoftbd.com/
 * Text Domain: larasoft
 * Tags: WP, application-status, status, appliation, contact, form, online, contact-form-7
 * Domain Path: /languages
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * @package     Contact-Form-7-Extension
 * @category 	Core
 * @author 		LaraSoft
 */

/**
 * Restrict direct access
 */




if ( ! defined( 'ABSPATH' ) ) { exit; }
define('ASDIR', plugin_dir_path( __FILE__ ));

require_once(ASDIR . 'inc/aps-class.php');
new contactApplicaitonStatus;
