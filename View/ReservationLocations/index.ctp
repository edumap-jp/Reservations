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

		<?php if ($reservationLocations) : ?>
			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
					<tr>
						<th colspan="2">
							<?php echo $this->Paginator->sort('location_name', __d('reservations', 'Location name')); ?>
						</th>
						<?php if ($categories) : ?>
							<th>
								<?php echo $this->Paginator->sort('CategoryOrder.weight', __d('categories', 'Category')); ?>
							</th>
						<?php endif; ?>
						<th>
							<?php echo __d('reservations', 'Available datetime') ?>
						</th>
					</tr>
					</thead>
					<?php foreach ($reservationLocations as $reservationLocation): ?>
						<tr>
							<td>
								<?php echo h($reservationLocation['ReservationLocation']['location_name']); ?>
							</td>
							<td>
								<?php
									echo $this->LinkButton->edit(
										null,
										['action' => 'edit', 'key' => $reservationLocation['ReservationLocation']['key']],
										['iconSize' => 'btn-xs']
									);
								?>
							</td>
							<?php if ($categories) : ?>
								<td>
									<?php echo h($reservationLocation['CategoriesLanguage']['name']); ?>
								</td>
							<?php endif; ?>
							<td>
								<?php echo h($reservationLocation['ReservationLocation']['openText']); ?>
							</td>
						</tr>
					<?php endforeach;?>
				</table>
			</div>
		<?php else: ?>
			<?php echo __d('reservations', 'No institution yet registered.'); ?>
		<?php endif; ?>
    </div>
</article>
