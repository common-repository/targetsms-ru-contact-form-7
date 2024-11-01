<?php
/*

Plugin Name: TargetSMS.ru: СМС-рассылки и уведомления из Contact Form 7
Plugin URI: https://targetsms.ru/otpravka-sms-iz-contact-form-7
Author: TargetSMS
Version: 1.0.1
Author URI: https://targetsms.ru/
Description: С помощью плагина "TargetSMS.ru: СМС-рассылки и уведомления из Contact Form 7" Вы можете отправлять автоматические СМС-уведомления администраторам и клиентам при заполнении формы Contact Form 7.

*/
define('AOMP_AOMAILER_CF_DIR', plugin_dir_path(__FILE__));
define('AOMP_AOMAILER_CF_URL', plugin_dir_url(__FILE__));
define('AOMP_CF_DEBUG', false); // minimize everything after
require_once __DIR__ . '/autoload_cf.php';
include_once ABSPATH . 'wp-admin/includes/plugin.php'; // to check if there is woocommerce inside view
load_plugin_textdomain('aomailer_cf', false, dirname(plugin_basename(__FILE__)) . '/lang/'); // change later
register_activation_hook(__FILE__, ['AomailerCFAdmin', 'install']);
register_uninstall_hook(__FILE__, ['AomailerCFAdmin', 'uninstall']);
register_deactivation_hook(__FILE__, ['AomailerCFAdmin', 'deactivation']);
function aomp_aomailer_cf_load() {
	if (is_admin()) {
		$is_plugin_page = false;
		if (!empty($_GET['page']) && preg_match('/aomailer-cf/i', $_GET['page'])) {
			$is_plugin_page = true;
		}
		AomailerCFAdmin::aomp($is_plugin_page)->wpAdmin();
	}
	AomailerCFCore::aomp()->listner();
}

add_action('plugins_loaded', 'aomp_aomailer_cf_load');
