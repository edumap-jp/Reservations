<?php
/**
 * 施設設定 > 施設一覧
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationSettingsComponent', 'Reservations.Controller/Component');
?>

<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(ReservationSettingsComponent::MAIN_TAB_LOCATION_SETTING); ?>

    <div class="tab-content">
        <div class="text-right nc-table-add">
			<?php
				//並び替え
				if (count($reservationLocations) > 1) {
					$params = ['controller' => 'reservation_locations', 'action' => 'sort'];
					$options = [];
				} else {
					$params = '#';
					$options = ['disabled' => true];
				}
				echo $this->LinkButton->sort('', $params, $options);
			?>
			<?php
				//追加
				echo $this->LinkButton->add(
					__d('net_commons', 'Add'),
					['action' => 'add', 'frame_id' => Current::read('Frame.id')]
				);
			?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>
						<?php echo $this->Paginator->sort('CategoryOrder.weight', __d('categories', 'Category')); ?>
					</th>
                    <th colspan="2">
						<?php echo $this->Paginator->sort('location_name', __d('reservations', 'Location name')); ?>
					</th>
                    <th><?php __d('reservations', 'Available datetim') ?></th>
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
