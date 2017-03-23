<?php
/**
 * 予定詳細 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Reservations.scripts');
?>

<article ng-controller="ReservationsDetailEdit" class="block-setting-body">

	<header class="clearfix">
		<div class="pull-left">
			<?php
				$urlOptions = array(
					'controller' => 'reservations',
					'action' => 'index',
					'frame_id' => Current::read('Frame.id'),
					'?' => array(
						'year' => $vars['year'],
						'month' => $vars['month'],
					)
				);
				if (isset($vars['returnUrl'])) {
					$cancelUrl = $vars['returnUrl'];
				} else {
					$cancelUrl = $this->ReservationUrl->getReservationUrl($urlOptions);
				}
				echo $this->LinkButton->toList(null, $cancelUrl);
			?>
		</div>

		<div class="pull-right">
			<?php echo $this->ReservationButton->getEditButton($vars, $event);?>
		</div>
	</header>

	<?php /* ステータス＆タイトル */ ?>
	<h1 data-reservation-name="dispTitle">
		<?php echo $this->ReservationCommon->makeWorkFlowLabel($event['ReservationEvent']['status']); ?>
		<?php echo $this->TitleIcon->titleIcon($event['ReservationEvent']['title_icon']); ?>
		<?php echo h($event['ReservationEvent']['title']); ?>
	</h1>

	<div class="row">

		<div class="col-xs-12">
            <div>
                <h3><?php echo __d('reservations', '施設'); ?></h3>
                <p><?php echo h($event['ReservationLocation']['location_name']); ?></p>
            </div>

            <?php /* 日時 */ ?>
			<div data-reservation-name="showDatetime" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Date and time'); ?></h3>
				<p>
					<?php
					$startUserDateWdayTime = $this->ReservationPlan->makeDatetimeWithUserSiteTz($event['ReservationEvent']['dtstart'], $event['ReservationEvent']['is_allday']);
					echo h($startUserDateWdayTime);
					?>
					<?php
					if (! $event['ReservationEvent']['is_allday']) {
						echo '&nbsp&nbsp' . __d('reservations', ' - ') . '&nbsp&nbsp';
						$endUserDateWdayTime = $this->ReservationPlan->makeDatetimeWithUserSiteTz($event['ReservationEvent']['dtend'], $event['ReservationEvent']['is_allday']);
						echo h($endUserDateWdayTime);
					}
					?>
				</p>
			</div>

			<?php /* 繰り返し予定 */ ?>
			<?php $rrule = $this->ReservationPlanRrule->getStringRrule($event['ReservationRrule']['rrule']); ?>
			<?php if ($rrule !== '') : ?>
			<div data-reservation-name="repeat">
				<label><?php echo __d('reservations', 'Repeat the event:'); ?></label>
				<?php /* getStringRrule()で表示するものは直接入力値はつかわない。よってh()は不要 */ ?>
				<span><?php echo $this->ReservationPlanRrule->getStringRrule($event['ReservationRrule']['rrule']); ?></span>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php /* 公開対象 */ ?>
			<div data-reservation-name="dispRoomForOpen" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Category'); ?></h3>
				<p><?php echo $this->ReservationCategory->getCategoryName($vars, $event); ?></p>
			</div><!-- おわり-->

			<?php /* 共有者 */ ?>
			<?php if ($this->ReservationShareUsers->isShareEvent($event)): ?>
			<div data-reservation-name="sharePersons" class="reservation-eachplan-box">
				<h3><?php echo $this->ReservationShareUsers->getReservationShareUserTitle($vars, $event, $shareUserInfos); ?></h3>
				<p><?php echo $this->ReservationShareUsers->getReservationShareUser($vars, $event, $shareUserInfos); ?></p>
			</div>
			<?php endif; ?>

			<?php if ($event['ReservationEvent']['location'] !== '') : ?>
			<div data-reservation-name="showLocation" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Location'); ?></h3>
				<p><?php echo h($event['ReservationEvent']['location']); ?></p>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php if ($event['ReservationEvent']['contact'] !== '') : ?>
			<div data-reservation-name="showContact" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Contact'); ?></h3>
				<p><?php echo h($event['ReservationEvent']['contact']); ?></p>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php if ($event['ReservationEvent']['description'] !== '') : ?>
			<div data-reservation-name="description" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Details'); ?></h3>
				<?php /* ここにwysiwyigの内容がきます wysiwygの内容は下手にPタグでくくれない */ ?>
				<?php echo $event['ReservationEvent']['description']; ?>
			</div><!-- おわり-->
			<?php endif; ?>

			<div data-reservation-name="writer" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Author'); ?></h3>
				<p><?php echo $this->DisplayUser->handleLink($event, array('avatar' => true)); ?></p>
			</div><!-- おわり-->

			<div data-reservation-name="updateDate" class="reservation-eachplan-box">
				<h3><?php echo __d('reservations', 'Date'); ?></h3>
				<p><?php echo h((new NetCommonsTime())->toUserDatetime($event['ReservationEvent']['modified'])); ?></p>
			</div><!-- おわり-->
		</div>
	</div>
</article>
