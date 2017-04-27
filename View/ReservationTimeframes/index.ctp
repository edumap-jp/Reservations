<?php
/**
 * 時間枠設定 > 時間枠登録
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingTabComponent', 'Reservations.Controller/Component');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingTabComponent::MAIN_TAB_TIMEFRAME_SETTING); ?>

    <div class="tab-content">
		<?php /* 施設予約にはBLOCK_TAB_SETTINGは無し */ ?>

		<?php //echo $this->element('Blocks.edit_form', array(
		//	'model' => 'ReservationFrameSetting',
		//	'callback' => 'Reservations.ReservationFrameSettings/edit_form',
		//	'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
		//)); ?>

        <div class="text-right nc-table-add">
            <?php echo $this->LinkButton->add(__d('net_commons', 'Add'), ['action' => 'add', 'frame_id' => Current::read('Frame.id')]); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th colspan="2"><?php echo __d('reservations', 'Time frame name') ?></th>
                    <th><?php echo __d('reservations', 'Time frame range') ?></th>
                    <th><?php echo __d('reservations', 'Time frame color') ?></th>
                </tr>
                </thead>
                <?php foreach ($reservationTimeframes as $reservationTimeframe): ?>
                    <tr>
                        <td>
							<?php echo h($reservationTimeframe['ReservationTimeframe']['title']); ?>
						</td>
                        <td>
                            <?php
								echo $this->LinkButton->edit(
									null,
									[
										'action' => 'edit',
										'key' => $reservationTimeframe['ReservationTimeframe']['key']
									],
									[
										'iconSize' => 'btn-xs'
									]
								);
							?>
                        </td>
						<td>
							<?php echo __d('reservations', '%s - %s',
									h($reservationTimeframe['ReservationTimeframe']['start_time']),
									h($reservationTimeframe['ReservationTimeframe']['end_time'])
									); ?>
						</td>
						<td>
							<div style="background-color:<?php echo h($reservationTimeframe['ReservationTimeframe']['color'])?>">&nbsp;</div>
						</td>
                    </tr>
                <?php endforeach;?>
            </table>

        </div>

    </div><!--end tab-content-->
</article>
