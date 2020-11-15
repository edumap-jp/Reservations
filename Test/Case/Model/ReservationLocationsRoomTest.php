<?php
/**
 * ReservationLocationsRoom Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('ReservationLocationsRoom', 'Reservations.Model');

/**
 * Summary for ReservationLocationsRoom Test Case
 *
 * @property ReservationLocationsRoom $ReservationLocationsRoom
 */
class ReservationLocationsRoomTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_locations_room',
		'plugin.reservations.reservation_location',
		'plugin.reservations.room4test',
		'plugin.reservations.roles_rooms_user4test',
		'plugin.reservations.roles_room4test',
		//'plugin.reservations.user',
		//'plugin.reservations.role',
		'plugin.reservations.user_role_setting4test',
		//'plugin.reservations.users_language',
		//'plugin.reservations.language'
		'plugin.categories.category',
		'plugin.categories.categories_language',
		'plugin.categories.category_order',

	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReservationLocationsRoom = ClassRegistry::init('Reservations.ReservationLocationsRoom');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationLocationsRoom);

		parent::tearDown();
	}

/**
 * test全てのルームから予約を受け付けている施設はユーザが参加している全てのルームで予約可能
 *
 * @return void
 */
	public function testGetReservableRoomsByLocationKeyWithAllRoomReservableLocation() {
		$locationKey = 'location_1';
		$userId = '10';
		$result = $this->ReservationLocationsRoom->getReservableRoomsByLocationKey($locationKey, $userId);

		$roomIds = array_column(array_column($result, 'Room'), 'id');

		$expectedIds = [
			'11', '12'
		];
		sort($expectedIds);
		sort($roomIds);
		self::assertSame($expectedIds, $roomIds);
	}

/**
 * test特定のルームのみから予約を受け付けている施設はユーザが参加しているルームのうち施設予約可能としているルームのみ予約可能
 *
 * @return void
 */
	public function testGetReservableRoomsByLocationKeyWithSpecifyRoomsReservableLocation() {
		$locationKey = 'location_5';
		$userId = '10';
		$result = $this->ReservationLocationsRoom->getReservableRoomsByLocationKey($locationKey, $userId);

		$roomIds = array_column(array_column($result, 'Room'), 'id');

		$expectedIds = [
			'11',
		];
		self::assertSame($expectedIds, $roomIds);
	}

/**
 * getReservableRoomsByLocationKeyで施設がみつからなければ空配列を返す
 *
 * @return void
 */
	public function testGetReservableRoomsByLocationKeyWhenNotFoundResultIsEmptyArray() {
		$locationKey = 'not_found_location_key';
		$userId = '10';
		$result = $this->ReservationLocationsRoom->getReservableRoomsByLocationKey($locationKey, $userId);

		self::assertSame([], $result);
	}

}
