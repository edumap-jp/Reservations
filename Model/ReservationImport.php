<?php
/**
 * ReservationImport.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationImport
 */
class ReservationImport extends AppModel {

/**
 * @var bool useTable
 */
	public $useTable = false;

/**
 * @var string Alias
 */
	public $alias = 'ReservationActionPlan';

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
		$this->validate = ValidateMerge::merge($this->validate, array(
			'csv_file' => array(
				'rule1' => array(
					'rule' => array('extension', ['csv']),
					'message' => __d('reservations', 'Please select csv file.'),
				),
			),
		));
	}
}