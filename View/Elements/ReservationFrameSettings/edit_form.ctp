<?php
/**
 * reservation frame setting form view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<?php
//
//以下の項目は画面からの入力項目にないので、(値省略型)hiddenで指定する必要あり。
//hidden指定しないと、BlackHole行きとなる。
//逆に、画面からの入力項目化したら、ここのhiddenから外すこと。
//
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.id');
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.frame_key');
echo $this->NetCommonsForm->hidden('Frame.id');
echo $this->NetCommonsForm->hidden('Frame.key');
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.room_id');

$displayType = $this->request->data['ReservationFrameSetting']['display_type'];
?>

<?php /* 表示方法 */ ?>
<div class="form-group">
	<?php
		echo $this->NetCommonsForm->input(
			'ReservationFrameSetting.display_type',
			array(
				'type' => 'select',
				'label' => __d('reservations', 'Display type'),
				'div' => false,
				'options' => $displayTypeOptions,
				'selected' => $this->request->data['ReservationFrameSetting']['display_type'],
				'data-reservation-frame-id' => Current::read('Frame.id'),
				'ng-model' => 'data.reservationFrameSetting.displayType',
				'ng-change' => 'displayChange()',
			)
		);
	?>
</div>

<?php /* 最初に表示するカテゴリ選択 */ ?>
<div class="form-group row" ng-show="isShowSelectCategory"
		ng-controller="ReservationFrameSettings.selectLocation"
		ng-init="initialize(<?php echo h(json_encode(['locations' => $locations])); ?>)">

	<div class="col-xs-11 col-xs-offset-1">
		<?php
			//カテゴリ絞り込み
			$locationCategories = Hash::combine(
				$categories,
				'{n}.Category.id',
				'{n}.CategoriesLanguage.name'
			);
			//施設の絞り込み, カテゴリなし　を追加
			$locationCategories = Hash::merge(
				[
					'' => __d('reservations', 'Display all'),
				],
				$locationCategories
			);
			echo $this->NetCommonsForm->input(
				'ReservationFrameSetting.category_id',
				[
					'label' => __d('reservations', 'Default category'),
					'options' => $locationCategories,
					'div' => false,
				]
			);
		?>
	</div>
</div>

<?php /* 最初に表示する施設選択 */ ?>
<div class="form-group row" ng-show="isShowSelectLocation"
		ng-controller="ReservationFrameSettings.selectLocation"
		ng-init="initialize(<?php echo h(json_encode(['locations' => $locations])); ?>)">

	<div class="col-xs-11 col-xs-offset-1 reservation-select-location">
		<?php
			//カテゴリ絞り込み
			$locationCategories = Hash::combine(
				$categories,
				'{n}.Category.id',
				'{n}.CategoriesLanguage.name'
			);
			//施設の絞り込み, カテゴリなし を追加
			$locationCategories = Hash::merge(
				[
					'all' => __d('reservations', 'Display all'),
					'' => __d('reservations', 'no category')
				],
				$locationCategories
			);

			echo $this->NetCommonsForm->label(
				'ReservationFrameSetting.location_key',
				__d('reservations', 'Default institution')
			);

			//カテゴリー絞り込み
			echo $this->NetCommonsForm->input(
				'ReservationLocation.filterByCategoryId',
				[
					'label' => false,
					'options' => $locationCategories,
					'ng-change' => 'selectLocationCategory()',
					'ng-model' => 'locationCategory',
				]
			);

			//施設の選択
			$locationOptions = Hash::combine(
				$locations,
				'{n}.ReservationLocation.key',
				'{n}.ReservationLocation.location_name'
			);
			echo $this->NetCommonsForm->input(
				'ReservationFrameSetting.location_key',
				[
					'label' => false,
					'type' => 'select',
					'ng-options' => 'location.ReservationLocation.location_name for location in locationOptions track by location.ReservationLocation.key',
					'ng-init' => 'setLocationKey(\'' . $this->request->data['ReservationFrameSetting']['location_key'] . '\')',
					'ng-model' => 'selectLocation',
					// optionsを指定しないとSecurityComponentでBlackHole送りになる
					'options' => $locationOptions,
					'ng-change' => 'changeLocation()',
				]
			);
		?>

		<?php
			echo __d('reservations', '[Available]');
			echo '{{selectLocation.ReservationLocation.openText}}'
		?>
		<?php
			$dataContent =
				'<dl>' .
					'<dt>' . __d('reservations', 'Available') . '</dt>' .
					'<dd>{{selectLocation.ReservationLocation.openText}}</dd>' .
					'<dt>' . __d('reservations', 'Contact') . '</dt>' .
					'<dd>{{selectLocation.ReservationLocation.contact}}</dd>' .
				'</dl>' .
				'<p>{{selectLocation.ReservationLocation.description}}</p>';
		?>
		<a href="" data-toggle="popover" data-placement="bottom" title="" data-trigger="click"
				data-content="<?php echo $dataContent; ?>"
				data-original-title="{{selectLocation.ReservationLocation.location_name}}">
			<?php echo __d('reservations', 'Details'); ?>
		</a>
		<?php
			echo '<script type="text/javascript">' .
					'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });' .
				'</script>';
		?>
	</div>
</div>

<?php //タイムライン開始時刻 ?>
<div class="form-group reservation-radio-timeline" ng-show="isShowTimelineStart">
	<?php
		echo $this->NetCommonsForm->input('ReservationFrameSetting.display_start_time_type', [
			'label' => __d('reservations', 'Timeline start time'),
			'type' => 'radio',
			'options' => [
				0 => __d('reservations', 'alters by time of use'),
				1 => __d('reservations', 'fixed')
			],
			'ng-model' => 'display_start_time_type',
			'ng-init' => sprintf(
				'display_start_time_type=\'%s\'',
				$this->request->data['ReservationFrameSetting']['display_start_time_type']
			),
			'div' => false,
		]);
	?>

	<div class="row">
		<div class="col-xs-11 col-xs-offset-1" ng-show="display_start_time_type == 1">
			<?php
				$options = array();
				$minTime = ReservationsComponent::CALENDAR_TIMELINE_MIN_TIME;
				$maxTime = ReservationsComponent::CALENDAR_TIMELINE_MAX_TIME;
				for ($idx = $minTime; $idx <= $maxTime; ++$idx) {
					$options[$idx] = sprintf('%02d:00', $idx);
				}

				echo $this->NetCommonsForm->input(
					'ReservationFrameSetting.timeline_base_time',
					array(
						'type' => 'select',
						'label' => false,
						'div' => false,
						'options' => $options,
						'selected' => $this->request->data['ReservationFrameSetting']['timeline_base_time'],
						'class' => 'form-control',
					)
				);
			?>
		</div>
	</div>
</div>

<?php /* 時間枠 */ ?>
<?php
	if ($hasTimeframe) {
		echo $this->NetCommonsForm->input('ReservationFrameSetting.display_timeframe', [
			'label' => __d('reservations', 'Time Frames setting'),
			'type' => 'checkbox',
			'options' => [
				'1' => __d('reservations', 'Display timeframe')
			]
		]);
	}
