<?php
/**
 * ReservationLocationReservable Test Case
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationLocationReservable', 'Reservations.Model');

/**
 * Summary for ReservationLocationReservable Test Case
 */
class ReservationLocationReservableTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_location_reservable',
		'plugin.reservations.room',
		'plugin.reservations.user',
		'plugin.reservations.role',
		'plugin.reservations.user_role_setting',
		'plugin.reservations.users_language',
		'plugin.reservations.language'
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

}
