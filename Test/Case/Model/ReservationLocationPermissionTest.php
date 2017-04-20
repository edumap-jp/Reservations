<?php
/**
 * ReservationLocationPermission Test Case
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationLocationPermission', 'Reservations.Model');

/**
 * Summary for ReservationLocationPermission Test Case
 */
class ReservationLocationPermissionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_location_permission',
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
		$this->ReservationLocationPermission = ClassRegistry::init('Reservations.ReservationLocationPermission');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationLocationPermission);

		parent::tearDown();
	}

}
