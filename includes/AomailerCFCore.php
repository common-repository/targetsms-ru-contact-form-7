<?php 
class AomailerCFCore
{	
	public $settings;
	
	public function __construct()
	{
		$config_path = realpath(AOMP_AOMAILER_CF_DIR) . DIRECTORY_SEPARATOR . 'config.php';
		if (file_exists($config_path)) {
			$this->settings = require($config_path);
		}
		$this->settings = $this->settings + AomailerCFSettings::aomp()->loadSettings('core');
	}
	
	/**
	 * listner()
	 */
	public function listner()
	{
		add_action( 'wpcf7_before_send_mail', [$this, 'send_sms'], 10, 1);
	}
	
	/**
	 * new_status($order_id, $old_status, $new_status)
	 */
	public function send_sms($form)
	{
		$form_settings = AomailerCFFormSettings::aomp()->loadFormSettings(['form_id' => method_exists($form, 'id') ? $form->id() : $form->id]);
		if (!$form_settings->is_new()) {
			if (!empty($form_settings->admin_enable)) {
				$data = self::prepareData('admin', $form_settings, $form);
				//~ AomailerCFAdmin::err_l($data, true, 'admin_data');
				if (!empty($data)) {
					$send = AomailerCFSMSApi::aomp()->send($data);
					//~ AomailerCFAdmin::err_l($send, true, 'admin_send');
				}
			}
			if (!empty($form_settings->client_enable)) {
				$data = self::prepareData('client', $form_settings, $form);
				//~ AomailerCFAdmin::err_l($data, true, 'client_data');
				if (!empty($data)) {
					$send = AomailerCFSMSApi::aomp()->send($data);
					//~ AomailerCFAdmin::err_l($send, true, 'client_send');
				}
			}		
		}
		return false;
	}
	
	/**
	 * prepareData($type=0, $id=0, $array=[])
	 */
	private function prepareData($type = null, $form_settings, $form)
	{
		
		if (empty($type)) {
			return false;
		}
		
		$data = [
			'login' => $this->settings['login'],
			'passwd' => $this->settings['passwd'],
		];
		
		$from_name = $type.'_from_name';
		$message = $type.'_message';
		$used_translit = $type.'_used_translit';
		$numbers = $type.'_number';
		
		$data['message'][0]['name_delivery'] = 'wordpress_contactform7';
		
		if (!empty($form_settings->$from_name)) {
			$data['message'][0]['from_name'] = $form_settings->$from_name;
		} else {
			return false;
		}
		
		if (!empty($form_settings->$message)) {
			$data['message'][0]['sms_text'] = self::wpcf7_replace_tags($form_settings->$message, $form);
			if (!empty($form_settings->$used_translit)) {
				$data['message'][0]['sms_text'] = AomailerCFSMSApi::aomp()->transliterate($data['message'][0]['sms_text']);
			}
		} else {
			return false;
		}
		if (!empty($form_settings->$numbers) && is_array($form_settings->$numbers)) {
			foreach ($form_settings->$numbers as $number) {
				if ($type == 'client') {
					$number = self::wpcf7_replace_tags($number, $form);
				}
				$number = trim($number);
				if (empty($number)) {
					return false;
				}
				$data['message'][0]['abonents'][] = [
					'number' => AomailerCFSMSApi::aomp()->getFormat($number,'phone'),
					'time_send' => '',
					'validity_period' => '',
				];
			}
		} else {
			return false;
		}
		return $data;	
	}
	
	private function wpcf7_replace_tags($data = null, $form_obj) {
		if (empty($data)) {
			return false;
		}
		// Contact Form 7 > 3.9 
		if(function_exists('wpcf7_mail_replace_tags')) {
			$data = wpcf7_mail_replace_tags($data, array());
		} elseif(method_exists($form_obj, 'replace_mail_tags')) {
			$data = $form_obj->replace_mail_tags($data);
		}
		return $data;
	}
	
	/**
	 * aomp($className=__CLASS__)
	 */ 
	public static function aomp($className=__CLASS__)
	{
		return new $className;
	}
}
