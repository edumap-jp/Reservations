<article class="block-setting-body">

	<?php //echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_FRAME_SETTING); ?>
	<?php echo $this->BlockTabs->main('location_settings'); ?>

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
                    <th><?php echo $this->Paginator->sort('category_id', __d('reservations', 'Category')); ?></th>
                    <th colspan="2"><?php echo $this->Paginator->sort('location_name', __d('reservations', 'Location name')); ?></th>
                    <!--<th>--><?php //echo $this->Paginator->sort('add_authority'); ?><!--</th>-->
                    <th><?php __d('reservations', '利用可能日時') ?></th>
                    <!--<th>--><?php //echo $this->Paginator->sort('use_private'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('use_auth_flag'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('use_all_rooms'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('display_sequence'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('created_user'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('created'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('modified_user'); ?><!--</th>-->
                    <!--<th>--><?php //echo $this->Paginator->sort('modified'); ?><!--</th>-->

                </tr>
                </thead>
                <?php foreach ($reservationLocations as $reservationLocation): ?>
                    <tr>
                        <td>
                            <?php echo $reservationLocation['CategoriesLanguage']['name'] ?>
                        </td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['location_name']); ?>&nbsp;</td>
                        <td>
                            <?php
								echo $this->LinkButton->edit(
									null,
									[
										'action' => 'edit',
										'key' => $reservationLocation['ReservationLocation']['key']
									],
									[
										'iconSize' => 'btn-xs'
									]
								);
							?>
                        </td>
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['add_authority']); ?><!--&nbsp;</td>-->
                        <td>
							<?php echo $this->ReservationLocation->openText($reservationLocation); ?>
                        </td>
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['use_private']); ?><!--&nbsp;</td>-->
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['use_auth_flag']); ?><!--&nbsp;</td>-->
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['use_all_rooms']); ?><!--&nbsp;</td>-->
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['display_sequence']); ?><!--&nbsp;</td>-->
                        <!--<td>-->
							<?php //echo $this->Html->link($reservationLocation['TrackableCreator']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableCreator']['id'])); ?>
                        <!--</td>-->
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['created']); ?><!--&nbsp;</td>-->
                        <!--<td>-->
							<?php //echo $this->Html->link($reservationLocation['TrackableUpdater']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableUpdater']['id'])); ?>
                        <!--</td>-->
                        <!--<td>--><?php //echo h($reservationLocation['ReservationLocation']['modified']); ?><!--&nbsp;</td>-->
                    </tr>
                <?php endforeach;?>
            </table>

        </div>

    </div><!--end tab-content-->
</article>