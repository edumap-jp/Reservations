<?php
class AlterFrameSettings extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'alter_frame_settings';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_frame_settings' => array(
					'category_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'カテゴリ', 'after' => 'display_type'),
					'location_key' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '最初に表示する施設', 'charset' => 'utf8', 'after' => 'category_id'),
					'display_timeframe' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '時間枠表時', 'after' => 'location_key'),
					'display_start_time_type' => array('type' => 'integer', 'null' => true, 'default' => '0', 'length' => 4, 'unsigned' => false, 'comment' => '0:閲覧時刻により変動 1:固定', 'after' => 'display_timeframe'),
					'display_interval' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '表示幅（時間）', 'after' => 'timeline_base_time'),
				),
				'reservation_locations' => array(
					'weight' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true, 'after' => 'use_all_rooms'),
					'indexes' => array(
						'category_id' => array('column' => array('category_id', 'weight'), 'unique' => 0),
					),
				),
			),
			'alter_field' => array(
				'reservation_frame_settings' => array(
					'timeline_base_time' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => '表示開始時'),
				),
			),
			'drop_field' => array(
				'reservation_locations' => array('display_sequence', 'indexes' => array('category_id')),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_frame_settings' => array('category_id', 'location_key', 'display_timeframe', 'display_start_time_type', 'display_interval'),
				'reservation_locations' => array('weight', 'indexes' => array('category_id')),
			),
			'alter_field' => array(
				'reservation_frame_settings' => array(
					'timeline_base_time' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => '単一日タイムライン基準時'),
				),
			),
			'create_field' => array(
				'reservation_locations' => array(
					'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => true),
					'indexes' => array(
						'category_id' => array(),
					),
				),
			),
		),
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
		return true;
	}
}
