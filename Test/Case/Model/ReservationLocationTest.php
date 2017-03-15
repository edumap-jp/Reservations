<?php
/**
 * ReservationLocation Test Case
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationLocation', 'Reservations.Model');

/**
 * Summary for ReservationLocation Test Case
 */
class ReservationLocationTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_location',
		'plugin.reservations.language',
		'plugin.reservations.user',
		'plugin.reservations.role',
		'plugin.reservations.user_role_setting',
		'plugin.reservations.users_language',
		'plugin.reservations.category'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->ReservationLocation);

		parent::tearDown();
	}

}
