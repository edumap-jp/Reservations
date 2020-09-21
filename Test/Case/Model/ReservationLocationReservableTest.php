<?php
/**
 * ReservationLocationReservable Test Case
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
final class ReservationLocationReservableTest extends NetCommonsModelTestCase {

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

	public function test個人的な予約を受け付けてる施設はプライベートルームが使えるユーザならルームでの権限がなくても予約可能() {
		$userId = 10;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$location = [
			'ReservationLocation' => [
				'id' => '3',
				'key' => 'KEY_3',
				'use_private' => true,
				'use_all_rooms' => true,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);
		self::assertTrue($result);
	}
	public function test個人的な予約を受け付けてる施設でプライベートルームが使えないユーザならルーム権限もなければ予約不可() {
		$userId = 11;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'custom_user');
		Current::write('User.UserRoleSetting.use_private_room', false);

		$location = [
			'ReservationLocation' => [
				'id' => '3',
				'key' => 'KEY_3',
				'use_private' => true,
				'use_all_rooms' => true,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);
		self::assertFalse($result);
	}

	public function test全てのルームから予約を受け付けている施設はいずれかのルームで権限あれば予約可能() {
		$userId = 10;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$location = [
			'ReservationLocation' => [
				'id' => '1',
				'key' => 'KEY_1',
				'use_private' => false,
				'use_all_rooms' => true,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);
		self::assertTrue($result);
	}

	public function test特定のルームのみ予約を受け付けている施設はいずれかのそのルームで権限あれば予約可能() {
		$userId = 10;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'general_user');
		Current::write('User.UserRoleSetting.use_private_room', true);

		$location = [
			'ReservationLocation' => [
				'id' => '2',
				'key' => 'KEY_2',
				'use_private' => false,
				'use_all_rooms' => false,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);
		self::assertTrue($result);
	}

	public function test全てのルームから予約を受け付けている施設でいずれのルーム権限もなければ予約不可() {
		$userId = 11;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'custom_user');
		Current::write('User.UserRoleSetting.use_private_room', false);

		$location = [
			'ReservationLocation' => [
				'id' => '1',
				'key' => 'KEY_1',
				'use_private' => false,
				'use_all_rooms' => true,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);

		self::assertFalse($result);
	}

	public function test特定のルームのみ予約を受け付けている施設でそのルームの権限がなければ予約不可() {
		$userId = 11;
		Current::write('User.id', $userId);
		Current::write('User.role_key', 'custom_user');
		Current::write('User.UserRoleSetting.use_private_room', false);

		$location = [
			'ReservationLocation' => [
				'id' => '2',
				'key' => 'KEY_2',
				'use_private' => false,
				'use_all_rooms' => false,
			]
		];
		$result = $this->ReservationLocationReservable->isReservableByLocation($location);

		self::assertFalse($result);

	}
}
