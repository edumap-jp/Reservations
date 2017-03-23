<?php
/**
 * ReservationEventContent Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');

/**
 * Reservation Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationEventContent extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.Trackable',
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'ReservationEvent' => array(
			'className' => 'Reservations.ReservationEvent',
			'foreignKey' => 'reservation_event_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
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
		$this->validate = Hash::merge($this->validate, array(
			'model' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(
						__d('net_commons', 'Please input %s.'), __d('reservations', 'Model Name')
					),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'content_key' => array(
				'numeric' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(
						__d('net_commons', 'Please input %s.'), __d('reservations', 'content key')
					),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'reservation_event_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'),
						__d('reservations', 'reservation_event id')),
					'allowEmpty' => false,
					'required' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * saveLinkedData
 *
 * 施設予約イベントコンテンツ登録
 *
 * @param array $rEventData イベントデータ
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed 成功時はModel::data、失敗時はfalse
 * @throws InternalErrorException
 */
	public function saveLinkedData($rEventData, $createdUserWhenUpd = null) {
		$data = false;
		$this->begin();
		try {
			$options = array(
				'conditions' => array(
					$this->alias . '.model' => $rEventData[$this->alias]['model'],
					$this->alias . '.content_key' => $rEventData[$this->alias]['content_key'],
				)
			);
			$data = $this->find('first', $options);
			if (! $data) {
				//modelとcontent_key一致データなし。なので、insert
				$data = $this->create();
				$data[$this->alias]['model'] = $rEventData[$this->alias]['model'];
				$data[$this->alias]['content_key'] = $rEventData[$this->alias]['content_key'];
				//これだけは親モデル
				$data[$this->alias]['reservation_event_id'] = $rEventData['ReservationEvent']['id'];
			} else {
				//modelとcontent_key一致データあり。なので、reservation_event_idを更新する。
				//これだけは親モデル
				$data[$this->alias]['reservation_event_id'] = $rEventData['ReservationEvent']['id'];
			}

			//施設予約独自の例外追加１）
			//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
			//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
			//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
			if ($createdUserWhenUpd !== null) {
				$data[$this->alias]['created_user'] = $createdUserWhenUpd;
			}

			if (! $this->save($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
			throw $ex;	//再throw
		}
		return $data;
	}
}
