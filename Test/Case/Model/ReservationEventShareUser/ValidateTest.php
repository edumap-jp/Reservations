<?php
/**
 * ReservationEventShareUser::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
//App::uses('ReservationFixture', 'Reservations.Test/Fixture');

/**
 * ReservationEventShareUser::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationEventShareUser
 */
class ReservationEventShareUserValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation',
		'plugin.reservations.reservation_event',
		'plugin.reservations.reservation_event_content',
		'plugin.reservations.reservation_event_share_user',
		'plugin.reservations.reservation_frame_setting',

		'plugin.reservations.reservation_rrule',
		'plugin.workflow.workflow_comment',
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
	protected $_modelName = 'ReservationEventShareUser';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validates';

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
		//$data['ReservationMailSetting'] = (new ReservationMailSettingFixture())->records[0];
		$data = array();
		return array(
			array('data' => $data, 'field' => 'reservation_event_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'share_user', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
		);
	}

}
