<?php
/**
 * ReservationFixture
 *
* @author Noriko Arai <arai@nii.ac.jp>
* @author Your Name <yourname@domain.com>
* @link http://www.netcommons.org NetCommons Project
* @license http://www.netcommons.org/license.txt NetCommons License
* @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for ReservationFixture
 */
class ReservationFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => true, 'key' => 'primary'),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'イベントKey', 'charset' => 'utf8'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ルームID'),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => '言語ID'),
		'is_origin' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'オリジナルかどうか'),
		'is_translation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '翻訳したかどうか'),
		'is_original_copy' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'オリジナルのコピー。言語を新たに追加したときに使用する'),
		'location_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true, 'key' => 'index'),
		'calendar_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
		'title' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title_icon' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_allday' => array('type' => 'boolean', 'null' => true, 'default' => '1', 'comment' => '終日かどうか | 0:終日ではない | 1:終日'),
		'start_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => '開始日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => '開始時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtstart' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '開始日時 (YYYYMMDDhhmmss) iCalendarのDTDSTARTからTとZを外したもの', 'charset' => 'utf8'),
		'end_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => '終了日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => '終了時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtend' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => '終了日時 (YYYYMMDDhhmmss形式) iCalendarのDTENDからTとZをはずしたもの', 'charset' => 'utf8'),
		'timezone_offset' => array('type' => 'float', 'null' => false, 'default' => '0.0', 'length' => '3,1', 'unsigned' => false, 'comment' => 'タイムゾーンオフセット-12.0～+12.0'),
		'description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '詳細', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => '公開状況  1:公開中>、2:公 開申請中、3:下書き中、4:差し戻し'),
		'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'アクティブなコンテンツかどうか 0:アクティブでない 1:アクティブ'),
		'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '最新コンテンツかどうか 0:最新でない 1:最新'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'location_id' => array('column' => array('location_id', 'dtstart'), 'unique' => 0),
			'room_id' => array('column' => array('room_id', 'location_id'), 'unique' => 0),
			'dt' => array('column' => array('dtstart', 'dtend'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'key' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'is_origin' => 1,
			'is_translation' => 1,
			'is_original_copy' => 1,
			'location_id' => 1,
			'calendar_id' => 1,
			'title' => 'Lorem ipsum dolor sit amet',
			'title_icon' => 'Lorem ipsum dolor sit amet',
			'is_allday' => 1,
			'start_date' => 'Lorem ',
			'start_time' => 'Lore',
			'dtstart' => 'Lorem ipsum ',
			'end_date' => 'Lorem ',
			'end_time' => 'Lore',
			'dtend' => 'Lorem ipsum ',
			'timezone_offset' => 1,
			'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'status' => 1,
			'is_active' => 1,
			'is_latest' => 1,
			'created_user' => 1,
			'created' => '2017-02-18 15:36:26',
			'modified_user' => 1,
			'modified' => '2017-02-18 15:36:26'
		),
	);

}
