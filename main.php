<?php
/**
 * @package Wordefinery Mail.ru Counter
 */
/*
Plugin Name: Wordefinery Mail.ru Counter
Plugin URI: http://wordefinery.com/plugins/mailru-counter/?from=wp&v=0.8.10.1
Description: Displays Rating@Mail.ru counter
Version: 0.8.10.1
Author: Wordefinery
Author URI: http://wordefinery.com
License: GPLv2 or later

*/

if ( !function_exists( 'add_action' ) ) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

require_once(dirname( __FILE__ ) . '/lib/init.php');
Wordefinery::Register(dirname( __FILE__ ), 'MailruCounter', '0.8.10.1');
