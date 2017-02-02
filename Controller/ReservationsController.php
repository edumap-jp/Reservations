<?php
App::uses('ReservationsAppController', 'Reservations.Controller');

App::uses('CalendarTime', 'Calendars.Utility');
/**
 * Reservations Controller
 *
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */
class ReservationsController extends ReservationsAppController {

	public $helpers = [
		'Calendars.CalendarTurnCalendar',
		'Calendars.CalendarMonthly',
	];

	public $components = [
		'Calendars.Calendars'
	];

	public function index() {
		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');

		$vars['mInfo'] = [
			'yearOfPrevMonth' => 2017,
			'prevMonth' => 1,
			'daysInPrevMonth' =>  31,
			'yearOfNextMonth' =>  2017,
			'nextMonth' =>  3,
			'daysInNextMonth' =>  31,
			'year' =>  2017,
			'month' =>  2,
			'wdayOf1stDay' =>  3,
			'daysInMonth' =>  28,
			'wdayOfLastDay' =>  2,
			'numOfWeek' =>  5,
		];
		$vars['year'] = 2017;
		$vars['month'] = 2;
		$vars['day'] = 1;
		$vars['style'] = [];
		$vars['today'] = [
			'year' => 2017,
			'month' => 02,
			'day' => 01,
			'hour' => 17,
			'min' => 28,
			'sec' => 36,
		];
		$vars['plans'] = [];

		App::uses('CalendarPermissiveRooms', 'Calendars.Utility');
		$perm['roomInfos'] =[];
		CalendarPermissiveRooms::setRoomPermRoles($perm);

		$vars['holidays'] = [];
		$this->set('frameId', $frameId);
		$this->set('languageId', $languageId);
		$this->set('vars', $vars);
	}
}
