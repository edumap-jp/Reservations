<article class="block-setting-body">

	<?php //echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_FRAME_SETTING); ?>
	<?php echo $this->BlockTabs->main('timeframe_settings'); ?>

    <div class="tab-content">
		<?php /* 施設予約にはBLOCK_TAB_SETTINGは無し */ ?>

		<?php //echo $this->element('Blocks.edit_form', array(
		//	'model' => 'ReservationFrameSetting',
		//	'callback' => 'Reservations.ReservationFrameSettings/edit_form',
		//	'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
		//)); ?>

        <div class="text-right nc-table-add">
            <?php echo $this->LinkButton->add(__d('reservations', '追加'), ['action' => 'add', 'frame_id' => Current::read('Frame.id')]); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th colspan="2"><?php echo __d('reservations', '時間枠名') ?></th>
                    <th><?php echo __d('reservations', '時間範囲') ?></th>
                    <th><?php echo __d('reservations', '色') ?></th>
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
							<?php echo __d('reservations', '%s 〜 %s',
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
