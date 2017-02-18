<?php
/**
 * Reservation Test Case
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

App::uses('Reservation', 'Reservations.Model');

/**
 * Summary for Reservation Test Case
 */
class ReservationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation',
		'plugin.reservations.room',
		'plugin.reservations.user',
		'plugin.reservations.role',
		'plugin.reservations.user_role_setting',
		'plugin.reservations.users_language',
		'plugin.reservations.language',
		'plugin.reservations.location',
		'plugin.reservations.calendar'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Reservation = ClassRegistry::init('Reservations.Reservation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Reservation);

		parent::tearDown();
	}

}
