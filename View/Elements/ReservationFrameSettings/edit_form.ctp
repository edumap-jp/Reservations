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
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.is_myroom');

$displayType = $this->request->data['ReservationFrameSetting']['display_type'];
?>

<div class="form-group">
	<div class='row'>
		<?php echo $this->NetCommonsForm->label(
			'ReservationFrameSetting.display_type',
			__d('reservations', 'Display type'),
			array('class' => 'col-xs-12')
		); ?>
		<div class='col-xs-12'>
			<?php
			echo $this->NetCommonsForm->input(
				'ReservationFrameSetting.display_type',
				array(
					'type' => 'select',
					'label' => false,
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
		<div class="clearfix"></div>
	</div>
</div><!-- form-groupおわり-->

<?php
/* ルーム選択 */
//echo $this->element('Reservations.ReservationFrameSettings/room_select');
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.is_select_room');
?>
<?php
/* 開始位置 */
//echo $this->element('Reservations.ReservationFrameSettings/start_pos');
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.start_pos');
?>
<?php
/* 日数 */
//echo $this->element('Reservations.ReservationFrameSettings/display_count');
echo $this->NetCommonsForm->hidden('ReservationFrameSetting.display_count');
?>

<?php
/* 表時開始カテゴリ選択 */
?>
<div class="form-group col-xs-11 col-xs-offset-1" ng-show="isShowSelectCategory"
		ng-controller="ReservationFrameSettings.selectLocation"
		ng-init="initialize(<?php echo h(json_encode(['locations' => $locations])); ?>)">
	<?php echo $this->NetCommonsForm->label(
		'ReservationFrameSetting.location_key',
		__d('reservations', 'Default category')
	); ?>
	<!--<div class="col-xs-12 col-sm-9">-->

	<?php
	//  カテゴリ絞り込み
	$locationCategories = Hash::combine(
		$categories,
		'{n}.Category.id',
		'{n}.CategoriesLanguage.name'
	);
	// 施設の絞り込み, カテゴリなし　を追加
	$locationCategories = Hash::merge(
		[
			'' => __d('reservations', 'Display all'),
			//'' => __d('reservations', 'no category')
		],
		$locationCategories
	);
	//$this->NetCommonsForm->unlockField('ReservationLocation.category_id');
	echo $this->NetCommonsForm->input(
		'ReservationFrameSetting.category_id',
		[
			'label' => false,
			'options' => $locationCategories,
			//'ng-change' => 'selectLocationCategory()',
			//'ng-model' => 'locationCategory',
		]
	);
	?>
	<div class="clearfix"></div><?php /* 幅広画面整えるため追加 */ ?>
</div><!-- form-groupおわり-->



<?php
/* 表時開始施設選択 */
?>
<div class="form-group col-xs-11 col-xs-offset-1" ng-show="isShowSelectLocation"
		ng-controller="ReservationFrameSettings.selectLocation"
		ng-init="initialize(<?php echo h(json_encode(['locations' => $locations])); ?>)">
	<?php echo $this->NetCommonsForm->label(
		'ReservationFrameSetting.location_key',
		__d('reservations', 'Default institution')
	); ?>
	<!--<div class="col-xs-12 col-sm-9">-->

	<?php
	//  カテゴリ絞り込み
	$locationCategories = Hash::combine(
		$categories,
		'{n}.Category.id',
		'{n}.CategoriesLanguage.name'
	);
	// 施設の絞り込み, カテゴリなし　を追加
	$locationCategories = Hash::merge(
		[
			'all' => __d('reservations', 'Display all'),
			'' => __d('reservations', 'no category')
		],
		$locationCategories
	);
	//$this->NetCommonsForm->unlockField('ReservationLocation.category_id');
	$this->NetCommonsForm->unlockField('ReservationActionPlan.location_key');
	echo $this->NetCommonsForm->input(
		'ReservationLocation.filterByCategoryId',
		[
			'label' => false,
			'options' => $locationCategories,
			'ng-change' => 'selectLocationCategory()',
			'ng-model' => 'locationCategory',
		]
	);
	?>
	<!--施設選択-->
	<?php $locationOptions = Hash::combine(
		$locations,
		'{n}.ReservationLocation.key',
		'{n}.ReservationLocation.location_name'
	); ?>
	<?php
	echo $this->NetCommonsForm->input(
		'ReservationFrameSetting.location_key',
		[
			'label' => false,
			'type' => 'select',
			//'ng-options' =>
			//'location.ReservationLocation.location_name for location in data.locations track by location.ReservationLocation.key',
			'ng-options' =>
				'location.ReservationLocation.location_name for location in locationOptions track by location.ReservationLocation.key',
			//'ng-init' => 'ReservationActionPlan.location_key = \'' .
			//	$this->request->data['ReservationActionPlan']['location_key']
			//	. '\'',
			'ng-init' => 'setLocationKey(\'' .
				$this->request->data['ReservationFrameSetting']['location_key'] .
				'\')',
			//'ng-model' => 'ReservationActionPlan.location_key',
			'ng-model' => 'selectLocation',
			// optionsを指定しないとSecurityComponentでBlackHole送りになる
			'options' => $locationOptions,
			'ng-change' => 'changeLocation()',
		]
	);
	?>
	<?php echo __d('reservations', '[Available]'); ?>
	{{selectLocation.ReservationLocation.openText}}
	<a href="" data-toggle="popover" data-placement="bottom" title="" data-trigger="focus"
			data-content="
					<dl>
					<dt><?php echo __d('reservations', 'Available'); ?></dt><dd>{{selectLocation.ReservationLocation.openText}}</dd>
					<dt><?php echo __d('reservations', 'Contact'); ?></dt><dd>{{selectLocation.ReservationLocation.contact}}</dd>
					</dl>
					<p>{{selectLocation.ReservationLocation.description}}</p>
" data-original-title="{{selectLocation.ReservationLocation.location_name}}"><?php echo __d(
			'reservations',
			'詳細'
		); ?></a>
	<?php
	$html = '<script type="text/javascript">' .
		'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });</script>';
	echo $html;
	?>


	<div class="clearfix"></div><?php /* 幅広画面整えるため追加 */ ?>
</div><!-- form-groupおわり-->

<?php // タイムラインスタート位置 ?>
<div class="form-group" ng-show="isShowTimelineStart">
	<?php echo $this->NetCommonsForm->label(
		'ReservationFrameSetting.timeline_base_time',
		__d('reservations', 'Timeline start time')
	); ?>
	<?php
	echo $this->NetCommonsForm->input('ReservationFrameSetting.display_start_time_type', [
		'type' => 'radio',
		'options' => [
			0 => __d('reservations', 'alters by time of use'),
			1 => __d('reservations', 'fixed')
		],
		'ng-model' => 'display_start_time_type',
		'ng-init' => sprintf("display_start_time_type='%s'",
			$this->request->data['ReservationFrameSetting']['display_start_time_type']),
	]);
	?>
	<div class="col-xs-11 col-xs-offset-1"
			ng-show="display_start_time_type == 1">
		<?php
		$options = array();
		for ($idx = ReservationsComponent::CALENDAR_TIMELINE_MIN_TIME; $idx <= ReservationsComponent::CALENDAR_TIMELINE_MAX_TIME; ++$idx) {
			$options[$idx] = sprintf("%02d:00", $idx);
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

	<div class="clearfix"></div>
</div><!-- form-groupおわり-->


<?php
/* 時間枠 */
?>
<div class="form-group">
	<?php
	/* 表時枠表示 */
	echo $this->NetCommonsForm->label(
		'ReservationFrameSetting.display_timeframe',
		__d('reservations', '時間枠表示')
	);
	echo $this->NetCommonsForm->checkbox(
		'ReservationFrameSetting.display_timeframe',
		[
			'label' =>
				__d('reservations', '時間枠を表示する')
		]
	);
	?>
</div>

