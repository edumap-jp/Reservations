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
 * @property ReservationLocation $ReservationLocation
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\Test\Case\Model\ReservationLocation
 */
class IsReservableByLocationTest extends ReservationsGetTest {

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

	public function setUp() {
		parent::setUp();
		/** @var ReservationLocationReservable $reservationLocationReservable */
		$reservationLocationReservable = ClassRegistry::init('Reservations.ReservationLocationReservable');
		$reservationLocationReservable->clearCache();
	}

	//public function test承認が必要な施設は承認者情報つきで取得される() {
	//	$categoryId = null;
	//	$userId = 1;
	//	Current::write('User', [
	//		'id' => 1,
	//		'timezone' => 'Asia/Tokyo',
	//		'role_key' => 'system_administrator',
	//	]);
	//	$locations = $this->ReservationLocation->getReservableLocations($categoryId, $userId);
	//	$idIndexes = array_column(array_column($locations, 'ReservationLocation'), 'id');
	//	$id4index = array_search('4', $idIndexes);
	//	$expected = ['1'];
	//	self::assertSame($expected, $locations[$id4index]['approvalUserIds']);
	//}
	//
	//public function testいずれかのルームで予約可能なロール以上であれば予約可能な施設として取得される() {
	//	$categoryId = null;
	//	$userId = 2; // room.id:2で chief_editor
	//	Current::write('User', [
	//		'id' => 2,
	//		'timezone' => 'Asia/Tokyo',
	//		'role_key' => 'general_user',
	//		'UserRoleSetting' => ['use_private_room' => false]
	//	]);
	//	$locations = $this->ReservationLocation->getReservableLocations($categoryId, $userId);
	//	debug($locations);
	//	$ids = array_column(array_column($locations, 'ReservationLocation'), 'id');
	//	debug($ids);
	//	self::assertContains('1', $ids);
	//}
	//
	//public function testいずれのルームでも施設の予約可能なロールを満たしてない施設は取得されない() {
	//	$categoryId = null;
	//	$userId = 3; // room.id:2で editor
	//	Current::write('User', [
	//		'id' => 3,
	//		'timezone' => 'Asia/Tokyo',
	//		'role_key' => 'general_user',
	//		'UserRoleSetting' => ['use_private_room' => false]
	//	]);
	//	$locations = $this->ReservationLocation->getReservableLocations($categoryId, $userId);
	//	$ids = array_column(array_column($locations, 'ReservationLocation'), 'id');
	//
	//	debug($ids);
	//	// location.id:1はchief_editor以上でないと予約できないので取得されない
	//	self::assertNotContains('1', $ids);
	//}
}
