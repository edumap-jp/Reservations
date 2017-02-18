<?php
/**
 * ReservationsController Test Case
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsController', 'Reservations.Controller');

/**
 * Summary for ReservationsController Test Case
 */
class ReservationsControllerTest extends ControllerTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.reservations.reservation',
		'plugin.reservations.language',
		'plugin.reservations.user',
		'plugin.reservations.role',
		'plugin.reservations.user_role_setting',
		'plugin.reservations.users_language'
	);

}
