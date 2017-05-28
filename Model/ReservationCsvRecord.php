<?php
/**
 * ReservationImport.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationCsvRecord
 */
class ReservationCsvRecord extends AppModel {

/**
 * @var bool useTable
 */
	public $useTable = false;

/**
 * @var array actsAs
 */
	public $actsAs = [
		'Reservations.ReservationValidate',
	];

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'title' => array(
				'rule1' => array(
					'rule' => array('notBlank'),
					'required' => true,
					'message' => __d('reservations', 'Invalid input. (plan title)'),
				),
				'rule2' => array(
					'rule' => array('maxLength', ReservationsComponent::CALENDAR_VALIDATOR_TITLE_LEN),
					'message' => sprintf(__d('reservations',
						'%d character limited. (plan title)'), ReservationsComponent::CALENDAR_VALIDATOR_TITLE_LEN),
				),
			),
			'is_allday' => array(
				'rule1' => array(
					'rule' => array('inList', [0, 1, null]),
					'message' => __d('reservations',
						'「利用時間の制限なし」は0, 1またはnullにしてください。'),
				),

			),
			'start_date' => array(
				'rule1' => array(
					'rule' => array('checkYyyymmdd'),
					'message' => __d('reservations', '予約日はyyyymmdd形式で入力して下さい。'),
				),
			),
			'start_time' => array(
				'rule1' => array(
					'rule' => array('checkHis'),
					'message' => __d('reservations', '開始時刻はhhmmss形式で入力して下さい。'),
				),
			),
			'end_time' => array(
				'rule1' => array(
					'rule' => array('checkHis'),
					'message' => __d('reservations', '終了時刻はhhmmss形式で入力して下さい。'),
				),
			),
		));
	}

/**
 * CSV行データからフィールド名ありデータにする
 * 時間指定なしのデータは施設の利用時間のMaxにする
 *
 * @param array $row $csv行データ
 * @param array $location ReservationLocation  data
 * @return array
 */
	public function getCsvRecordByRow($row, $location) {
		$data = [];
		foreach ($row as $key => $value) {
			// 'null' って文字だったら nullにする
			$row[$key] = (strtolower($value) == 'null') ? null : $value;
		}

		$data['title'] = $row[0];
		$data['is_allday'] = $row[1];
		if ($data['is_allday'] == 1) {
			// 時間指定無し なら 施設のMax範囲
			//$userTimezone = Current::read('User.timezone');
			$ncTime = new NetCommonsTime();
			$locationStart = $ncTime->toUserDatetime($location['ReservationLocation']['start_time']);
			$locationEnd = $ncTime->toUserDatetime($location['ReservationLocation']['end_time']);
			$locationStartTime = date('His', strtotime($locationStart));
			$locationEndTime = date('His', strtotime($locationEnd));
			if ($locationStartTime == $locationEndTime) {
				// 24時間予約OKな施設
				$data['start_time'] = '000000';
				$data['end_time'] = '240000';
			} else {
				$data['start_time'] = $locationStartTime;
				$data['end_time'] = $locationEndTime;
			}
		} else {
			$data['start_time'] = $row[3];
			$data['end_time'] = $row[4];
		}
		$data['start_date'] = $row[2];

		$data['contact'] = $row[5];
		$data['description'] = $row[6];
		return $data;
		//$ret = [
		//	'ReservationCsvRecord' => $data,
		//	];
		//return $ret;
	}

/**
 * csvRecordの値をReservationActionPlanのフィールドへマッピングする
 *
 * @param array $csvRecord csvRecord
 * @return array
 */
	public function convertActionPlanData($csvRecord) {
		$data = [];
		$data['title'] = $csvRecord['title'];
		$startDatetime =
			$this->_getDatetiemFromCsvData($csvRecord['start_date'], $csvRecord['start_time']);
		$endDatetime =
			$this->_getDatetiemFromCsvData($csvRecord['start_date'], $csvRecord['end_time']);
		if ($startDatetime >= $endDatetime) {
			// 開始、終了が逆点してるときは、終点がホントは24時間後
			$endDatetime = date('Y-m-d H:i',
				strtotime('+1 day', strtotime($endDatetime))
				);
		}

		$data['detail_start_datetime'] =
			$startDatetime;
		$data['detail_end_datetime'] =
			$endDatetime;

		$data['contact'] = $csvRecord['contact'];
		$data['description'] = $csvRecord['description'];

		return $data;
	}

/**
 * Ymd, His から Y-m-d H:i を返す
 *
 * @param string $date Ymd
 * @param string $time His
 * @return string Y-m-d H:i
 */
	protected function _getDatetiemFromCsvData($date, $time) {
		$datetime = sprintf('%04d-%02d-%02d %02d:%02d',
			substr($date, 0, 4),
			substr($date, 4, 2),
			substr($date, 6, 2),
			substr($time, 0, 2),
			substr($time, 2, 2));
		return $datetime;
	}

}