<?php
/**
 * TimezoneOffsetToId
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */


App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');

/**
 * 予約のtimezone_offset からtimezone idをうめる
 *
 * @package NetCommons\Reservations\Config\Migration
 */
class TimezoneOffsetToId extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'timezone_offset_to_id';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(),
		'down' => array(),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		$this->ReservationEvent = $this->generateModel('ReservationEvent');

		if ($direction == 'up') {
			// timezone_offsetからtimezoneをうめる
			$tzTbl = self::getTzTbl();
			$offsetIdList = [];
			foreach ($tzTbl as $vars) {
				$offsetIdList[sprintf('%.1f', $vars[1])] = $vars[2];
			}
			$events = $this->ReservationEvent->find('all', array(
				'recursive' => -1,
				'callbacks' => false,
			));
			foreach ($events as $event) {
				$event['ReservationEvent']['timezone'] =
					$offsetIdList[$event['ReservationEvent']['timezone_offset']];
				$this->ReservationEvent->save($event, array(
					'validate' => false,
					'callbacks' => false,
				));
			}
		} elseif ($direction == 'down') {
			// null に戻す
			$events = $this->ReservationEvent->find('all', array(
				'recursive' => -1,
				'callbacks' => false,
			));
			foreach ($events as $event) {
				$event['ReservationEvent']['timezone'] = null;
				$this->ReservationEvent->save($event, array(
					'validate' => false,
					'callbacks' => false,
				));
			}
		}
		return true;
	}

