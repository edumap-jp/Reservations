<?php
/**
 * ReservationLocationsApprovalUser Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationLocationsApprovalUser', 'Reservations.Model');

/**
 * Summary for ReservationLocationsApprovalUser Test Case
 *
 * @property ReservationLocationsApprovalUser $ReservationLocationsApprovalUser
 */
class ReservationLocationsApprovalUserTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_locations_approval_user',
		//'plugin.reservations.user'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReservationLocationsApprovalUser = ClassRegistry::init('Reservations.ReservationLocationsApprovalUser');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationLocationsApprovalUser);

		parent::tearDown();
	}

/**
 * test findApprovalUserIdsByLocations で施設keyをキーとした連想配列に施設の承認ユーザIDリストが取得できる
 *
 * @return void
 */
	public function testFindApprovalUserIdsByLocations() {
		$locations = [
			[
				'ReservationLocation' => [
					'key' => 'location_4',
					'use_workflow' => 1,
				]
			],
			[
				// use_workflow:0 なので承認不要の施設
				'ReservationLocation' => [
					'key' => 'location_5',
					'use_workflow' => 0,
				]
			],
			[
				'ReservationLocation' => [
					'key' => 'location_6',
					'use_workflow' => 1,
				]
			],
		];

		$userIds = $this->ReservationLocationsApprovalUser->findApprovalUserIdsByLocations($locations);
		$expected = [
			'location_4' => [
				'1'
			],
			'location_6' => [
				'2'
			]
		];
		self::assertSame($expected, $userIds);
	}

/**
 * 取得結果がキャッシュされる
 *
 * @return void
 * @throws ReflectionException
 */
	public function testResultCache() {
		$locations = [
			[
				'ReservationLocation' => [
					'key' => 'location_4',
					'use_workflow' => 1,
				]
			],
		];

		$userIds = $this->ReservationLocationsApprovalUser->findApprovalUserIdsByLocations($locations);
		$cacheProperty = new ReflectionProperty($this->ReservationLocationsApprovalUser, '_approvalUserIds');
		$cacheProperty->setAccessible(true);

		self::assertSame($userIds, $cacheProperty->getValue($this->ReservationLocationsApprovalUser));
	}

/**
 * キャッシュがあればキャッシュから結果が返される
 *
 * @return void
 * @throws ReflectionException
 */
	public function testUseCache() {
		// キャッシュが使われることの確認のためキャッシュ済み状態にしておく
		$cache = [
			'location_key_a' => [
				'1',
				'2'
			],
			'location_key_b' => [
				'3',
				'4'
			]
		];
		$cacheProperty = new ReflectionProperty($this->ReservationLocationsApprovalUser, '_approvalUserIds');
		$cacheProperty->setAccessible(true);
		$cacheProperty->setValue($this->ReservationLocationsApprovalUser, $cache);

		$locations = [
			[
				'ReservationLocation' => [
					'key' => 'location_key_a',
					'use_workflow' => 1,
				]
			],
			[
				// use_workflow:0 なので承認不要の施設
				'ReservationLocation' => [
					'key' => 'location_key_b',
					'use_workflow' => 1,
				]
			],
		];

		$result = $this->ReservationLocationsApprovalUser->findApprovalUserIdsByLocations($locations);

		self::assertSame($cache, $result);
	}

}
