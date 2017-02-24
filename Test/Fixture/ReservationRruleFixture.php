<?php
/**
 * ReservationRruleFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for ReservationRruleFixture
 */
class ReservationRruleFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'reservation_id' => 1,
			'key' => 'reservationplan1',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 2,
			'reservation_id' => 2,
			'key' => 'reservationplan2',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 4,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 4,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 3,
			'reservation_id' => 3,
			'key' => 'reservationplan3',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 4,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 4,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 4,
			'reservation_id' => 4,
			'key' => 'reservationplan4',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 5,
			'reservation_id' => 5,
			'key' => 'reservationplan5',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 6,
			'reservation_id' => 1,
			'key' => 'reservationplan6',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 2,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 2,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 7,
			'reservation_id' => 1,
			'key' => 'reservationplan7',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=DAILY;INTERVAL=1;COUNT=2',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 8,
			'reservation_id' => 8,
			'key' => 'reservationplan8',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 9,
			'reservation_id' => 9,
			'key' => 'reservationplan9line',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 10,
			'reservation_id' => 1,
			'key' => 'reservationplan10',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=WEEKLY;INTERVAL=1;BYDAY=1SU;COUNT=2',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 12,
			'reservation_id' => 1,
			'key' => 'reservationplan12',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=1SU;COUNT=2',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 14,
			'reservation_id' => 1,
			'key' => 'reservationplan14',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=YEARLY;INTERVAL=1;BYMONTH=2;COUNT=2',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 16,
			'reservation_id' => 1,
			'key' => 'reservationplan16',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=WEEKLY;INTERVAL=2;BYDAY=TH;UNTIL=20160902T150000',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 17,
			'reservation_id' => 1,
			'key' => 'reservationplan17',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=2;BYMONTHDAY=2;COUNT=1',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1, //test1
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 18,
			'reservation_id' => 1,
			'key' => 'reservationplan18',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=YEARLY;INTERVAL=2;BYMONTH=9;BYDAY=2SA;UNTIL=20170901T150000',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 19,
			'reservation_id' => 1,
			'key' => 'reservationplan19',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=DAILY;INTERVAL=2;UNTIL=20160902T150000',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 20,
			'reservation_id' => 1,
			'key' => 'reservationplan20',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=2MO;COUNT=1',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 21,
			'reservation_id' => 1,
			'key' => 'reservationplan21',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=3TU;COUNT=1',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 22,
			'reservation_id' => 1,
			'key' => 'reservationplan22',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=4WE;COUNT=1',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 23,
			'reservation_id' => 1,
			'key' => 'reservationplan23',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=-1TH;COUNT=1',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 27,
			'reservation_id' => 1,
			'key' => 'reservationplan27',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'ireservation_uid' => 'Lorem ipsum dolor sit amet',
			'ireservation_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '8',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
	);

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		require_once App::pluginPath('Reservations') . 'Config' . DS . 'Schema' . DS . 'schema.php';
		$this->fields = (new ReservationsSchema())->tables[Inflector::tableize($this->name)];
		parent::init();
	}

}
