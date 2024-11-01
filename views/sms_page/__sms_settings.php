<?php
add_action('admin_print_footer_scripts', 'aomailer_cf_sms_page_settings', 99);
function aomailer_cf_sms_page_settings() {
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			var token = '<?php echo esc_html(wp_create_nonce())?>';
			jQuery(document).delegate('#sms-settings-form', 'submit', function(event){
				jQuery('#ao_custom_alert2').hide();
				var form_data = {};
				form_data.token = token;
				form_data.page_type = 'sms';
				form_data.AomailerSmsSettings = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					
					jQuery.each(event.currentTarget, function(index, value){
						
						if (value.type=='submit') {
							return false;
						}
						
						var name = value.name.replace(/.*\[|\]/gi,'');
						
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AomailerSmsSettings[name] = value.checked;
						} else {
							form_data.AomailerSmsSettings[name] = value.value;
						}
					});
				}
				reloadForm('smssettings', token, form_data, '<?php echo esc_html(admin_url())?>');
				event.preventDefault();
				return false;
			});	
		});
	</script>
<?php } ?>
<div id="ao_custom_alert2" class="row">
	<div class="col-sm-12">
		
		<?php if (!empty($this->settings['error'])) : ?>
			<p class="alert alert-danger fs-6 text">
				<?php echo esc_html(strip_tags($this->settings['error']))?>
			</p>
		<?php elseif (!empty($this->settings['success'])) : ?>
			<p class="alert alert-success fs-6 text">
				<?php echo esc_html($this->settings['success'])?>
			</p>
			
		<?php endif; ?>
		
	</div>
</div>
<div class="row">
	<div class="col-12">
		<div class="bs-callout bs-callout-danger">
			<h4><?php echo esc_html(__('SettingsAdministrator', 'aomailer_cf'))?></h4>
			<p><?php echo esc_html(__('SettingsAdministratorWarning', 'aomailer_cf'))?></p>
		</div>
	</div>
	<div class="col-sm-10 mx-auto">
		<form id="sms-settings-form" action="" method="post" role="form">
			<div class="row gy-3 justify-content-end">
				<div class="col-sm-2 text-sm-end">
					<label for="AomailerSmsSettings_login" class="control-label fw-bold"><?php echo esc_html(__('Login', 'aomailer_cf'))?></label>
				</div>
				<div class="col-sm-10">
					<input type="text" class="form-control" name="AomailerSmsSettings[login]" id="AomailerSmsSettings_login" value="<?php echo !empty($this->settings['login']) ? esc_html($this->settings['login']) : ''?>">
				</div>
				<div class="col-sm-2 text-sm-end">
					<label for="AomailerSmsSettings_passwd" class="control-label fw-bold"><?php echo esc_html(__('Password', 'aomailer_cf'))?></label>
				</div>
				<div class="col-sm-10">
					<input type="password" class="form-control" name="AomailerSmsSettings[passwd]" id="AomailerSmsSettings_passwd" placeholder="********" value="<?php echo !empty($this->settings['passwd']) ? esc_html($this->settings['passwd']) : ''?>">
				</div>
			
				<div class="col-sm-10">
					<p class="fs-6 text">
						<?php echo esc_html(__('To register in the service fill out the','aomailer_cf'))?>&nbsp;<a class="text-decoration-none" href="<?php echo esc_url($this->settings['register_link'])?>" target="_blank"><?php echo esc_html(__('form', 'aomailer_cf'))?></a>
					</p>
					<input type="submit" class="btn btn-primary pull-right btn-xs-block floating_button" value="<?php echo esc_html(__('btnSave', 'aomailer_cf'))?>">
				</div>
			</div>
		</form>
	</div>
</div>
