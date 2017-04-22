<?php
class AlterLocationAddUseWorkflow extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'alter_location_add_use_workflow';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_field' => array(
				'reservation_locations' => array(
					'use_workflow' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'after' => 'use_all_rooms'),
				),
			),
		),
		'down' => array(
			'drop_field' => array(
				'reservation_locations' => array('use_workflow'),
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
