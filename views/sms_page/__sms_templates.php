<?php
$mail_tags = $form->collect_mail_tags();
$this->sms_page_scripts();
$sms_admin_info = $this->sms_info($form_settings->admin_message);
$sms_client_info = $this->sms_info($form_settings->client_message);
?>
<h2><?php echo esc_html(__('TargetSMS.ru: SMS from Contact Form 7','aomailer_cf'))?></h2>
<div id="wpcf7_aomailer" class="ao-container">
	<?php if (!empty($settings['error'])) : ?>
		<div class="ao-row">
			<div class="ao-col">
				<?php echo esc_html($settings['error'])?>
			</div>
		</div>
	<?php elseif (!empty($settings['success'])) : ?>
		<div class="ao-row">
			<div class="ao-col">
				<?php echo esc_html($settings['success'])?>
			</div>
		</div>		
	<?php endif; ?>
	<?php if (!empty($form_settings->getErrors())) : ?>
		<div class="ao-row">
			<div class="ao-col">
				<?php echo esc_html($form_settings->getErrors());?>
			</div>
		</div>
	<?php endif; ?>
	<div class="ao-row">
		<div class="ao-col">
			<h3><?php echo esc_html(__('SMS to administrators', 'aomailer_cf'))?></h3>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_admin_enable"><?php echo esc_html(__('Send SMS to administrators:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
				<input type="checkbox" id="wpcf7_aomailer_admin_enable" name="wpcf7_aomailer[admin_enable]" class="wide" <?php echo !empty($form_settings->admin_enable) ? 'checked' : ''?>>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_admin_phone"><?php echo esc_html(__('Mobile phone numbers of Administrators:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-6">
		  	<div class="ao-col-content">
				<input type="text" id="wpcf7_aomailer_admin_phone" name="wpcf7_aomailer[admin_number][]" class="wide" style="width:100%;" value="<?php echo !empty($form_settings->admin_number) ? esc_html(implode(', ', $form_settings->admin_number)) : ''?>">
			</div>
		</div>
		<div class="ao-col-3">
			<span id="admin_number_tooltip_switch" class="dashicons dashicons-info"></span>
			<div id="admin_number_tooltip" style="position:relative; display:none;">
				<?php echo esc_html(__('Enter phone numbers in international format, for example, +79001234567. If you neet to enter several phone numbers, use a comma to separate them', 'aomailer_cf'))?>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_admin_from_name"><?php echo esc_html(__('From Name:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
				<select id="wpcf7_aomailer_admin_from_name" name="wpcf7_aomailer[admin_from_name]" class="wide">
					<?php if (!empty($settings['array_from_name'])) : ?>
						<?php if($form_settings->is_new()): ?>		
							<option value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<?php $first = true; ?>
									<?php if ($first): ?>
										<option selected><?php echo esc_html($value['value'])?></option>
										<?php $first = false; ?>
									<?php else: ?>
										<option><?php echo esc_html($value['value'])?></option>	
									<?php endif; ?>
								<?php endforeach; ?>
						<?php else: ?>
							<?php if (empty($form_settings->admin_from_name)) :?>
								<option selected value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<option><?php echo esc_html($value['value'])?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<option <?php echo ($value['value']==$form_settings->admin_from_name) ? 'selected' : ''?>><?php echo esc_html($value['value'])?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php else: ?>
						<option selected value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
					<?php endif; ?>
			  	</select>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_admin_message"><?php echo esc_html(__('Text of SMS:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-6 ao-order">
			<div class="ao-col-content">	
				<textarea id="wpcf7_aomailer_admin_message" name="wpcf7_aomailer[admin_message]" cols="100" rows="6" class="large-text code" ><?php echo esc_html(htmlspecialchars(trim($form_settings->admin_message ?? '')))?></textarea>
				<br>
				<small><i><?php echo esc_html(__('CountLetters', 'aomailer_cf'))?> <span class="aomp-count-sms-letters"><?php echo esc_html($sms_admin_info['count'])?></span></i></small>	
				<div>
					<small><i><?php echo esc_html(__('LengthOneSms', 'aomailer_cf'))?> <span class="aomp-length-sms-letters">
						<?php echo esc_html($sms_admin_info['divider'])?>
					</span> <?php echo esc_html(__('LengthOneSmsEng', 'aomailer_cf'))?></i></small>
				</div>	
				<div>
					<small><i><?php echo esc_html(__('SizeMessage', 'aomailer_cf'))?>: <span class="aomp-size-sms-letters"><?php echo esc_html($sms_admin_info['size'])?></span> SMS</i></small>
				</div>
			</div>
		</div>
		<div class="ao-col-3 ao-order">
			<span id="admin_message_tooltip_switch" class="dashicons dashicons-info"></span>
			<div id="admin_message_tooltip" style="position:relative; display:none;">
				<div><?php echo esc_html(__('Use tags if necessary', 'aomailer_cf'))?></div>
				<div><?php echo esc_html(__('New tags are available after form is saved.', 'aomailer_cf'))?></div>
			</div>
			<hr>
			<?php if (!empty($mail_tags)) : ?>
				<?php foreach($mail_tags as $tag) : ?>
					<div class="ao-tags"><b><?php echo esc_html('['.$tag.']')?></b></div><br>
				<?php endforeach; ?>
			<?php endif; ?>			
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
		  	<label for="wpcf7_aomailer_admin_used_translit"><?php echo esc_html(__('Use Translit:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
			  	<input type="checkbox" id="wpcf7_aomailer_admin_used_translit" name="wpcf7_aomailer[admin_used_translit]" class="wide" <?php echo !empty($form_settings->admin_used_translit) ? 'checked' : ''?>>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col">
		  	<h3><?php echo esc_html(__('SMS to users', 'aomailer_cf'))?></h3>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_client_enable"><?php echo esc_html(__('Send SMS to users:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
			  	<input type="checkbox" id="wpcf7_aomailer_client_enable" name="wpcf7_aomailer[client_enable]" class="wide" <?php echo !empty($form_settings->client_enable) ? 'checked' : ''?>>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label><?php echo esc_html(__('User first [tel] tag from CF7 form:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
				<?php if (empty($form_settings->client_number[0])) : ?>
					<span><?php echo esc_html(__('Add a phone field to the form to send a message to the user.', 'aomailer_cf'))?></span>
				<?php else : ?>
					<span><?php echo esc_html($form_settings->client_number[0])?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_client_from_name"><?php echo esc_html(__('From Name:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
			<div class="ao-col-content">
				<select id="wpcf7_aomailer_client_from_name" name="wpcf7_aomailer[client_from_name]" class="wide">
					<?php if (!empty($settings['array_from_name'])) : ?>
						<?php if($form_settings->is_new()): ?>		
							<option value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<?php $first = true; ?>
									<?php if ($first): ?>
										<option selected><?php echo esc_html($value['value'])?></option>
										<?php $first = false; ?>
									<?php else: ?>
										<option><?php echo esc_html($value['value'])?></option>	
									<?php endif; ?>
								<?php endforeach; ?>
						<?php else: ?>
							<?php if (empty($form_settings->client_from_name)) :?>
								<option selected value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<option><?php echo esc_html($value['value'])?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
								<?php foreach($settings['array_from_name'] as $value) : ?>
									<option <?php echo ($value['value']==$form_settings->client_from_name) ? 'selected' : ''?>><?php echo esc_html($value['value'])?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php else: ?>
						<option selected value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
					<?php endif; ?>
			  	</select>
			</div>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
			<label for="wpcf7_aomailer_client_message"><?php echo esc_html(__('Text of SMS:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-6 ao-order">
			<div class="ao-col-content">
				<textarea id="wpcf7_aomailer_client_message" name="wpcf7_aomailer[client_message]" cols="100" rows="6" class="large-text code"><?php echo esc_html(htmlspecialchars(trim($form_settings->client_message ?? '')))?></textarea>
				<br>
				<small><i><?php echo esc_html(__('CountLetters', 'aomailer_cf'))?> <span class="aomp-count-sms-letters"><?php echo esc_html($sms_client_info['count'])?></span></i></small>	
				<div>
					<small><i><?php echo esc_html(__('LengthOneSms', 'aomailer_cf'))?> <span class="aomp-length-sms-letters">
						<?php echo esc_html($sms_client_info['divider'])?>
					</span> <?php echo esc_html(__('LengthOneSmsEng', 'aomailer_cf'))?></i></small>
				</div>	
				<div>
					<small><i><?php echo esc_html(__('SizeMessage', 'aomailer_cf'))?>: <span class="aomp-size-sms-letters"><?php echo esc_html($sms_client_info['size'])?></span> SMS</i></small>
				</div>
			</div>
		</div>
		<div class="ao-col-3 ao-order">
			<span id="client_message_tooltip_switch" class="dashicons dashicons-info"></span>
			<div id="client_message_tooltip" style="position:relative; display:none;">
				<div><?php echo esc_html(__('Use tags if necessary', 'aomailer_cf'))?></div>
				<div><?php echo esc_html(__('New tags are available after form is saved.', 'aomailer_cf'))?></div>
			</div>
			<hr>
			<?php if (!empty($mail_tags)) : ?>
				<?php foreach($mail_tags as $tag) : ?>
					<div class="ao-tags"><b><?php echo esc_html('['.$tag.']')?></b></div><br>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="ao-row">
		<div class="ao-col-3">
				<label for="wpcf7_aomailer_client_used_translit"><?php echo esc_html(__('Use Translit:', 'aomailer_cf'))?></label>
		</div>
		<div class="ao-col-9">
		  	<div class="ao-col-content">
				<input type="checkbox" id="wpcf7_aomailer_client_used_translit" name="wpcf7_aomailer[client_used_translit]" class="wide" <?php echo !empty($form_settings->client_used_translit) ? 'checked' : ''?>>
			</div>
		</div>
	</div>
</div>
