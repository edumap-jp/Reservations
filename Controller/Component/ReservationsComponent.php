<?php
/**
 * Reservations Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');

/**
 * ReservationsComponent
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Controller
 */
class ReservationsComponent extends Component {

/**
 * 表示方法
 *
 * @var int
 */
	const	CALENDAR_DISP_TYPE_SMALL_MONTHLY = 1;	//月表示（縮小）
	const	CALENDAR_DISP_TYPE_LARGE_MONTHLY = 2;	//月表示（拡大）
	const	CALENDAR_DISP_TYPE_WEEKLY = 3;			//週表示
	const	CALENDAR_DISP_TYPE_DAILY = 4;			//日表示
	const	CALENDAR_DISP_TYPE_TSCHEDULE = 5;		//スケジュール（時間順）
	const	CALENDAR_DISP_TYPE_MSCHEDULE = 6;		//スケジュール（会員順）
	const RESERVATION_DISP_TYPE_CATEGORY_WEEKLY = 1;  //カテゴリー別 - 週表示
	const RESERVATION_DISP_TYPE_CATEGORY_DAILY = 2;   //カテゴリー別 - 日表示
	const RESERVATION_DISP_TYPE_LACATION_MONTHLY = 3; //施設別 - 月表示
	const RESERVATION_DISP_TYPE_LACATION_WEEKLY = 4;  //施設別 - 週表示

/**
 * 表示方法のデフォルト
 *
 * @var int
 */
	const RESERVATION_DISP_TYPE_DEFAULT = self::RESERVATION_DISP_TYPE_CATEGORY_WEEKLY;

/**
 * 表示方法のリスト
 *
 * @var array
 */
	public static $reservationTypes = array(
		self::RESERVATION_DISP_TYPE_CATEGORY_WEEKLY,
		self::RESERVATION_DISP_TYPE_CATEGORY_DAILY,
		self::RESERVATION_DISP_TYPE_LACATION_MONTHLY,
		self::RESERVATION_DISP_TYPE_LACATION_WEEKLY,
	);

/**
 * 画面スタイル(文字列)
 *
 * @var string
 */
	const	CALENDAR_STYLE_SMALL_MONTHLY = 'smallmonthly';	//月表示（縮小）
	const	CALENDAR_STYLE_LARGE_MONTHLY = 'largemonthly';	//月表示（拡大）
	const	CALENDAR_STYLE_WEEKLY = 'weekly';			//週表示
	const	CALENDAR_STYLE_DAILY = 'daily';			//日表示
	const	CALENDAR_STYLE_SCHEDULE = 'schedule';		//スケジュール
	const RESERVATION_STYLE_CATEGORY_WEEKLY = 'multiweekly';    //カテゴリー別 - 週表示
	const RESERVATION_STYLE_CATEGORY_DAILY = 'multidaily';      //カテゴリー別 - 日表示
	const RESERVATION_STYLE_LACATION_MONTHLY = 'singlemonthly'; //施設別 - 月表示
	const RESERVATION_STYLE_LACATION_WEEKLY = 'singleweekly';   //施設別 - 週表示

/**
 * 画面スタイルのデフォルト値
 *
 * @var string
 */
	const RESERVATION_STYLE_DEFAULT = self::RESERVATION_STYLE_CATEGORY_WEEKLY;

/**
 * スケジュール画面ソート(文字列)
 *
 * @var string
 */
	const	CALENDAR_SCHEDULE_SORT_TIME = 'time';	//スケジュール（時間順）
	const	CALENDAR_SCHEDULE_SORT_MEMBER = 'member';	//スケジュール（会員順）

/**
 * 単一日画面タブ(文字列)
 *
 * @var string
 */
	const	CALENDAR_DAILY_TAB_LIST = 'list';	//単一日画面タブ（一覧）
	const	CALENDAR_DAILY_TAB_TIMELINE = 'timeline';	//単一日画面タブ（タイムライン）

/**
 * 開始位置 (年用)
 *
 * @var int
 */
	const	CALENDAR_START_POS_YEARLY_THIS_MONTH = 0;				//今月
	const	CALENDAR_START_POS_YEARLY_LAST_MONTH = 1;				//前月
	const	CALENDAR_START_POS_YEARLY_JANUARY = 2;					//1月
	const	CALENDAR_START_POS_YEARLY_APRIL = 3;					//4月

/**
 * 開始位置 (週用、スケジュール用)
 *
 * @var int
 */
	const	CALENDAR_START_POS_WEEKLY_TODAY = 0;					//今日
	const	CALENDAR_START_POS_WEEKLY_YESTERDAY = 1;				//前日

/**
 * 表示日数（最小、最大）
 *
 * @var int
 */
	const	CALENDAR_MIN_DISPLAY_DAY_COUNT = 1;					//最小表示日数
	const	CALENDAR_STANDARD_DISPLAY_DAY_COUNT = 3;			//標準表示日数
	const	CALENDAR_MAX_DISPLAY_DAY_COUNT = 14;				//最大表示日数

/**
 * 単一日タイムライン基準時
 *
 * @var int
 */
	const	CALENDAR_TIMELINE_MIN_TIME = 0;							//最小時刻(00:00)
	const	CALENDAR_TIMELINE_DEFAULT_BASE_TIME = 8;				//標準時刻(08:00)
	const	CALENDAR_TIMELINE_MAX_TIME = 16;						//最大時刻(16:00)

/**
 * 施設予約承認
 *
 * @var int
 */
	const	CALENDAR_USE_WORKFLOW = '1';					//使う
	const	CALENDAR_NOT_USE_WORKFLOW = '0';				//使わない

/**
 * 施設予約コンテンツ長さ
 *
 * @var int
 */
	const	CALENDAR_VALIDATOR_TITLE_LEN = 100;
	const	CALENDAR_VALIDATOR_TEXTAREA_LEN = 60000;
	const	CALENDAR_VALIDATOR_GENERAL_VCHAR_LEN = 255;

/**
 * メール通知タイミング
 *
 * @var int
 */
	//メール通知タイミング初期値(60分前=1時間前)
	const	CALENDAR_DEFAULT_MAIL_SEND_TIME = 60;

/**
 * xdebug.max_nesting_levelのカレンダ用上限値
 *
 * @var int
 */
	//xdebug.max_nesting_levelのカレンダ用上限値。但し、Xdebugが入っている環境の時だけ意味がある値。
	const	CALENDAR_XDEBUG_MAX_NESTING_LEVEL = 1000;

/**
 * 繰り返し上限
 *
 * @var string
 */
	const CALENDAR_RRULE_COUNT_MAX = '366';
	const CALENDAR_RRULE_COUNT_MIN = '1';
	const CALENDAR_RRULE_TERM_UNTIL_MAX = '2033-12-31 23:59:59';
	const CALENDAR_RRULE_TERM_UNTIL_MIN = '2001-01-01 00:00:00';
	const CALENDAR_RRULE_TERM_UNTIL_TM_MAX = 2019686399;
	const CALENDAR_RRULE_TERM_UNTIL_TM_MIN = 978307200;
	const CALENDAR_RRULE_TERM_UNTIL_YEAR_MAX = 2033;
	const CALENDAR_RRULE_TERM_UNTIL_YEAR_MIN = 2001;

/**
 * 繰返し周期
 *
 * @var string
 */
	const CALENDAR_REPEAT_FREQ_DAILY = 'DAILY';
	const CALENDAR_REPEAT_FREQ_WEEKLY = 'WEEKLY';
	const CALENDAR_REPEAT_FREQ_MONTHLY = 'MONTHLY';
	const CALENDAR_REPEAT_FREQ_YEARLY = 'YEARLY';

/**
 * 繰返し周期(日単位)の日にち間隔
 *
 * @var string
 */
	const CALENDAR_RRULE_INTERVAL_DAILY_MIN = 1;	//最小:1日おき
	const CALENDAR_RRULE_INTERVAL_DAILY_MAX = 6;	//最大:6日おき

/**
 * 繰返し周期(週単位)の週の間隔
 *
 * @var string
 */
	const CALENDAR_RRULE_INTERVAL_WEEKLY_MIN = 1;	//最小:1週おき
	const CALENDAR_RRULE_INTERVAL_WEEKLY_MAX = 6;	//最大:6週おき

/**
 * 繰返し周期(年単位)の年の間隔
 *
 * @var string
 */
	const CALENDAR_RRULE_INTERVAL_YEARLY_MIN = 1;	//最小:1年おき
	const CALENDAR_RRULE_INTERVAL_YEARLY_MAX = 12;	//最大:12年おき

/**
 * 曜日
 *
 * @var string
 */
	const CALENDAR_REPEAT_WDAY = 'SU|MO|TU|WE|TH|FR|SA';

/**
 * 繰返し周期(月単位)の月の間隔
 *
 * @var string
 */
	const CALENDAR_RRULE_INTERVAL_MONTHLY_MIN = 1;	//最小:1ヶ月おき
	const CALENDAR_RRULE_INTERVAL_MONTHLY_MAX = 11;	//最大:11ヶ月おき

/**
 * 繰返しの終了
 *
 * @var string
 */
	const CALENDAR_RRULE_TERM_COUNT = 'COUNT';
	const CALENDAR_RRULE_TERM_UNTIL = 'UNTIL';

/**
 * 繰返しエラー発生キーワード
 *
 * @var string
 */
	const CALENDAR_RRULE_ERROR_HAPPEND = 'reservation_rrule_error_happend';

/**
 * 繰返しの区切り文字
 *
 * @var string
 */
	const CALENDAR_RRULE_PAUSE = ',';

/**
 * フォーマット
 *
 * @var string
 */
	const CALENDAR_DATE_FORMAT = 'Y/m/d';


/**
 * 保存時にデータに負荷する拡張情報の配列キー
 *
 * @val string
 */
	const ADDITIONAL = 'CALENDAR_ADDITIONAL';

/**
 * 予定編集のモード
 *
 * @val string
 */
	const PLAN_ADD = 'add';
	const PLAN_EDIT = 'edit';

/**
 * 「仲間の予定」仮想ルームID
 * @val int
 */
	const FRIEND_PLAN_VIRTUAL_ROOM_ID = 2147483647;	//符号付32bit整数のHigh-Valueとする

/**
 * 施設予約タイムゾーン情報の要素の位置
 *
 * @val integer
 */
	const CALENDAR_TIMEZONE_AREA_NAME = 0;
	const CALENDAR_TIMEZONE_OFFSET_VAL = 1;
	const CALENDAR_TIMEZONE_ID = 2;

/**
 * getTzTbl
 *
 * 施設予約タイムゾーン情報配列取得関数
 *
 * @return array 施設予約タイムゾーン情報配列取得関数
 */
	public static function getTzTbl() {
		$tzTbl = array(
		'_TZ_GMTM12' => array(
			__d('reservations', '(GMT-12:00) Eniwetok, Kwajalein'), -12.0, "Etc/GMT+12"),
		'_TZ_GMTM11' => array(
			__d('reservations', '(GMT-11:00) Midway Island, Samoa'), -11.0, "Pacific/Midway"),
		'_TZ_GMTM10' => array(
			__d('reservations', '(GMT-10:00) Hawaii'), -10.0, "US/Hawaii"),
		'_TZ_GMTM9' => array(
			__d('reservations', '(GMT-9:00) Alaska'), -9.0, "US/Alaska"),
		'_TZ_GMTM8' => array(
			__d('reservations', '(GMT-8:00) Pacific Time (US & Canada)'),
			-8.0, "US/Pacific"),
		'_TZ_GMTM7' => array(__d('reservations', '(GMT-7:00) Mountain Time (US & Canada)'),
			-7.0, "US/Mountain"),
		'_TZ_GMTM6' => array(
			__d('reservations', '(GMT-6:00) Central Time (US & Canada), Mexico City'),
			-6.0, "US/Central"),
		'_TZ_GMTM5' => array(
			__d('reservations', '(GMT-5:00) Eastern Time (US & Canada), Bogota, Lima, Quito'),
			-5.0, "US/Eastern"),
		'_TZ_GMTM4' => array(
			__d('reservations', '(GMT-4:00) Atlantic Time (Canada), Caracas, La Paz'),
			-4.0, "Atlantic/Bermuda"),
		'_TZ_GMTM35' => array(
			__d('reservations', '(GMT-3:30) Newfoundland'), -3.5, "Canada/Newfoundland"),
		'_TZ_GMTM3' => array(
			__d('reservations', '(GMT-3:00) Brasilia, Buenos Aires, Georgetown'),
			-3.0, "Brazil/East"),
		'_TZ_GMTM2' => array(
			__d('reservations', '(GMT-2:00) Mid-Atlantic'), -2.0, "Atlantic/South_Georgia"),
		'_TZ_GMTM1' => array(
			__d('reservations', '(GMT-1:00) Azores, Cape Verde Islands'), -1.0, "Atlantic/Azores"),
		'_TZ_GMT0' => array(
			__d('reservations', '(GMT) Greenwich Mean Time, London, Dublin, Lisbon, Casablanca, Monrovia'),
			0.0, "Etc/Greenwich"),
		'_TZ_GMTP1' => array(
			__d('reservations', '(GMT+1:00) Amsterdam, Berlin, Rome, Copenhagen, Brussels, Madrid, Paris'),
			1.0, "Europe/Amsterdam"),
		'_TZ_GMTP2' => array(
			__d('reservations', '(GMT+2:00) Athens, Istanbul, Minsk, Helsinki, Jerusalem, South Africa'),
			2.0, "Europe/Athens"),
		'_TZ_GMTP3' => array(
			__d('reservations', '(GMT+3:00) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg'),
			3.0, "Asia/Baghdad"),
		'_TZ_GMTP35' => array(__d('reservations', '(GMT+3:30) Tehran'), 3.5, "Asia/Tehran"),
		'_TZ_GMTP4' => array(
			__d('reservations', '(GMT+4:00) Abu Dhabi, Muscat, Baku, Tbilisi'), 4.0, "Asia/Muscat"),
		'_TZ_GMTP45' => array(
			__d('reservations', '(GMT+4:30) Kabul'), 4.5, "Asia/Kabul"),
		'_TZ_GMTP5' => array(
			__d('reservations', '(GMT+5:00) Ekaterinburg, Islamabad, Karachi, Tashkent'),
			5.0, "Asia/Karachi"),
		'_TZ_GMTP55' => array(
			__d('reservations', '(GMT+5:30) Bombay, Calcutta, Madras, New Delhi'),
			5.5, "Asia/Calcutta"),
		'_TZ_GMTP6' => array(
			__d('reservations', '(GMT+6:00) Almaty, Dhaka, Colombo'),
			6.0, "Asia/Almaty"),
		'_TZ_GMTP7' => array(
			__d('reservations', '(GMT+7:00) Bangkok, Hanoi, Jakarta'), 7.0, "Asia/Bangkok"),
		'_TZ_GMTP8' => array(
			__d('reservations', '(GMT+8:00) Beijing, Perth, Singapore, Hong Kong, Urumqi, Taipei'),
			8.0, "Asia/Singapore"),
		'_TZ_GMTP9' => array(
			__d('reservations', '(GMT+9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk'), 9.0, "Asia/Tokyo"),
		'_TZ_GMTP95' => array(
			__d('reservations', '(GMT+9:30) Adelaide, Darwin'), 9.5, "Australia/Adelaide"),
		'_TZ_GMTP10' => array(
			__d('reservations', '(GMT+10:00) Brisbane, Canberra, Melbourne, Sydney, Guam,Vlasdiostok'),
			10.0, "Australia/Brisbane"),
		'_TZ_GMTP11' => array(
			__d('reservations', '(GMT+11:00) Magadan, Solomon Islands, New Caledonia'), 11.0, "Etc/GMT-11"),
		'_TZ_GMTP12' => array(
			__d('reservations', '(GMT+12:00) Auckland, Wellington, Fiji, Kamchatka, Marshall Island'),
			12.0, "Pacific/Auckland"),
		);
		return $tzTbl;
	}
}
