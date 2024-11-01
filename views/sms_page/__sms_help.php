<div class="row">

	<div class="col-sm-12">
						
		<div class="row">
			<div class="col-sm-12">
				<div class="bs-callout bs-callout-danger">
					<h4><?php echo esc_html(__('HelpTitle', 'aomailer_cf'))?></h4>
					<p class="fs-6 text">
						<?php echo esc_html(__('Please, visit our website to find detailed','aomailer_cf'))?>&nbsp;<a class="text-decoration-none" href="<?php echo esc_url($this->settings['instruction_link'])?>" target="_blank"><?php echo esc_html(__('instructions','aomailer_cf'))?></a>&nbsp;<?php echo esc_html(__('or send us your question to','aomailer_cf'))?>&nbsp;<a class="text-decoration-none" href="mailto:<?php echo esc_html($this->settings['targetsms_email'])?>"><?php echo esc_html($this->settings['targetsms_email'])?></a>
					</p>
				</div>
			</div>
		</div>
		
		<div class="row" id="aomp-help-load-content">
			<div class="col-sm-12">
				<h3><?php echo esc_html(__('HelpWarning', 'aomailer_cf'))?></h3>
			</div>
		</div>
	
	</div>

</div>
