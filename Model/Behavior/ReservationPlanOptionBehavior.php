<?php
/**
 * ReservationPlanOption Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationPlanOptionBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationPlanOptionBehavior extends ReservationAppBehavior {

/**
 * getNoticeEmailOption
 *
 * メール通知する時の選択selectのoptions配列
 *
 * @param Model $model 実際のモデル名
 * @return mixed 生成したoptions配列を返す
 */
	public function getNoticeEmailOption(Model $model) {
		$options = array(
			'0' => __d('reservations', 'Before 0 minutes'),
			'5' => __d('reservations', 'Before 5 minutes'),
			'10' => __d('reservations', 'Before 10 minutes'),
			'15' => __d('reservations', 'Before 15 minutes'),
			'20' => __d('reservations', 'Before 20 minutes'),
			'25' => __d('reservations', 'Before 25 minutes'),
			'30' => __d('reservations', 'Before 30 minutes'),
			'45' => __d('reservations', 'Before 45 minutes'),
			'60' => __d('reservations', '1 hour before'),
			'120' => __d('reservations', '2 hours before'),
			'180' => __d('reservations', '3 hours before'),
			'720' => __d('reservations', '12 hours before'),
			'1440' => __d('reservations', '24 hours before'),
			'2880' => __d('reservations', '2 days before'),
			'8540' => __d('reservations', '1 week before'),
			'-1' => __d('reservations', 'Right now'),
		);
		return $options;
	}
}
