<?php
/**
 * reservation frame timeline start pos view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group" ng-show="isShowSelectLocation"
		ng-controller="ReservationFrameSettings.selectLocation" ng-init="initialize(<?php echo h(json_encode(['locations' => $locations])); ?>)">
	<?php echo $this->NetCommonsForm->label('ReservationFrameSetting.location_key', __d('reservations', '最初に表示する施設'), array('class' => 'col-xs-12')); ?>
	<div class="col-xs-12 col-sm-9">

		<?php
		//  カテゴリ絞り込み
		$locationCategories = Hash::combine($categories, '{n}.Category.id', '{n}.CategoriesLanguage.name');
		// 施設の絞り込み, カテゴリなし　を追加
		$locationCategories = Hash::merge(
			[
				'all' => __d('reservations', '--施設の絞り込み--'),
				'' => __d('reservations', 'カテゴリ無し')
			],
			$locationCategories
		);
		$this->NetCommonsForm->unlockField('ReservationLocation.category_id');
		$this->NetCommonsForm->unlockField('ReservationActionPlan.location_key');
		echo $this->NetCommonsForm->input('ReservationLocation.category_id',
			[
				'label' => false,
				'options' => $locationCategories,
				'ng-change' => 'selectLocationCategory()',
				'ng-model' => 'locationCategory',
			]
		);
		?>
		<!--施設選択-->
		<?php $locationOptions = Hash::combine($locations, '{n}.ReservationLocation.key', '{n}.ReservationLocation.location_name'); ?>
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
		<?php echo __d('reservations', '【使用時間】'); ?>
		{{selectLocation.ReservationLocation.openText}}
		<a href="" data-toggle="popover" data-placement="bottom" title="" data-trigger="focus" data-content="
					<dl>
					<dt><?php echo __d('reservations', '利用時間'); ?></dt><dd>{{selectLocation.ReservationLocation.openText}}</dd>
					<dt><?php echo __d('reservations', '施設管理者'); ?></dt><dd>{{selectLocation.ReservationLocation.contact}}</dd>
					</dl>
					<p>{{selectLocation.ReservationLocation.description}}</p>
" data-original-title="{{selectLocation.ReservationLocation.location_name}}"><?php echo __d('reservations', '詳細'); ?></a>
		<?php
		$html = '<script type="text/javascript">' .
			'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });</script>';
		echo $html;
		?>


	</div><!-- col-xs-10おわり -->
	<div class="clearfix"></div><?php /* 幅広画面整えるため追加 */ ?>
</div><!-- form-groupおわり-->
