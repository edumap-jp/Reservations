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
 */
class ReservationLocationsApprovalUserTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation_locations_approval_user',
		'plugin.reservations.user'
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

}
