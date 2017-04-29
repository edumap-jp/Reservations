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

$urlParams = array(
	'controller' => 'reservation_mail_settings',
	'action' => 'edit',
	'?' => array(
		'frame_id' => Current::read('Frame.id'),
	)
);
?>
<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_MAIL_SETTING); ?>

	<div class="tab-content">

		<div class="form-group">
			<div class="well well-sm">
				<?php
					echo __d(
						'reservations',
						'Please set from Select the room for which you want e-mail notification settings. It will be the setting of one room.'
					);
				?>
			</div>
			<label><?php echo __d('reservations', 'Target room'); ?></label>
			<span class="btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<?php echo h($mailRooms[Current::read('Room.id')]); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					<?php foreach ($mailRooms as $key => $name) : ?>
						<?php
							if ($key == Current::read('Room.id')) {
								$active = ' class="active"';
							} else {
								$active = '';
							}
						?>
						<li<?php echo $active; ?>>
							<?php echo $this->NetCommonsHtml->link($name,
								Hash::merge($urlParams, array('?' => array('room' => $key)))
							); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</span>
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

