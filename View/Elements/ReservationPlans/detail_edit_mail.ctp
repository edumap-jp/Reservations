<?php
/**
 * 予定編集（メール通知設定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$checkMailStyle = '';
	if (!isset($mailSettingInfo['MailSetting']['is_mail_send']) ||
		$mailSettingInfo['MailSetting']['is_mail_send'] == 0) {
		$checkMailStyle = "style='display: none;'";
	}
?>
<div class="col-xs-12 col-sm-12">
	<?php
	echo $this->NetCommonsForm->inlineCheckbox('ReservationActionPlan.enable_email', ['label' =>
	__d(
		'reservations',
		'Inform other members by e-mail?'
	)]);

	//echo $this->NetCommonsForm->hidden('ReservationActionPlan.enable_email', array('value' => false));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.email_send_timing', array('value' => 5));
	?>

</div>
<!--
<div class="form-group" data-reservation-name="checkMail" <?php echo $checkMailStyle; ?>>
	<div class="col-xs-12">
		<br />
		<?php
			/*echo $this->NetCommonsForm->label('', __d('reservations', 'Notify by e-mail'));*/
		?>
		<div class="form-inline">
			<?php /*
				echo $this->NetCommonsForm->checkbox('ReservationActionPlan.enable_email', array(
					'checked' => ($this->request->data['ReservationActionPlan']['enable_email']) ? true : false,
					'label' => __d('reservations', 'e-mail notification before event'),
				));*/
			?>
			<?php
				$options = array(
					'5' => __d('reservations', 'Event 5 minutes ago'),
					'10' => __d('reservations', 'Event 10 minutes ago'),
					'15' => __d('reservations', 'Event 15 minutes ago'),
					'20' => __d('reservations', 'Event 20 minutes ago'),
					'25' => __d('reservations', 'Event 25 minutes ago'),
					'30' => __d('reservations', 'Event 30 minutes ago'),
					'45' => __d('reservations', 'Event 45 minutes ago'),
					'60' => __d('reservations', 'Event 1 hour ago'),
					'120' => __d('reservations', 'Event 2 hours ago'),
					'180' => __d('reservations', 'Event 3 hours ago'),
					'720' => __d('reservations', 'Event 12 hours ago'),
					'1440' => __d('reservations', 'Event 24 hours ago'),
					'2880' => __d('reservations', 'Event 2 days ago'),
					'8540' => __d('reservations', 'Event 1 week ago'),
				);
			?>
			<?php /*
				echo $this->NetCommonsForm->select('ReservationActionPlan.email_send_timing', $options, array(
					'value' => $this->request->data['ReservationActionPlan']['email_send_timing'], //valueは初期値
					'class' => 'form-control',
					'empty' => false,
					'required' => true,
				)); */
			?>
		</div>
	</div>
</div>
-->
