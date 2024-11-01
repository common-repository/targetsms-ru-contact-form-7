<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_script('timepicker_min_js', plugins_url('assets/js/jquery-ui-timepicker-addon-1.6.3.min.js', dirname(__FILE__)));
add_action('admin_print_footer_scripts', 'aomailer_cf_script_sms', 99);
function aomailer_cf_script_sms() {
	?>
	<script type="text/javascript">
		function getBalance(token) {
			jQuery.ajax({
				type: "POST",
				url: '<?php echo admin_url()?>admin-ajax.php?action=aomailer_cf_balance_action',
				data: {token: token, type: 'balance'},
				success: function(d){ 
					if (d) {
						jQuery('#aomp-balance').text(d);
						if (d>0) {
							jQuery('#aomp-balance').parents('.alert').removeClass('alert-warning').addClass('alert-success');
						} else {
							jQuery('#aomp-balance').parents('.alert').removeClass('alert-success').addClass('alert-warning');
						}
					}	
				}
			});
		}
		function loadDateTimePickerPlugin() {
			jQuery.datepicker.setDefaults({
				closeText: 'Закрыть',
				prevText: '<Пред',
				nextText: 'След>',
				currentText: 'Сегодня',
				monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
				monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
				dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
				dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
				dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
				weekHeader: 'Нед',
				dateFormat : 'yy-mm-dd',
				firstDay: 1,
				showAnim: 'slideDown',
				isRTL: false,
				showMonthAfterYear: false,
				yearSuffix: '',
			});
			jQuery('#AomailerSmsMailing_date_send, #AomailerSmsHistory_date_start, #AomailerSmsHistory_date_stop').datetimepicker({
				dateFormat : 'yy-mm-dd',
				currentText: 'Сегодня',
				closeText: 'Закрыть',
				timeFormat: 'HH:mm:ss',
				timeOnlyTitle: 'Выберите время',
				timeText: 'Время',
				hourText: 'Часы',
				minuteText: 'Минуты',
				secondText: 'Секунды',
				timeSuffix: ''
			});	
		}
		jQuery(document).ready(function($) {
			var token = '<?php echo esc_html(wp_create_nonce())?>';
			setTimeout(function(){jQuery('.message_output').fadeOut('slow')}, 10000);
			setInterval(function(){getBalance(token)}, 10000);
		});
	</script>
<?php } ?>
<div id="aomp-page-sms" class="container-fluid">
	<div id="aomp-overlay"></div>
	<i id="aomp-order-loader-admin" class="fa fa-cog fa-spin fa-fw"></i>
	<div class="masthead">
		<h3 class="text-muted mb-4">
			<img src="<?php echo esc_html($this->settings['logo'])?>" alt="logo" class="" width="200"><br><br>
			<?php echo esc_html(__('TargetSMS.ru: SMS from Contact Form 7', 'aomailer_cf'))?>
		</h3>
    </div>
	<div class="row">
		<div class="col-sm-3">
			<div class="alert alert-<?php echo !empty($this->settings['balance']) ? 'success' : 'warning'?>" style="display:table">
				<i class="fa fa-balance-scale" aria-hidden="true"></i> 
				<?php echo esc_html(__('Balance', 'aomailer_cf'))?>:
				<span id="aomp-balance"><?=!empty($this->settings['balance']) ? AomailerCFSMSApi::aomp()->getFormat($this->settings['balance'], 'money') : '0'?></span>
				<?php if (!empty($this->settings['currency'])) : ?>
					<?php if ($this->settings['currency']=='RUR') : ?>
						&nbsp;<i class="fa fa-rub" aria-hidden="true"></i>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>	
		<div class="col-sm-9 text-sm-end">
			<?php if (!empty($this->settings['personal_account_link'])) : ?>
				<a href="<?php echo esc_url($this->settings['personal_account_link'])?>" target="_blank" class="btn btn-info btn-xs-block mr-xs-1">
					<?php echo esc_html(__('PersonalAccount', 'aomailer_cf'))?>
				</a>
			<?php endif; ?>
			<?php if (!empty($this->settings['balance_link'])) : ?>
				<a href="<?php echo esc_url($this->settings['balance_link'])?>" target="_blank" class="btn btn-success btn-xs-block">
					<?php echo esc_html(__('AddBalance', 'aomailer_cf'))?>
				</a>
			<?php endif; ?>
		</div>
	</div>
	<p class="clearfix"></p>
	<div class="row">
		<div class="col-sm-12">
			<ul class="nav nav-tabs" id="aomp-settings-tab">
				<li class="nav-item mb-0"?>
					<a class="nav-link active" href="#aomp-smssettings" data-toggle="tab">
						<i class="fa fa-cogs" aria-hidden="true"></i> <?php echo esc_html(__('Settings', 'aomailer_cf'))?>
					</a>
				</li>
				<li class="nav-item mb-0">
					<a class="nav-link" href="#aomp-smshistory" data-toggle="tab">
						<i class="fa fa-history" aria-hidden="true"></i> <?php echo(__('History', 'aomailer_cf'))?>
					</a>
				</li>
				<li class="nav-item mb-0">
					<a class="nav-link" href="#aomp-smshelp" data-toggle="tab">
						<i class="fa fa-question-circle-o" aria-hidden="true"></i> <?php echo esc_html(__('Instructions and Support', 'aomailer_cf'))?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="tab-content">
		<div class="tab-pane fade in active show" id="aomp-smssettings">
			<?php require_once __DIR__ . '/sms_page/__sms_settings.php'; ?>
		</div>
		<div class="tab-pane fade" id="aomp-smshistory">
			<?php require_once __DIR__ . '/sms_page/__sms_history.php'; ?>
		</div>
		<div class="tab-pane fade" id="aomp-smshelp">
			<?php require_once __DIR__ . '/sms_page/__sms_help.php'; ?>
		</div>
	</div>
</div>

