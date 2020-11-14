<?php
/**
 * ReservationAppModelのメソッドをReservationLocationReservable経由でテストする
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('ReservationLocationReservable', 'Reservations.Model');

/**
 * Summary for ReservationLocationReservable Test Case
 *
 * @property ReservationLocationReservable $ReservationLocationReservable
 */
final class ReservationAppModelTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_location_reservable',
		'plugin.reservations.room4test',
		'plugin.reservations.roles_room4test',
		'plugin.reservations.roles_rooms_user4test',
		//'plugin.reservations.user',
		//'plugin.reservations.role',
		'plugin.reservations.user_role_setting4test',
		//'plugin.reservations.users_language',
		//'plugin.reservations.language'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReservationLocationReservable = ClassRegistry::init('Reservations.ReservationLocationReservable');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationLocationReservable);

		parent::tearDown();
	}

/**
 * getReadableRoomIds では参加している全ルームIDが取得される
 *
 * @return void
 */
	public function testGetReadableRoomIds() {
		$userId = 40;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$roomIds = $this->ReservationLocationReservable->getReadableRoomIds();

		$expected = [
			'40',
			'41',
			'42',
		];
		self::assertSame($expected, $roomIds);
	}

/**
 * getReadableRoomIds ではプライベートルームのIDも含まれる
 *
 * @return void
 */
	public function testGetReadableRoomIdsIncludePrivateRoomId() {
		$userId = 40;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$roomIds = $this->ReservationLocationReservable->getReadableRoomIds();
		// room_id:42はプライベートルーム
		self::assertContains('42', $roomIds);
	}

/**
 * getReadableRoomIdsWithOutPrivate ではプライベートルームのIDは含まれない
 *
 * @return void
 */
	public function testGetReadableRoomIdsWithOutPrivateNotIncludePrivateRoomId() {
		$userId = 40;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$roomIds = $this->ReservationLocationReservable->getReadableRoomIdsWithOutPrivate();

		$expected = [
			'40',
			'41',
		];
		self::assertSame($expected, $roomIds);
	}

/**
 * getReadableRoomIds 実行時にプライベートルーム以外のルームIDに対応したロールキーが_roleKeysWithRoomIdに保持される
 *
 * @return void
 * @throws ReflectionException
 */
	public function testRoleKeysWithRoomId() {
		$userId = 40;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$this->ReservationLocationReservable->getReadableRoomIds();

		$property = new ReflectionProperty($this->ReservationLocationReservable, '_roleKeysWithRoomId');
		$property->setAccessible(true);
		$value = $property->getValue($this->ReservationLocationReservable);

		$expected = [
			'40' => 'chief_editor',
			'41' => 'general_user',
		];
		self::assertSame($expected, $value);
	}
}
