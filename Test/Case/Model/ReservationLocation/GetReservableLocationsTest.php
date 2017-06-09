<?php
/**
 * ReservationLocation::getReservableLocations()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsGetTest', 'Reservations.TestSuite');
//App::uses('TestAuthGeneral', 'AuthGeneral.TestSuite');

/**
 * ReservationLocation::getReservableLocations()のテスト
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationLocation
 */
class ReservationLocationGetReservableLocationsTest extends ReservationsGetTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation',
		'plugin.reservations.reservation_event',
		//'plugin.reservations.reservation_event_content',,
		'plugin.reservations.reservation_event_share_user',
		'plugin.reservations.reservation_frame_setting',
		'plugin.reservations.reservation_location',
		'plugin.reservations.reservation_location_reservable',
		'plugin.reservations.reservation_locations_approval_user',
		'plugin.reservations.reservation_locations_room',
		'plugin.reservations.reservation_rrule',
		'plugin.reservations.reservation_timeframe',
		'plugin.workflow.workflow_comment',
		'plugin.categories.category',
		'plugin.categories.categories_language',
		'plugin.categories.category_order',
		'plugin.user_roles.user_role_setting',
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
	protected $_methodName = 'getReservableLocations';

/**
 * getReservableLocations()のテスト
 *
 * @return void
 */
	public function testGetReservableLocations() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$categoryId = null;
		$userId = 1;

		//TestAuthGeneral::login($this);

		Current::write('User', [
			'id' => 1,
			'timezone' => 'Asia/Tokyo',
			'role_key' => 'system_administrator',
		]);

		//テスト実施
		$result = $this->$model->$methodName($categoryId, $userId);
		//TestAuthGeneral::logout($this);

		//チェック
		//:Assertを書く
		// ε(　　　　 v ﾟωﾟ)　＜ ひとまず件数だけチェック
		$this->assertEquals(3, count($result));
		//debug($result);
	}

/**
 * 最後の施設が表示されず、その手前の施設が2回繰り返して表示されるバグの修正
 *
 * @see https://github.com/NetCommons3/NetCommons3/issues/897
 * @return void
 */
	public function testNC3Issue897duplicateLocations() {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//データ生成
		$categoryId = null;
		$userId = 1;

		//TestAuthGeneral::login($this);

		Current::write('User', [
			'id' => 1,
			'timezone' => 'Asia/Tokyo',
			'role_key' => 'system_administrator',
		]);

		//テスト実施
		$result = $this->$model->$methodName($categoryId, $userId);
		//TestAuthGeneral::logout($this);

		//チェック
		//:Assertを書く
		// ひとまず件数だけチェック
		$this->assertEquals(3, count($result));

		// 取得される施設のキーが重複してないことを確認する(Issue897発生時はこれがFailedになる)
		$keys = [];
		foreach ($result as $location) {
			$this->assertNotContains($location['ReservationLocation']['key'], $keys);
			$keys[] = $location['ReservationLocation']['key'];
		}
	}
}
