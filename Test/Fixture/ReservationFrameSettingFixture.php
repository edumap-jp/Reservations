<?php
/**
 * ReservationFrameSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for ReservationFrameSettingFixture
 */
class ReservationFrameSettingFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'frame_key' => 'frame_3',
			'display_type' => 1,
			'start_pos' => 1,
			'display_count' => 3,
			'is_myroom' => 1,
			'is_select_room' => 1,
			'room_id' => '2',
			'timeline_base_time' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:18',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:18'
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
