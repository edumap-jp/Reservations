<?php
/**
 * ReservationLocation::getLocations()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsGetTest', 'Reservations.TestSuite');

/**
 * ReservationLocation::getLocations()のテスト
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationLocation
 */
class ReservationLocationGetLocationsTest extends ReservationsGetTest {

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
		'plugin.reservations.reservation_frame_setting_select_room',
		'plugin.reservations.reservation_location',
		'plugin.reservations.reservation_location_reservable',
		'plugin.reservations.reservation_locations_approval_user',
		'plugin.reservations.reservation_locations_room',
		'plugin.reservations.reservation_rrule',
		'plugin.reservations.reservation_timeframe',
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
	protected $_modelName = 'ReservationLocation';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getLocations';

/**
 * getLocations()のテスト
 *
 * @return void
 */
	public function testGetLocations() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$categoryId = null;

		Current::write('User', [
			'id' => 1,
			'timezone' => 'Asia/Tokyo',
		]);

		//テスト実施
		$result = $this->$model->$methodName($categoryId);

		//チェック
		// ε(　　　　 v ﾟωﾟ)　＜ ひとまず件数だけチェック
		$this->assertEquals(3, count($result));
		debug($result);
	}

}