/**
 * getTzTbl
 *
 * @return array カレンダータイムゾーン情報配列取得関数
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public static function getTzTbl() {
		$tzTbl = array(
			// NC3にEtc/GM+12はないが、施設予約ではNC3タイムゾーンからオフセット計算したデータが
			// これまでに登録されてるはずなので、-12.0というデータは存在しないはず。
			//
			//'_TZ_GMTM12' => array(
			//	__d('calendars', '(GMT-12:00) Eniwetok, Kwajalein'),
			//	-12.0,
			//	"Etc/GMT+12"
			//),
			'_TZ_GMTM11' => array(
				__d('calendars', '(GMT-11:00) Midway Island, Samoa'),
				-11.0, "Pacific/Midway"
			),
			'_TZ_GMTM10' => array(
				__d('calendars', '(GMT-10:00) Hawaii'),
				-10.0, "Pacific/Honolulu" // NC3にないのでUS/Hawaiiから変更
			),
			'_TZ_GMTM9' => array(
				__d('calendars', '(GMT-9:00) Alaska'),
				-9.0,
				"America/Anchorage" // NC3にないので変更//"US/Alaska"
			),
			'_TZ_GMTM8' => array(
				__d('calendars', '(GMT-8:00) Pacific Time (US & Canada)'),
				-8.0,
				"America/Los_Angeles" // NC3にないので変更//"US/Pacific"
			),
			'_TZ_GMTM7' => array(
				__d('calendars', '(GMT-7:00) Mountain Time (US & Canada)'),
				-7.0,
				"America/Denver" //NC3にないので変更//"US/Mountain"
			),
			'_TZ_GMTM6' => array(
				__d('calendars', '(GMT-6:00) Central Time (US & Canada), Mexico City'),
				-6.0,
				"America/Mexico_City" //NC3にないので変更//"US/Central"
			),
			'_TZ_GMTM5' => array(
				__d('calendars', '(GMT-5:00) Eastern Time (US & Canada), Bogota, Lima, Quito'),
				-5.0,
				"America/New_York" //NC3にないので変更//"US/Eastern"
			),
			'_TZ_GMTM4' => array(
				__d('calendars', '(GMT-4:00) Atlantic Time (Canada), Caracas, La Paz'),
				-4.0, "Atlantic/Bermuda"
			),
			'_TZ_GMTM35' => array(
				__d('calendars', '(GMT-3:30) Newfoundland'),
				-3.5,
				"America/St_Johns" // NC3にないので//"Canada/Newfoundland"
			),
			'_TZ_GMTM3' => array(
				__d('calendars', '(GMT-3:00) Brasilia, Buenos Aires, Georgetown'),
				-3.0,
				"America/Sao_Paulo" // NC3になかったので\//"Brazil/East"
			),
			'_TZ_GMTM2' => array(
				__d('calendars', '(GMT-2:00) Mid-Atlantic'),
				-2.0, "Atlantic/South_Georgia"
			),
			'_TZ_GMTM1' => array(
				__d('calendars', '(GMT-1:00) Azores, Cape Verde Islands'),
				-1.0, "Atlantic/Azores"
			),
			'_TZ_GMT0' => array(
				__d('calendars', '(GMT) Greenwich Mean Time, London, Dublin, Lisbon, Casablanca, Monrovia'),
				0.0, "UTC" // NC3になかったので//"Etc/Greenwich"
			),
			'_TZ_GMTP1' => array(
				__d('calendars', '(GMT+1:00) Amsterdam, Berlin, Rome, Copenhagen, Brussels, Madrid, Paris'),
				1.0, "Europe/Amsterdam"
			),
			'_TZ_GMTP2' => array(
				__d('calendars', '(GMT+2:00) Athens, Istanbul, Minsk, Helsinki, Jerusalem, South Africa'),
				2.0, "Europe/Athens"
			),
			'_TZ_GMTP3' => array(
				__d('calendars', '(GMT+3:00) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg'),
				3.0, "Asia/Baghdad"
			),
			'_TZ_GMTP35' => array(__d('calendars', '(GMT+3:30) Tehran'), 3.5, "Asia/Tehran"),
			'_TZ_GMTP4' => array(
				__d('calendars', '(GMT+4:00) Abu Dhabi, Muscat, Baku, Tbilisi'),
				4.0, "Asia/Muscat"
			),
			'_TZ_GMTP45' => array(
				__d('calendars', '(GMT+4:30) Kabul'),
				4.5, "Asia/Kabul"
			),
			'_TZ_GMTP5' => array(
				__d('calendars', '(GMT+5:00) Ekaterinburg, Islamabad, Karachi, Tashkent'),
				5.0, "Asia/Karachi"
			),
			'_TZ_GMTP55' => array(
				__d('calendars', '(GMT+5:30) Bombay, Calcutta, Madras, New Delhi'),
				5.5,
				"Asia/Kolkata" // NC3にないので変更した//"Asia/Calcutta"
			),
			'_TZ_GMTP6' => array(
				__d('calendars', '(GMT+6:00) Almaty, Dhaka, Colombo'),
				6.0, "Asia/Almaty"
			),
			'_TZ_GMTP7' => array(
				__d('calendars', '(GMT+7:00) Bangkok, Hanoi, Jakarta'),
				7.0, "Asia/Bangkok"
			),
			'_TZ_GMTP8' => array(
				__d('calendars', '(GMT+8:00) Beijing, Perth, Singapore, Hong Kong, Urumqi, Taipei'),
				8.0, "Asia/Singapore"
			),
			'_TZ_GMTP9' => array(
				__d('calendars', '(GMT+9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'),
				9.0, "Asia/Tokyo"
			),
			'_TZ_GMTP95' => array(
				__d('calendars', '(GMT+9:30) Adelaide, Darwin'),
				9.5, "Australia/Adelaide"
			),
			'_TZ_GMTP10' => array(
				__d('calendars', '(GMT+10:00) Brisbane, Canberra, Melbourne, Sydney, Guam,Vlasdiostok'),
				10.0, "Australia/Brisbane"
			),
			'_TZ_GMTP11' => array(
				__d('calendars', '(GMT+11:00) Magadan, Solomon Islands, New Caledonia'),
				11.0, "Pacific/Guadalcanal" //"Etc/GMT-11"// NC3にないので変更した。
			),
			'_TZ_GMTP12' => array(
				__d('calendars', '(GMT+12:00) Auckland, Wellington, Fiji, Kamchatka, Marshall Island'),
				12.0, "Pacific/Auckland"
			),
		);
		return $tzTbl;
	}
}
