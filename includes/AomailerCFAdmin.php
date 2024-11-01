<?php
class AomailerCFAdmin
{
	protected $page;
	protected $capability;
	protected $url;
	protected $functions_page;
	protected $view_path;
	protected $config;
	
	public $settings = [];
	
	public $history = [];
	public $is_plugin_page = 0;
	
	public function __construct($is_plugin_page)
	{
		$this->page = 'TargetSMS CF7';
		$this->capability = 'edit_others_pages';
		$this->url = 'aomailer-cf-sms';
		$this->functions_page = 'settings_page_sms';
		$this->is_plugin_page = $is_plugin_page;
		$this->view_path =  realpath(AOMP_AOMAILER_CF_DIR) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
	}
	/**
	 * wpAdmin()
	 */
	public function wpAdmin()
	{
		add_action('admin_menu', [$this, 'settings_menu']);
		if (is_plugin_active('contact-form-7/wp-contact-form-7.php')) {
			add_action( 'wpcf7_after_save', [$this, 'save_form'] );
			add_filter('wpcf7_editor_panels', [$this, 'settings_menu_wpcf7']);
		}
		add_action('wp_ajax_aomailer_cf_balance_action', [$this, 'balance_action']);
		add_action('wp_ajax_aomailer_cf_load_form_smssettings', [$this, 'load_form_smssettings']);
		add_action('wp_ajax_aomailer_cf_load_form_smshistory', [$this, 'load_form_smshistory']);
	}
	/**
	 * settings_menu()
	 */
	public function settings_menu()
	{
		add_menu_page(
			$this->page,
			$this->page, 
			$this->capability, 
			$this->url,
			[$this, $this->functions_page] 
		);
	}
	/**
	 * settings_menu_cf()
	 */
	public function settings_menu_wpcf7($panels)
	{
		$panels['target-sms-panel'] = array(
			'title' => 'TargetSMS',
			'callback' => [$this, 'settings_form']
		);
		return $panels;
	}

	public function settings_form($form)
	{
		if (!wpcf7_admin_has_edit_cap()) {
			return;
		}
		self::resourceRegistration_wpcf7();
		$settings = AomailerCFSettings::aomp()->loadSettings();
		$form_settings = AomailerCFFormSettings::aomp()->loadFormSettings(['form_id' => method_exists($form, 'id') ? $form->id() : $form->id]);
		if ($form_settings->is_new()) {	
			$default_text = [
				'site_url' => wp_parse_url(esc_url(get_bloginfo('url')), PHP_URL_HOST),
				'admin' => [
					'message' => __('The client','aomailer_cf'),
					'tags' => '[your-name] ([your-email])',
					'action' => __('have filled out the form on the website','aomailer_cf'),
				],
				'client' => [
					'message' => __('Hello! You filled out the form on the website','aomailer_cf'),
					'action' => __('Our manager will contact you soon','aomailer_cf'),
				],
			];
			//admin
			$form_settings->admin_message = $default_text['admin']['message'] . ' ';
			$form_settings->admin_message .= $default_text['admin']['tags'] . ' ';
			$form_settings->admin_message .= $default_text['admin']['action'] . ' ';
			$form_settings->admin_message .= $default_text['site_url'];
			//client
			$form_settings->client_message = $default_text['client']['message'] . ' ';
			$form_settings->client_message .= $default_text['site_url'] . '. ';
			$form_settings->client_message .= $default_text['client']['action'] . '.';
			unset($default_text);
		}
		if (file_exists($this->view_path . 'sms_page' . DIRECTORY_SEPARATOR . '__sms_templates.php')) {
			require_once ($this->view_path . 'sms_page' . DIRECTORY_SEPARATOR . '__sms_templates.php');
		}
	}
	
	
	/**
	 * save_form()
	 *  Подразумевалась обработка нескольких полей с телефонами.
	 *  Но позже мы решили обрабатывать по одному полю у админа и пользователя.
	 *  Для этого массивы пользователя останавливаются с помощью "break".
	 * 	А массивы админа подменяем первым полем.
	 */
	public function save_form($form)
	{
		$form_id = method_exists($form, 'id') ? $form->id() : $form->id;
		$form_settings = AomailerCFFormSettings::aomp()->loadFormSettings(['form_id' => $form_id]);
		$first_tel = false;
		$mail_tags = $form->scan_form_tags();
		foreach($mail_tags as $key => $value) {
			if ($value->basetype == 'tel') {
				$first_tel = '[' . $value->name . ']';
				break;
			}
		}
		unset($mail_tags);
		
		if (isset($_POST['wpcf7_aomailer'])) {
			$attr = $_POST['wpcf7_aomailer'];
			// admin number
			$attr['admin_number'] = $attr['admin_number'][0];
			if (!empty($attr['admin_number'])) {
				$attr['admin_number'] = preg_replace('/[^,0-9]/', '', $attr['admin_number']);
				$attr['admin_number'] = trim($attr['admin_number'], ',');
				$attr['admin_number'] = explode(',',  $attr['admin_number']);
			}
			$form_settings->attributes($attr);
			unset($attr);
			// client number
			$form_settings->client_number[0] = sanitize_text_field($first_tel);
			if ($form_settings->is_new()) {
				$form_settings->set_form_id($form_id);
				$form_settings->insert();
			} else {
				$form_settings->update();
			}
		}
	}
	
	
	/**
	 * settings_page_sms()
	 */
	public function settings_page_sms()
	{
		if (!empty($this->is_plugin_page)) {
			self::resourceRegistration();
			$this->settings = $this->settings + AomailerCFSettings::aomp()->loadSettings('sms');
			$this->settings['error'] = false;
			$this->history = AomailerCFSMSApi::aomp()->getHistory();	
		}
		if (file_exists($this->view_path.'settings_page_sms.php')) {
			require_once $this->view_path.'settings_page_sms.php';
		}
	}
	
