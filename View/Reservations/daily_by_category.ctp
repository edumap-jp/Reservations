<?php
/**
 * カテゴリー別 - 日表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->element('Reservations.scripts');
echo $this->NetCommonsHtml->script(array(
		'/reservations/js/reservations_timeline.js',
));

?>

<article ng-controller="ReservationsDetailEdit" class="block-setting-body">
	<?php
		//共通タブ(カテゴリー別、施設別)
		echo $this->element('Reservations.Reservations/common_tabs');
	?>
	<?php
		//カテゴリー別の表示方法タブ(週、日)
		echo $this->element('Reservations.Reservations/tabs_by_category');
	?>

	<div class="clearfix">
		<?php echo $this->ReservationTurnReservation->getTurnReservationOperations('day', 'top', $vars); ?>

		<div class="reservation-category-operations reservation-category-operations-top pull-left">
			<?php echo $this->element('Reservations.Reservations/dropdown_category'); ?>
		</div>
	</div>

	<div class="clearfix reservation-daily-locations-table">
		<table class="pull-left reservation-row-head">
			<thead>
				<tr>
					<td></td>
				</tr>
				<?php if (Current::read('ReservationFrameSetting.display_timeframe')): ?>
					<tr>
						<td></td>
					</tr>
				<?php endif ?>
			</thead>

			<tbody>
				<?php foreach ($locations as $location) : ?>
					<tr>
						<td>
							<div>
								<?php echo h($location['ReservationLocation']['location_name']); ?>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php
		$startTime = $vars['ReservationFrameSetting']['timeline_base_time'];
		if ($vars['ReservationFrameSetting']['display_start_time_type'] == 0) {
			// 表示開始時刻可変のときは、現在時刻より1時間前の「時間」　ex 16:30 -> 15:30 -> 15
			$ncTime = new NetCommonsTime();
			$userNow = $ncTime->toUserDatetime(NetCommonsTime::getNowDatetime());
			$startTime = date('G', strtotime($userNow) - 60 * 60);
		}
		?>
		<div ng-controller="ReservationsHorizonTimeline"
				class="text-center table-responsive pull-right reservation-horizon-timeline"
				data-daily-start-time-idx="<?php echo $startTime ?>">
			<table class="reservation-row-data">
				<thead>
					<?php if (Current::read('ReservationFrameSetting.display_timeframe')): ?>
						<tr>
							<td style="padding: 0px">
								<?php
								// 時間枠を出力
								$displayTimeframes = [];
								foreach ($timeframes as $timeframe) {
									// 時間枠情報をUTCからユーザタイムゾーンに変換。
									$ncTime = new NetCommonsTime();
									$start = $ncTime->toUserDatetime($timeframe['ReservationTimeframe']['start_time']);
									$end = $ncTime->toUserDatetime($timeframe['ReservationTimeframe']['end_time']);
									$start = date('H:i', strtotime($start));
									$end = date('H:i', strtotime($end));
									if ($start > $end) {
										// Start > Endなら24時またぎなので2つの時間枠にわける
										$displayTimeframes[] = [
											'start' => $start,
											'end' => '24:00',
											'color' => $timeframe['ReservationTimeframe']['color'],
											'title' => $timeframe['ReservationTimeframe']['title'],
										];
										$displayTimeframes[] = [
											'start' => '00:00',
											'end' => $end,
											'color' => $timeframe['ReservationTimeframe']['color'],
											'title' => $timeframe['ReservationTimeframe']['title'],
										];
									} else {
										$displayTimeframes[] = [
											'start' => $start,
											'end' => $end,
											'color' => $timeframe['ReservationTimeframe']['color'],
											'title' => $timeframe['ReservationTimeframe']['title'],
										];
									}
								}
								?>
								<div ng-controller="ReservationsHorizontalTimeframe"
										ng-init="init(<?php echo h(json_encode(['timeframes' => $displayTimeframes]))
										?>)"
										style="position: relative;height: 100%;margin-top:0px">
									<div ng-repeat="timeframe in data.timeframes"
											ng-style="timeframe.style"

											class="reservation-horizontal-timeframe"
									>
										{{timeframe.title}}
									</div>
								</div>

							</td>
							<?php for($i = 1; $i < 24; $i++): ?>
								<td></td>
							<?php endfor;?>
						</tr>
					<?php endif ?>

				<?php // 時刻と予約の＋ボタン表示 ?>
					<?php echo $this->ReservationDailyTimeline->makeDailyTimlineHeaderHtml($vars); ?>
				</thead>

				<tbody>
					<?php foreach ($locations as $location) : ?>
					<?php
						echo $this->element('Reservations.Reservations/daily_timeline_element',
							array(
								'location' => $location,
							)
						);
					?>

					<?php endforeach; ?>
					<?php //echo $this->ReservationDailyTimeline->makeDailyTimlineBodyHtml($vars); ?>
					<?php //$reservationLinePlans = $this->ReservationWeekly->getLineData() ?>
				</tbody>
			</table>
		</div>
	</div>
</article>
