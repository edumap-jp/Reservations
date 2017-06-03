<?php
/**
 * Reservations All Test Suite
 *
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsTestSuite', 'NetCommons.TestSuite');

/**
 * Reservations All Test Suite
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Reservations\Test\Case
 * @codeCoverageIgnore
 */
class AllReservationsTest extends NetCommonsTestSuite {

/**
 * All test suite
 *
 * @return CakeTestSuite
 */
	public static function suite() {
		$plugin = preg_replace('/^All([\w]+)Test$/', '$1', __CLASS__);
		$suite = new NetCommonsTestSuite(sprintf('All %s Plugin tests', $plugin));
		//$suite->addTestDirectoryRecursive(CakePlugin::path($plugin) . 'Test' . DS . 'Case');
		$basePath = CakePlugin::path($plugin) . 'Test' . DS . 'Case';
		$suite->addTestDirectoryRecursive($basePath . DS . 'Service');
		$suite->addTestDirectoryRecursive($basePath . DS . 'Utility');
		//$suite->addTestDirectoryRecursive($basePath . DS . 'Model');

		$suite->addTestFile($basePath . DS . 'Model' . DS . 'ReservationLocation' . DS .
			'GetLocationsTest.php');
		$suite->addTestFile($basePath . DS . 'Model' . DS . 'ReservationLocation' . DS .
			'GetReservableLocationsTest.php');

		//$suite->addTestDirectoryRecursive($basePath . DS . 'Controller');
		return $suite;
	}
}
