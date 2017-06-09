<?php
/**
 * ReservationDeleteActionPlan::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('ReservationsComponent', 'Reservations.Controller/Component'); //constを使うため

/**
 * ReservationActionPlan::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationDeleteActionPlan
 */
class ReservationDeleteActionPlanValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.block_setting_for_reservation',
		'plugin.reservations.reservation',
		'plugin.reservations.reservation_event',
		//'plugin.reservations.reservation_event_content',,
		'plugin.reservations.reservation_event_share_user',
		'plugin.reservations.reservation_frame_setting',

		'plugin.reservations.reservation_rrule',
		'plugin.workflow.workflow_comment',
		'plugin.rooms.rooms_language4test',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'reservations';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'ReservationDeleteActionPlan';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validates';

/**
 * テストDataの取得
 *
 * @return array
 */
	private function __getData() {
		$data = array(
		'ReservationDeleteActionPlan' => array(
			'is_repeat' => 0,
			'first_sib_event_id' => 48,
			'origin_event_id' => 48,
			'is_recurrence' => 0,
			'edit_rrule' => 0,
		),
		'_NetCommonsTime' => array(
			'user_timezone' => 'Asia/Tokyo',
			'convert_fields' => '',
		),
		);

		return $data;
	}

/**
 * ValidationErrorのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - field フィールド名
 *  - value セットする値
 *  - message エラーメッセージ
 *  - overwrite 上書きするデータ(省略可)
 *
 * @return array テストデータ
 */
	public function dataProviderValidationError() {
		$data = $this->__getData();

		return array(
			//beforeValidate
			array('data' => $data, 'field' => 'edit_rrule', 'value' => 'aaa',
				'message' => __d('reservations', 'Invalid input. (edit rrule)')),
			array('data' => $data, 'field' => 'is_repeat', 'value' => 'a',
				'message' => __d('reservations', 'Invalid input. (repeat flag)')),
			array('data' => $data, 'field' => 'first_sib_event_id', 'value' => 'a',
				'message' => __d('reservations', 'Invalid input.  (first sib ebent id)')),
			array('data' => $data, 'field' => 'origin_event_id', 'value' => 'a',
				'message' => __d('reservations', 'Invalid input. (origin event id)')),
			array('data' => $data, 'field' => 'is_recurrence', 'value' => 'a',
				'message' => __d('reservations', 'Invalid input. (recurrence flag)')),
		);
	}

}
