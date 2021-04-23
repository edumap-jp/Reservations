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
<div class="col-xs-12 col-sm-12">
<?php
if (isset($mailSettingInfo['MailSetting']['is_mail_send']) &&
		$mailSettingInfo['MailSetting']['is_mail_send']) {
	echo $this->NetCommonsForm->inlineCheckbox('ReservationActionPlan.enable_email', ['label' =>
	__d(
		'reservations',
		'Inform other members by e-mail?'
	)]);
} else {
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.enable_email', array('value' => false));
}

echo $this->NetCommonsForm->hidden('ReservationActionPlan.email_send_timing', array('value' => 5));
?>
</div>
