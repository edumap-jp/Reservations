<?php
/**
 * ReservationRrule Model
 *
 * @property Block $Block
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * ReservationRrule Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationRrule extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'NetCommons.Trackable',
		////'Workflow.WorkflowComment',
		////'Workflow.Workflow',
		'Reservations.ReservationValidate',
		'Reservations.ReservationApp',	//base
		'Reservations.ReservationInsertPlan', //Insert
		'Reservations.ReservationUpdatePlan', //Update
		'Reservations.ReservationDeletePlan', //Delete
		//'Blocks.Block' => array( // 施設予約ではブロック1つなんで削除 RyujiAMANO
		//	//nameの値がBlockモデルの名称として登録される。
		//	'name' => 'ReservationRrule.name',
		//	//'loadModels' => array(			//Blockと紐づく（アソシエーションのある）
		//	//								//他のモデルがあれば、loadModelsで指定しておくと。
		//	//								//ブロックデータ登録時、指定モデルのblock_id,block_key
		//	//								//に値を自動セットしてくれる。
		//	//								//フロックデータ削除時、指定モデルから削除してくれる。
		//	//								//
		//	//	'WorkflowComment' => 'Workflow.WorkflowComment',
		//	//								//Reservationの場合、WorkflowCommentがそれなので指定する。
		//	//								//
		//	//								//仕様詳細はBlocks/Model/Behavior/BlockBehavior.php参照のこと。
		//	//),
		//),
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Reservation' => array(
			'className' => 'Reservations.Reservation',
			'foreignKey' => 'reservation_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		//'Language' => array(
		//	'className' => 'Languages.Language',
		//	'foreignKey' => 'language_id',
		//	'conditions' => '',
		//	'fields' => '',
		//	'order' => ''
		//),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'ReservationEvent' => array(
			'className' => 'Reservations.ReservationEvent',
			'foreignKey' => 'reservation_rrule_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('id' => 'ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		$this->loadModels([
			'ReservationRrule' => 'Reservations.ReservationRrule',
		]);
	}

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = ValidateMerge::merge($this->validate, array(
			'reservation_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'room_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			//langauge_id, status, is_active, is_latestは削除した。
			//'language_id' => array(
			//	'rule1' => array(
			//		'rule' => array('numeric'),
			//		'message' => __d('net_commons', 'Invalid request.'),
			//	),
			//),
			//'status' => array(
			//	'rule1' => array(
			//		'rule' => array('numeric'),
			//		'message' => __d('net_commons', 'Invalid request'),
			//		'required' => true,
			//	),
			//),
			//'is_active' => array(
			//	'rule1' => array(
			//		'rule' => 'boolean',
			//		'message' => __d('net_commons', 'Invalid request'),
			//	),
			//),
			//'is_latest' => array(
			//	'rule1' => array(
			//		'rule' => 'boolean',
			//		'message' => __d('net_commons', 'Invalid request'),
			//	),
			//),
		));
		return parent::beforeValidate($options);
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @throws InternalErrorException
 */
}
