<?php
/**
 * RegistCalendarBehavior.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class RegistCalendarBehavior
 */
class RegistCalendarBehavior extends ReservationAppBehavior {

/**
 * カレンダ更新
 *
 * @param Model $model ReservationEvent
 * @param array $data ReservationEvent data
 * @param array $rruleData ReservationRrule data
 * @return void
 */
	public function updateCalendar(Model $model, $data, $rruleData) {
		$data = Hash::merge($data, $rruleData);

		$cmd = ($data[$model->alias]['use_calendar']) ? 'save' : 'del';

		$data[$model->alias]['start_datetime'] =
			date('Y-m-d H:i:s', strtotime($data[$model->alias]['dtstart']));
		$data[$model->alias]['end_datetime'] =
			date('Y-m-d H:i:s', strtotime($data[$model->alias]['dtend']));

		// save_1がセットされてないとカレンダ登録されないので…
		$data['save_1'] = true;

		if ($cmd === 'save') {
			//実施期間設定あり&&カレンダー登録する
			$this->_registCalendar($model, $data, $rruleData);
		} else {
			//cmd===del
			if (!empty($data[$model->alias]['calendar_key'])) {
				//calendar_keyが記録されているので、消しにいく。
				$this->_removeCalendar($model, $data, $rruleData);
			}
		}
	}

/**
 * カレンダから登録削除
 *
 * @param Model $model ReservationEvent
 * @param array $data ReservationEvent data
 * @param array $rruleData ReservationRrule data
 * @return void
 *
 * @throws InternalErrorException
 */
	protected function _removeCalendar(Model $model, $data, $rruleData) {
		$model->loadModels(
			[
				'CalendarDeleteActionPlan' => 'Calendars.CalendarDeleteActionPlan',
			]
		);
		//削除用settings指定
		$model->CalendarDeleteActionPlan->Behaviors->load(
			'Calendars.CalendarLink',
			array(
				'linkPlugin' => Current::read('Plugin.key'),
				'table' => $model->alias, //fieldsの対象テーブル
				'sysFields' => array(
					'key' => 'key', //tasksの場合、task_contentsテーブルのkey
					'calendar_key' => 'calendar_key', //tasksの場合、task_contentsテーブルのcalendar_key
				),
				'isDelRepeat' => (Hash::get($rruleData, 'ReservationRrule.rrule', false)) ?
					true : false,
			)
		);
		$model->CalendarDeleteActionPlan->deletePlanForLink($data);
		//削除が成功したので、calenar_keyをクリアし、use_calendarをＯＦＦにして、
		//TaskContentにsave(update)しておく。
		$data[$model->alias]['calendar_key'] = '';
		$data[$model->alias]['use_calendar'] = 0;
		$savedData = $model->save($data, false);
		if ($savedData === false) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$data[$model->alias] = $savedData[$model->alias];
		$model->CalendarDeleteActionPlan->Behaviors->unload('Calendars.CalendarLink');
	}

/**
 * カレンダ登録
 *
 * @param Model $model ReservationEvent
 * @param array $data ReservationEvent data
 * @param array $rruleData ReservationRrule data
 * @return void
 *
 * @throws InternalErrorException
 */
	protected function _registCalendar(Model $model, $data, $rruleData) {
		$model->loadModels(
			[
				'CalendarActionPlan' => 'Calendars.CalendarActionPlan',
			]
		);
		//登録・変更用settings指定付きでbehaviorロード
		$model->CalendarActionPlan->Behaviors->load(
			'Calendars.CalendarLink',
			array(
				'linkPlugin' => Current::read('Plugin.key'),
				'table' => $model->alias, //fieldsの対象テーブル
				//'table' => 'reservation_events',	//fieldsの対象テーブル
				'inputFields' => array(
					'title' => 'title',
					'description' => 'description',
				),
				'sysFields' => array(
					'key' => 'key', //tasksの場合、task_contentsテーブルのkey
					'calendar_key' => 'calendar_key', //tasksの場合、task_contentsテーブルのcalendar_key
				),
				'startendFields' => array(
					'start_datetime' => 'start_datetime',
					'end_datetime' => 'end_datetime',
				),
				'isServerTime' => true,
				'useStartendComplete' => false,
				'isLessthanOfEnd' => false,

				'isRepeat' => (Hash::get($rruleData, 'ReservationRrule.rrule', false)) ?
					true : false,
				'rruleTable' => 'ReservationRrule',
				'rrule' => 'rrule',
				'isPlanRoomId' => true,
				'planRoomTable' => $model->alias,
				'plan_room_id' => 'room_id',
			)
		);
		$calendarKey = $model->CalendarActionPlan->savePlanForLink($data);
		if (is_string($calendarKey) && !empty($calendarKey)) {
			//カレンダ登録成功
			//calenar_keyを TaskContentにsave(update)しておく。
			$data[$model->alias]['calendar_key'] = $calendarKey;

			$savedData = $model->save($data, false);

			if ($savedData === false) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$data[$model->alias] = $savedData[$model->alias];
		} elseif ($calendarKey === '') {
			//未承認や一時保存はカレンダー登録条件を満たさないのでスルー（通常）
		} else { //false
			//カレンダー登録時にエラー発生（エラー）
			//例外なげる
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$model->CalendarActionPlan->Behaviors->unload('Calendars.CalendarLink');
	}
}