	/**
	 * balance_action() 
	 */
	public function balance_action() 
	{
		$balance = 0;
		if (!self::verifyNonce($_POST['token'])) {
			echo esc_html($balance);
			wp_die(); 
		}
		$settings = AomailerCFSettings::aomp()->loadSettings('sms');
		if (!empty($_POST['type']) && $_POST['type']==='balance') {
			if (!empty($settings['login']) && !empty($settings['passwd'])) {
				$answer = AomailerCFSMSApi::aomp()->getBalance($settings['login'], $settings['passwd']);
				if (empty($answer['error'])) {
					$balance = AomailerCFSMSApi::aomp()->getFormat($answer['balance'], 'money');
				}
			}
		}
		echo esc_html($balance);
		wp_die(); 
	}
	
	/**
	 * load_form_smssettings() 
	 */
	public function load_form_smssettings() 
	{
		if (!self::verifyNonce($_POST['data']['token'])) {
			wp_die(); 
		}
		if (!empty($_POST['data']['AomailerSmsSettings'])) {
			$data = new AomailerCFSettings;
			$data->attributes($_POST['data']['AomailerSmsSettings']);
			if (!$data->add_data()) {
				$this->settings['error'] = self::addError(__('No Save', 'aomailer_cf'));
			} else {
				$this->settings['success'] =__('ConnectSuccess', 'aomailer_cf');	
			} 
		} else {
			$this->settings['error'] =__('Missing data', 'aomailer_cf');
		}
		$this->settings = $this->settings + AomailerCFSettings::aomp()->loadSettings('sms');
		require_once realpath(AOMP_AOMAILER_CF_DIR) . '/views/sms_page/__sms_settings.php'; 
		wp_die();
	}
	/**
	 * load_form_smshistory() 
	 */
	public function load_form_smshistory() 
	{
		if (!self::verifyNonce($_POST['data']['token'])) {
			wp_die(); 
		}
		$this->settings = $this->settings + AomailerCFSettings::aomp()->loadSettings('sms');
		if (!empty($_POST['data']['AomailerSmsHistory'])) {
			$data = new AomailerCFSMSApi;
			$data->attributes($_POST['data']['AomailerSmsHistory']);
			$this->history = $data->getHistory();
			if (empty($this->history) || !empty($this->history['error'])) {
				$this->settings['error'] = self::addError(__('No History', 'aomailer_cf'));
			}	
		}
		require_once realpath(AOMP_AOMAILER_CF_DIR) . '/views/sms_page/__sms_history.php'; 
		wp_die();
	}
	/**
	 * addError($error)
	 */
	public function addError($error='')
	{
		if (!empty($error)) {
			if (empty($this->settings['error'])) {
				$this->settings['error'] = $error.'. ';
			} else {
				if (!preg_match('/('.$error.')/i', $this->settings['error'])) {
					$this->settings['error'] .= $error.'. ';
				}
			}
		}
		return $this->settings['error'];
	}
	/**
	 * resourceRegistration()
	 */
	public function resourceRegistration()
	{
		wp_enqueue_style('aomp_bootstrap_min_css', plugins_url('assets/css/bootstrap.min.css', dirname(__FILE__)));
		wp_enqueue_style('aomp_bootstrap_color_css', plugins_url('assets/css/bootstrap5_colorfix.css', dirname(__FILE__)));		
		wp_enqueue_style('aomp_fontawesom_min_css', plugins_url('assets/css/font-awesome-4.7.0.min.css', dirname(__FILE__)));		
		
		if (AOMP_CF_DEBUG) {
			wp_enqueue_style('aomp_plugin_css', plugins_url('assets/css/style.css', dirname(__FILE__)));
		} else {
			wp_enqueue_style('aomp_plugin_css', plugins_url('assets/css/style.min.css', dirname(__FILE__)));
		}
		
		wp_enqueue_script('aomp_bootstrap_min_js', plugins_url('assets/js/bootstrap.min.js', dirname(__FILE__)));
		
		if (AOMP_CF_DEBUG) {
			wp_enqueue_script('aomp_plugin_js', plugins_url('assets/js/script.js', dirname(__FILE__)));
		} else {
			wp_enqueue_script('aomp_plugin_js', plugins_url('assets/js/script.min.js', dirname(__FILE__)));
		}
	}
	/**
	 * resourceRegistration_wpcf7()
	 */
	public function resourceRegistration_wpcf7()
	{
		wp_enqueue_style('aomp_plugin_css_wpcf7', plugins_url('assets/css/style_wpcf7.css', dirname(__FILE__)));
	}
	
	
	/**
	 * validateToken()
	 */
	private function verifyNonce($str='')
	{
		if (!empty($str) && wp_verify_nonce($str)) {
			return true;
		}
		return false;
	}
	/**
	 * install()
	 */
	public static function install()
	{
		AomailerCFSettings::aomp()->create_table();
		AomailerCFFormSettings::aomp()->create_table();
	}
	/**
	 * uninstall()
	 */
	public static function uninstall()
	{
		AomailerCFSettings::aomp()->delete_table();
		AomailerCFFormSettings::aomp()->delete_table();
	}
	/**
	 * deactivation()
	 */
	public static function deactivation()
	{
		AomailerCFSettings::aomp()->trincate_table();
		AomailerCFFormSettings::aomp()->trincate_table();
	}
	/**
	 * dump()
	 */
	public static function dump($s, $w = null)
	{
		if (!AOMP_CF_DEBUG) {
			return;
		}
		print ('<br>'. str_repeat('*', 10));
		print("<pre>");
		if (!empty($w)) {
			print($w.'<br><br>');
		}
		print_r($s);
		print("</pre>");
	}
	/**
	 * err_l()
	 */
	public static function err_l($s, $t = true, $text = "")
	{
		if (!AOMP_CF_DEBUG) {
			return;
		}
		if ($t) {
			$s = print_r($s, true);
		}
		$time = date('Y-m-d H i s', strtotime('+5 seconds'));
		if (!empty($text)) {
			$text = $text . "\n";
			//~ error_log($text."\r\n".PHP_EOL, 3, AOMP_AOMAILER_CF_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $time . '_log.txt');
			error_log($text."\r\n".PHP_EOL, 3, AOMP_AOMAILER_CF_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'log.txt');
		}
		if (!empty($s)) {
			//~ error_log($s."\r\n".PHP_EOL, 3, AOMP_AOMAILER_CF_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $time . '_log.txt');
			error_log($s."\r\n".PHP_EOL, 3, AOMP_AOMAILER_CF_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'log.txt');
		}
	}
	/**
	 * sms_page_scripts()
	 */
	public function sms_page_scripts() {
		add_action('admin_print_footer_scripts', 'wpfc7_aomailer_sms_template', 99);
		function wpfc7_aomailer_sms_template(){
			?>
				<script type="text/javascript">
					// copy on click
					jQuery('#wpcf7_aomailer .ao-tags').on('click', function() {
						let e = this;
						if (window.getSelection) { 
							let s=window.getSelection(); 
							if (s.setBaseAndExtent[0]) { 
								s.setBaseAndExtent(e,0,e,e.innerText.length-1); 
							}else{ 
								let r=document.createRange(); 
								r.selectNodeContents(e); 
								s.removeAllRanges(); 
								s.addRange(r);
							} 
						} else if (document.getSelection) { 
							let s=document.getSelection(); 
							let r=document.createRange(); 
							r.selectNodeContents(e); 
							s.removeAllRanges(); 
							s.addRange(r); 
						} else if (document.selection) { 
							let r=document.body.createTextRange(); 
							r.moveToElementText(e); 
							r.select();
						}
						document.execCommand('copy');
					});
					
					//toltip
					let admin_number_tooltip = jQuery('#admin_number_tooltip');
					let admin_message_tooltip = jQuery('#admin_message_tooltip');
					let client_message_tooltip = jQuery('#client_message_tooltip');
					
					let admin_number_tooltip_switch = jQuery('#admin_number_tooltip_switch');
					let admin_message_tooltip_switch = jQuery('#admin_message_tooltip_switch');
					let client_message_tooltip_switch = jQuery('#client_message_tooltip_switch');
					
					let tooltip_number_visible = false;
					let admin_message_tooltip_visible = false;
					let client_message_tooltip_visible = false;
					
					admin_number_tooltip_switch.on('click', function () {
						if (tooltip_number_visible) { 
							admin_number_tooltip.fadeOut(400);
							tooltip_number_visible = false;
						} else {
							admin_number_tooltip.fadeIn(400);
							tooltip_number_visible = true;
						}
					});
					
					admin_message_tooltip_switch.on('click', function () {
						if (admin_message_tooltip_visible) { 
							admin_message_tooltip.fadeOut(400);
							admin_message_tooltip_visible = false;
						} else {
							admin_message_tooltip.fadeIn(400);
							admin_message_tooltip_visible = true;
						}
					});
					
					client_message_tooltip_switch.on('click', function () {
						if (client_message_tooltip_visible) { 
							client_message_tooltip.fadeOut(400);
							client_message_tooltip_visible = false;
						} else {
							client_message_tooltip.fadeIn(400);
							client_message_tooltip_visible = true;
						}
					});
					
					// counter
					jQuery('#wpcf7_aomailer_admin_message, #wpcf7_aomailer_client_message').on('keyup', function(e){
						let count = 0;
						let size = 0;	
						let value = this.value;
						if (value && typeof value !== 'undefined') {
							count = value.length;
							let devider = 0;
							if (value.match(/[а-я]/gi)) {
								if (count<=70) {
									divider = 70;
								} else {
									divider = 67;
								}
							} else {
								if (count<=160) {
									divider = 160;
								} else {
									divider = 153;
								}
							}
							size = Math.ceil(count/divider);
						}
						jQuery(this).parent('div').find('.aomp-count-sms-letters').text(count);
						jQuery(this).parent('div').find('.aomp-size-sms-letters').text(size);
						jQuery(this).parent('div').find('.aomp-length-sms-letters').text(divider);
					});
					
				</script>
			<?php
		}
	}
	/**
	 * sms_info()
	 */
	public function sms_info($message) {
		$count = 0;
		$size = 0;
		$divider = '';
		if (!empty($message)) {
			if (preg_match('/[а-я]/i', $message)) {
				$count = mb_strlen($message);
				if ($count <= 70) {
					$divider = 70;
				} else {
					$divider = 67;
				}
			} else {
				$count = strlen($message);
				if ($count <= 160) {
					$divider = 160;
				} else {
					$divider = 153;
				}
			}
			$size = ceil($count/$divider);
		}
		return [ 'count'=> $count, 'size'=> $size, 'divider'=> $divider ];
	}
	/**
	 * aomp($is_plugin_page, $className=__CLASS__)
	 */ 
	public static function aomp($is_plugin_page, $className=__CLASS__)
	{
		return new $className($is_plugin_page);
	}
}
