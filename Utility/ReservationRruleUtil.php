<?php
/**
 * ReservationRruleUtil Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('ReservationTime', 'Reservations.Utility');

/**
 * ReservationRruleUtil Utility
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Utility
 * @SuppressWarnings(PHPMD)
 */
class ReservationRruleUtil {

/**
 * addKeyToIcalUid
 *
 * iReservation仕様UIDのユニーク部にkeyをappendする（最大４つまで）
 *
 * @param string $icalUid icalUid
 * @param string $key key
 * @return string iReservation仕様のUIDのパーツを生成して返す
 */
	public static function addKeyToIcalUid($icalUid, $key = '') {
		if ($key === '') {
			return $icalUid;
		}
		if (preg_match('/^([^-]+)-([^@]+)@(.*)$/', $icalUid, $matches) === 1) {
			$elms = explode('#', $matches[2]);
			if (count($elms) >= 100) {
				//入れ物(255バイト)におさめるため
				//ireservation_uidの型をvarchar(255)からtext型(mysqlだと最長65,535バイト）
				//に変えたので、連続分割追跡回数を4から100に変更。
				//
				array_shift($elms);
				$uniqid = implode('#', $elms) . '#' . $key;
			} else {
				$uniqid = $matches[2] . '#' . $key;
			}
			$icalUid = $matches[1] . '-' . $uniqid . '@' . $matches[3];
		} else {
			//echo "NO match\n";
		}
		return $icalUid;
	}

/**
 * generateIcalUid
 *
 * iReservation仕様のUID生成
 *
 * @param string $startDate 施設予約開始日付
 * @param string $startTime 施設予約開始時刻
 * @return string iReservation仕様のUIDを生成.
 */
	public static function generateIcalUid($startDate, $startTime) {
		$domain = 'localhost';
		if (preg_match("/(?:.+)(?:\/\/)([^\/]+)/", FULL_BASE_URL, $matches) === 1) {
			$domain = $matches[1];
		}
		//$iReservationUid = $startDate . 'T' . $startTime . 'Z' . '-' . uniqid() . '@' . $domain;
		//ドメインは可変なのと、出力時に付加できる要素なので、省略しておく。
		$iReservationUid = $startDate . 'T' . $startTime . 'Z' . '-' . uniqid() . '@';
		return $iReservationUid;
	}

/**
 * Rruleパース(CRUD用)
 *
 * @param string $rruleStr rrule文字列
 * @return $resultArray rrule配列
 */
	public function &parseRrule($rruleStr = '') {
		//CakeLog::debug("DBGRRULE: parseRrule() CALLed!");
		$resultArray = array();
		if ($rruleStr === '') {
			$rruleStr = 'FREQ=NONE';
		}

		//表示系では$freqの処理を１階層いれているが、
		//施設予約の追加・変更・削除の時のrrule解析では不要。

		$array = explode(';', $rruleStr);
		foreach ($array as $rrule) {
			list($key, $val) = explode('=', $rrule);
			if ($key === 'FREQ' || $key === 'COUNT' || $key === 'UNTIL') {
				$this->__parseRruleFreqCountUntil($key, $val, $resultArray);
				continue;
			}
			if ($key === 'INTERVAL') {
				$resultArray[$key] = intval($val);
				continue;
			}
			$resultArray[$key] = explode(',', $val);
		}
		return $resultArray;
	}

/**
 * RruleFreqCountUntilパース
 *
 * @param string $key key
 * @param string $val val
 * @param string &$resultArray resultArray
 * @return void
 */
	private function __parseRruleFreqCountUntil($key, $val, &$resultArray) {
		$resultArray[$key] = $val;
		if ($key === 'UNTIL') {
			if (preg_match('/^([0-9]{8})[^0-9]*([0-9]{6})/i', $val, $matches)) {
				$resultArray[$key] = $matches[1] . $matches[2];
			}
		}
		if ($key === 'COUNT') {
			$resultArray['REPEAT_COUNT'] = 1;	//self::_ON;
			$resultArray['REPEAT_UNTIL'] = 0;	//self::_OFF;
		}
		if ($key === 'UNTIL') {
			$resultArray['REPEAT_COUNT'] = 0;	//self::_OFF;
			$resultArray['REPEAT_UNTIL'] = 1;	//self::_ON;
		}
	}

/**
 * Rrule文字列化処理
 *
 * @param array $rrule rrule配列
 * @return $result 成功時rrule文字列. 失敗時false
 */
	public function concatRrule($rrule) {
		if (empty($rrule)) {
			return '';
		}
		$result = array();
		switch ($rrule['FREQ']) {
			case 'NONE':
				$result = array();
				break;
			case 'YEARLY':
				$result = $this->__concatRruleYearly($rrule);
				break;
			case 'MONTHLY':
				$result = $this->__concatRruleMonthly($rrule);
				break;
			case 'WEEKLY':
				$result = array('FREQ=WEEKLY');
				$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
				$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
				break;
			case 'DAILY':
				$result = array('FREQ=DAILY');
				$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
				break;
			default:
				return false;
		}
		if (isset($rrule['UNTIL'])) {
			$result[] = 'UNTIL=' . $rrule['UNTIL'];
		} elseif (isset($rrule['COUNT'])) {
			$result[] = 'COUNT=' . intval($rrule['COUNT']);
		}
		return implode(';', $result);
	}

/**
 * Rrule文字列化処理(Yearly)
 *
 * @param array $rrule rrule配列
 * @return $result result配列
 */
	private function __concatRruleYearly($rrule) {
		$result = array('FREQ=YEARLY');
		$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
		$result[] = 'BYMONTH=' . implode(',', $rrule['BYMONTH']);
		if (!empty($rrule['BYDAY'])) {
			$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
		}
		return $result;
	}

/**
 * Rrule文字列化処理(Monthly)
 *
 * @param array $rrule rrule配列
 * @return $result result配列
 */
	private function __concatRruleMonthly($rrule) {
		$result = array('FREQ=MONTHLY');
		$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
		if (!empty($rrule['BYDAY'])) {
			$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
		}
		if (!empty($rrule['BYMONTHDAY'])) {
			$result[] = 'BYMONTHDAY=' . implode(',', $rrule['BYMONTHDAY']);
		}
		return $result;
	}

/**
 * 表示用繰返し配列のデフォルト値配列の生成
 *
 * @param string $svrYmdHis サーバー系YmsHis文字列
 * @return array 表示用繰返し配列のデフォルト値配列
 */
	public function defViewArrayOfRrule($svrYmdHis = '') {
		//ユーザーTZを取得しておく。
		$userTz = (new NetCommonsTime())->getUserTimezone();

		if ($svrYmdHis === '') {
			//今の時間を基準にする。
			$date = new DateTime('now', (new DateTimeZone($userTz)));
			//year, month, dayは、ユーザー系の現在のyear, month, dayを使う
			$date->setTime(0, 0, 0);	//時間だけ00:00:00にしておく。
			$userYmdHis = $date->format('YmdHis');
		} else {
			//渡されたサーバ系の時間をユーザー系に直す。
			$userYmdHis = (new ReservationTime())->svr2UserYmdHis($svrYmdHis);
		}
		//ユーザー系のDateTimeObj取得
		$defDate = (new ReservationTime())->newgetDtObjWithTzDateTime($userTz,
			substr($userYmdHis, 0, 4), substr($userYmdHis, 4, 2), substr($userYmdHis, 6, 2),
			substr($userYmdHis, 8, 2), substr($userYmdHis, 10, 2), substr($userYmdHis, 12, 2));

		/*
		$date = $this->_request->getParameter("date");
		if (!empty($date)) {
			$date = timezone_date($date."000000", true, "YmdHis");
		} else {
			$date = timezone_date($date, true, "YmdHis");
		}
		$timestamp = mktime(substr($date,8,2), substr($date,10,2), substr($date,12,2),
							substr($date,4,2), substr($date,6,2), substr($date,0,4));
		*/

		//ユーザー系のデフォルト時間のY/m/d
		$untilView = $defDate->format(ReservationsComponent::CALENDAR_DATE_FORMAT);

		//サーバー系のデフォルト時間のYmdHis
		$defDate->setTimeZone(new DateTimeZone('UTC'));	//サーバー系TZに切替
		$until = $defDate->format("YmdHis");

		$resultArray = array(
			'FREQ' => 'NONE',
			'NONE' => array(),
			'YEARLY' => array('FREQ' => 'YEARLY'),
			'MONTHLY' => array('FREQ' => 'MONTHLY'),
			'WEEKLY' => array('FREQ' => 'WEEKLY'),
			'DAILY' => array('FREQ' => 'DAILY'),
			'COUNT' => 3,
			'UNTIL' => $until,
			'UNTIL_VIEW' => $untilView,
			'REPEAT_COUNT' => 1, //_ON,
			'REPEAT_UNTIL' => 0, //_OFF
		);

		$defDate->setTimeZone(new DateTimeZone($userTz));	//ユーザー系TZに切替

		$wday = $defDate->format('w');	//ユーザー系デフォルト日の曜日index(0-6)
		$month = $defDate->format('m');	//ユーザー系デフォルト日の2桁固定月(01-12)

		$resultArray['YEARLY'] = array(
			'INTERVAL' => 1,
			'BYDAY' => array(),
			'BYMONTH' => array(intval($month))
		);
		$resultArray['MONTHLY'] = array(
			'INTERVAL' => 1,
			'BYDAY' => array(),
			'BYMONTHDAY' => array()
		);
		$wdays = explode('|', ReservationsComponent::CALENDAR_REPEAT_WDAY);
		$resultArray['WEEKLY'] = array(
			'INTERVAL' => 1,
			'BYDAY' => array($wdays[$wday])
		);
		$resultArray['DAILY'] = array(
			'INTERVAL' => 1
		);
		return $resultArray;
	}

/**
 * 表示用繰返し配列の生成
 *
 * @param string $rruleStr rrule文字列
 * @param int $baseFlag baseFlag 1の時、デフォルト値配列を利用
 * @return array 表示用繰返し配列
 */
	public function mkViewArrayOfRrule($rruleStr = '', $baseFlag = 0) {
		$resultArray = array();
		if ($baseFlag) {
			$resultArray = $this->defViewArrayOfRrule();
		}
		if ($rruleStr == '') {
			return $resultArray;
		}

		$freq = $this->__getFreq($rruleStr);

		$array = explode(';', $rruleStr);
		foreach ($array as $rrule) {
			list($key, $val) = explode('=', $rrule);
			if ($key == 'FREQ' || $key == 'COUNT' || $key == 'UNTIL') {
				$resultArray = $this->__setFreqOrCountOrUntilCase($key, $val, $resultArray);
				continue;
			}
			if ($key == 'INTERVAL') {
				$resultArray[$freq][$key] = intval($val);
				continue;
			}
			$resultArray[$freq][$key] = explode(',', $val);
		}
		return $resultArray;
	}

/**
 * __getFreq
 *
 * freq変数取得
 *
 * @param string $rruleStr rrule文字列
 * @return string NONE,DAILY,WEEKLY,MONTHLY,YEARLYのいずれかが入ったfreq変数を返す
 */
	private function __getFreq($rruleStr) {
		$matches = array();
		$result = preg_match('/FREQ=(NONE)/', $rruleStr, $matches);
		if (!$result) {
			$result = preg_match('/FREQ=(YEARLY)/', $rruleStr, $matches);
		}
		if (!$result) {
			$result = preg_match('/FREQ=(MONTHLY)/', $rruleStr, $matches);
		}
		if (!$result) {
			$result = preg_match('/FREQ=(WEEKLY)/', $rruleStr, $matches);
		}
		if (!$result) {
			$result = preg_match('/FREQ=(DAILY)/', $rruleStr, $matches);
		}

		if ($result) {
			$freq = $matches[1];
		} else {
			$freq = 'NONE';
		}
		return $freq;
	}

/**
 * __setFreqOrCountOrUntilCase
 *
 * FREQ、COUNTまたはUNTIL時のresultArray変数セット
 *
 * @param string $key key
 * @param string $val val
 * @param array $resultArray 格納前のresultArray配列
 * @return array 結果を格納した$resultArray配列
 */
	private function __setFreqOrCountOrUntilCase($key, $val, $resultArray) {
		$resultArray[$key] = $val;
		if ($key == 'UNTIL') {
			//yyyymmddThhmmss, yyyymmddhhmmss いずれの形式にも対応する正規表現
			if (preg_match('/^([0-9]{8})[^0-9]*([0-9]{6})/i', $val, $matches)) {
				$svrYmdHis = $matches[1] . $matches[2];
				$resultArray[$key] = $svrYmdHis;

				//insertFlag=0はつまり表示系.サーバー系toユーザ系
				$insertFlag = 0;

				//toFlag=1は、$svrYmdHisをユーザー系に直した時、ユーザー系YmdHisの
				//His部分が000000の場合、x年y月z日00:00:00ではなく、
				//x年y月z日の「前日24:00:00」と表記せよとの指示となる。
				$toFlag = 1;

				$resultArray['UNTIL_VIEW'] =
					(new ReservationTime())->dateFormat($svrYmdHis, null, $insertFlag,
						ReservationsComponent::CALENDAR_DATE_FORMAT, $toFlag);
			}
		}
		if ($key == 'COUNT') {
			$resultArray['REPEAT_COUNT'] = 1; //_ON;
			$resultArray['REPEAT_UNTIL'] = 0; //_OFF;
		}
		if ($key == 'UNTIL') {
			$resultArray['REPEAT_COUNT'] = 0; //_OFF;
			$resultArray['REPEAT_UNTIL'] = 1; //_ON;
		}
		return $resultArray;
	}
}
