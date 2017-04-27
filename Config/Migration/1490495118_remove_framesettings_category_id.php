<?php
/**
 * RemoveFrameSettingsCategoryId
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class RemoveFrameSettingsCategoryId
 */
class RemoveFrameSettingsCategoryId extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'remove_frameSettings_category_id';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'drop_field' => array(
				'reservation_frame_settings' => array('category_id'),
			),
		),
		'down' => array(
			'create_field' => array(
				'reservation_frame_settings' => array(
					'category_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'カテゴリ'),
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
