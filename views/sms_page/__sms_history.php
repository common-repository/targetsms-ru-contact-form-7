<?php
add_action('admin_print_footer_scripts', 'aomailer_cf_sms_page_history', 99);
function aomailer_cf_sms_page_history() {
	?>
	<script type="text/javascript">
		var token = '<?php echo esc_html(wp_create_nonce())?>';
		jQuery(document).ready(function($) {
			jQuery(document).delegate('#sms-history-form', 'submit', function(event){
				var form_data = {};
				form_data.token = token;
				form_data.page_type = 'sms';
				form_data.AomailerSmsHistory = {};
				if ((typeof event.currentTarget === "object") && (event.currentTarget !== null)) {
					jQuery.each(event.currentTarget, function(index, value){
						if (value.type=='submit') {
							return false;
						}
							
						var name = value.name.replace(/.*\[|\]/gi,'');
						if (value.type=='checkbox' || value.type=='radio') {
							form_data.AomailerSmsHistory[name] = value.checked;
						} else {
							form_data.AomailerSmsHistory[name] = value.value;
						}
					});
				}
					
				reloadForm('smshistory', token, form_data, '<?php echo esc_html(admin_url())?>');
				event.preventDefault();
			});	
		});
	</script>
<?php } ?>

<div class="row message_output">
	<div class="col-sm-12">
	
		<?php if (!empty($this->settings['error'])) : ?>
				
			<p class="alert alert-danger fs-6 text">
				<?php echo esc_html(strip_tags($this->settings['error']))?>
			</p>
				
		<?php elseif (!empty($this->settings['success'])) : ?>
			
			<p class="alert alert-success fs-6 text">
				<?php echo esc_html(strip_tags($this->settings['success']))?>
			</p>
			
		<?php endif; ?>

	</div>
</div>

<div class="row">

	<div class="col-sm-12">
	
		<div class="row">
			<div class="col-sm-12">
				<div class="bs-callout bs-callout-danger">
					<h4><?php echo esc_html(__('HistoryAdministrator', 'aomailer_cf'))?></h4>
					<p><?php echo esc_html(__('HistoryAdministratorWarning', 'aomailer_cf'))?></p>
				</div>
			</div>
		</div>
		
		<div class="row" id="aomp-history-load-content">
			<div class="col-sm-12">
		
			<div class="table-responsive">
				<table class="table table-striped table-hover table-condensed">
					<thead>
						<tr>
							<th><?php echo esc_html(__('Number', 'aomailer_cf'))?></th>
							<th><?php echo esc_html(__('Operator', 'aomailer_cf'))?></th>
							<th><?php echo esc_html(__('Status', 'aomailer_cf'))?></th>
							<th><?php echo esc_html(__('Send time', 'aomailer_cf'))?></th>
							<th><?php echo esc_html(__('Price', 'aomailer_cf'))?></th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($this->history['stat'])) : ?>
							<tr>
								<td>0</td>
								<td></td>
								<td></td>
								<td>0000-00-00 00:00:00</td>
								<td>0.0</td>
							</tr>
						<?php else : ?>
						
							<?php foreach ($this->history['stat'] as $value) : ?>
								
								<tr>
									<td>+<?php echo esc_html($value['phone'])?></td>
									<td><?php echo esc_html($value['operator'])?></td>
									<td><?php echo esc_html($value['status_title'])?></td>
									<td><?php echo esc_html($value['time'])?></td>
									<td>
										
										<?php echo esc_html($value['price'])?> 
										<?php if (!empty($this->settings['currency'])) : ?>
				
											<?php if ($this->settings['currency']=='RUR') : ?>
											
												&nbsp;<i class="fa fa-rub" aria-hidden="true"></i>
												
											<?php endif; ?>
											
										<?php endif; ?>
									
									</td>
								</tr>
		
							<?php endforeach; ?>

						<?php endif; ?>

					</tbody>
				</table>
			</div>
		
		
			</div>
		</div>
		
		<p>&nbsp;</p>
		
		<div class="col-sm-12">
			<form id="sms-history-form" action="" method="post" role="form">
	
				<div class="row gy-3">
					<div class="col-sm-2 text-sm-end">
						<label for="AomailerSmsHistory_phone" class="control-label fw-bold"><?php echo esc_html(__('Phone', 'aomailer_cf'))?></label>
					</div>
					<div class="col-sm-10">
						<input type="text" class="form-control" name="AomailerSmsHistory[phone]" id="AomailerSmsHistory_phone" value="<?php echo !empty($_POST['data']['AomailerSmsHistory']['phone']) ? esc_html($_POST['data']['AomailerSmsHistory']['phone']) : ''?>">
					</div>
					<div class="col-sm-2 text-sm-end">
						<label class="control-label fw-bold"><?php echo esc_html(__('Period', 'aomailer_cf'))?></label>
					</div>	
					<div class="col-sm-10">
						 <div class="input-group">
							<span class="input-group-text">
								<?php echo esc_html(__('from', 'aomailer_cf'))?>
							</span>
							<input type="datetime" class="form-control" name="AomailerSmsHistory[date_start]" id="AomailerSmsHistory_date_start" class="datepicker" value="<?php echo !empty($_POST['data']['AomailerSmsHistory']['date_start']) ? esc_html($_POST['data']['AomailerSmsHistory']['date_start']) : ''?>" autocomplete="off">
							<span class="input-group-text">
								<?php echo esc_html(__('to', 'aomailer_cf'))?>
							</span>
							<input type="datetime" class="form-control" name="AomailerSmsHistory[date_stop]" id="AomailerSmsHistory_date_stop" class="datepicker" value="<?php echo !empty($_POST['data']['AomailerSmsHistory']['date_stop']) ? esc_html($_POST['data']['AomailerSmsHistory']['date_stop']) : ''?>" autocomplete="off">
						</div>
					</div>
				
				<?php if (!empty($this->settings['array_from_name'])) : ?>
					<div class="col-sm-2 text-sm-end">
						<label for="AomailerSmsHistory_from_name" class="control-label fw-bold"><?php echo esc_html(__('FromName', 'aomailer_cf'))?></label>
					</div>
					<div class="col-sm-10">
						<select name="AomailerSmsHistory[originator]" class="form-control" id="AomailerSmsHistory_from_name">
							<option value=""><?php echo esc_html(__('Select', 'aomailer_cf'))?></option>
							<?php if (!empty($this->settings['array_from_name'])) : ?>
								<?php foreach ($this->settings['array_from_name'] as $value) : ?>
									<option <?php echo (!empty($_POST['data']['AomailerSmsHistory']['originator']) && $value['value']==$_POST['data']['AomailerSmsHistory']['originator']) ? 'selected' : ''?> value="<?php echo esc_html($value['value'])?>"><?php echo esc_html($value['value'])?></option>
								<?php endforeach; ?>
							<?php endif ?>
						</select>
					</div>
				<?php endif; ?>
				
					<div class="col-sm-12">
						<input type="submit" class="btn btn-primary pull-right btn-xs-block floating_button" value="<?php echo esc_html(__('btnSearch', 'aomailer_cf'))?>">
					</div>
				</div>
				
			</form>
		
		</div>
	</div>

</div>
