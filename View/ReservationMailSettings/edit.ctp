<?php
/**
 * メール設定
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @author Allcreator <info@allcreator.net>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_MAIL_SETTING); ?>

	<div class="tab-content">
		<div class="well well-sm">
			<?php
				echo __d(
					'reservations',
					'This mail setting is mail setting common to all facilities. If you select "Use mail notification function", ' .
						'select whether or not to actually mail notification at reservation registration. ' .
						'In addition, the right to receive notifications is the setting of which privileges to notify of e-mails ' .
						'if you specify a room to be "published".'
				);
			?>
		</div>

		<?php echo $this->MailForm->editFrom(
			array(
				array(
					'mailBodyPopoverMessage' => __d('reservations', 'MailSetting.mail_fixed_phrase_body.popover'),
				),
			),
			NetCommonsUrl::backToPageUrl(true)
		); ?>
	</div>
</article>

