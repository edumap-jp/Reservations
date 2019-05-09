<?php
/**
 * Reservation Model
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
App::uses('BlockSettingBehavior', 'Blocks.Model/Behavior');

/**
 * Reservation Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class Reservation extends ReservationsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		//'NetCommons.OriginalKey',
		//'Workflow.WorkflowComment',
		//'Workflow.Workflow',
		//'Blocks.BlockSetting' => array(
		//	BlockSettingBehavior::FIELD_USE_WORKFLOW,
		//),
		'Categories.Category'
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_key',
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
		//'ReservationRrule' => array(
		//	'className' => 'Reservations.ReservationRrule',
		//	'foreignKey' => 'reservation_id',
		//	'dependent' => true,
		//	'conditions' => '',
		//	'fields' => '',
		//	'order' => array('id' => 'ASC'),
		//	'limit' => '',
		//	'offset' => '',
		//	'exclusive' => '',
		//	'finderQuery' => '',
		//	'counterQuery' => ''
		//)
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

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
			'block_key' => array(
				'rule1' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * After frame save hook
 *
 * このルーム・この言語で、アンケートブロックが存在しない場合、Blockモデルにsaveで新規登録する。
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws BadRequestException
 * @throws InternalErrorException
 */
	public function afterFrameSave($data) {
		// すでに結びついている場合は何もしないでよい
		if (!empty($data['Frame']['block_id'])) {
			return $data;
		}

		$this->begin();

		$this->loadModels([
			'Frame' => 'Frames.Frame',
		]);

		try {
			if (empty($data['Frame'])) {
				throw new BadRequestException(__d('net_commons', 'Bad Request'));
			}
			//$frame = $data['Frame'];	//FrameモデルですでにFrameモデルデータは登録済み

			//Blockモデルに存在するか調べる
			$block = $this->getBlock();
			// まだない場合
			if (empty($block)) {
				// ブロックを作成する
				$block = $this->saveBlock();
			} elseif (! $this->_saveReservation($block)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//取得したBlockを$current[Block]に記録しておく。
			Current::$current['Block'] = $block['Block'];

			//Frameモデルに、このブロックのidを記録しておく。 施設予約の場合、Frame:Blockの関係は n:1
			$data['Frame']['block_id'] = $block['Block']['id'];
			if (! $this->Frame->save($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			//新規に生成したBlockのidをCurrent[Frame]に追記しておく。
			Current::$current['Frame']['block_id'] = $block['Block']['id'];

			////このフレーム用の「表示方法変更」「権限設定」「メール設定」のレコードを
			////１セット用意します。
			//
			////このフレームの「表示方法変更」
			//if (! $this->_saveFrameChangeAppearance($data['Frame'])) {
			//	throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			//}

			////権限設定
			//if (! $this->_saveReservation($block)) {
			//	throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			//}
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
		}

		return $data;
	}

/**
 * フレームも何もなくても予定登録のときはこいつをたたいて準備しないといけない
 *
 * @return mixed 見つかった、もしくは作成したブロック
 * @throws InternalErrorException
 */
	public function prepareBlock() {
		//Blockを取得
		$block = $this->getBlock();

		try {
			// まだない場合
			if (empty($block)) {
				$this->begin();

				// ブロックを作成する
				if (! $this->saveBlock()) {
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}

				$this->commit();
			}

		} catch (Exception $ex) {
			$this->rollback($ex);
		}

		Current::$current['Block'] = $block['Block'];

		return $block;
	}

	///**
	// * saveMailSetting
	// *
	// * メール設定データを登録する
	// *
	// * @return mixed On success Model::$data if its not empty or true, false on failure
	// */
	//	protected function _saveMailSetting() {
	//		//$data = $this->_generateMailSettingData();
	//		//$this->MailSetting->set($data);
	//		//if (! $this->MailSetting->validates($data, false)) {
	//		//	CakeLog::error(serialize($this->MailSetting->validationErrors));
	//		//	return false;
	//		//}
	//		//$data = $this->MailSetting->save($data, false);
	//		//if (! $data) {
	//		//	CakeLog::error(serialize($this->MailSetting->validationErrors));
	//		//	return false;
	//		//}
	//		//return $data;
	//		return array();	//暫定
	//	}
	//
	///**
	// * generateMailSettingData
	// *
	// * メール設定データを生成する
	// *
	// * @return array 生成したメール設定データ
	// */
	//	protected function _generateMailSettingData() {
	//		$data = $this->MailSetting->create();
	//		$data = Hash::merge($data,
	//			array(
	//				$this->MailSetting->alias => array(
	//					'block_key' => Current::read('Block.key'),
	//					'plugin_key' => Current::read('Block.plugin_key'),
	//					'type_key' => 'aaa',	//定型文の種類',
	//					'mail_fixed_phrase_subject' => 'bbb',	//定型文 件名
	//					'mail_fixed_phrase_body' => 'ccc',	//定型文 本文
	//					'replay_to' => 'ddd',	//返信先アドレス
	//				)
	//			)
	//		);
	//		return $data;
	//	}
	//
	///**
	// * _saveFrameChangeAppearance
	// *
	// * フレームの「表示方法変更」のデータの登録
	// *
	// * @param array $frame フレーム
	// * @return array 生成したデータ
	// */
	//	protected function _saveFrameChangeAppearance($frame) {
	//		$this->loadModels([
	//			'ReservationFrameSetting' => 'Reservations.ReservationFrameSetting',
	//		]);
	//
	//		$frameKey = $frame['key'];
	//		$frameSetting = $this->ReservationFrameSetting->find('first', array(
	//			'recursive' => -1,
	//			'conditions' => array(
	//				'frame_key' => $frameKey
	//			),
	//		));
	//		if ($frameSetting) {
	//			return $frameSetting;
	//		}
	//		$frameSetting = $this->ReservationFrameSetting->create();
	//		$this->ReservationFrameSetting->setDefaultValue($frameSetting);	//Modelの初期値設定
	//		$frameSetting['ReservationFrameSetting']['frame_key'] = $frame['key'];
	////		$frameSetting['ReservationFrameSetting']['room_id'] = Current::read('Room.id');
	//
	//		return $this->ReservationFrameSetting->saveFrameSetting($frameSetting);
	//	}

/**
 * 施設予約のBlockデータの登録
 *
 * @param array $block ブロック
 * @return array 生成したデータ
 * @throws InternalErrorException
 */
	protected function _saveReservation($block) {
		// 今現在ブロックに対応した施設予約があるか
		$count = $this->find('count', array(
			'recursive' => -1,
			'conditions' => array(
				'block_key' => $block['Block']['key']
			)
		));
		// ない場合は作成する
		if (! $count) {
			$this->set($this->create([
				'block_key' => $block['Block']['key']
			]));
			if (!$this->save()) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
		return true;
	}

	//
	///**
	// * _makeBlock($frame);
	// *
	// * ブロックを作成する
	// *
	// * @param array $frame フレーム
	// * @return array 生成したブロック
	// * @throws InternalErrorException
	// */
	//	protected function _makeBlock($frame) {
	//		$block = $this->Block->save(array(
	//			'room_id' => $frame['room_id'],
	//			'plugin_key' => $frame['plugin_key'],
	//		));
	//		if (! $block) {
	//			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
	//		}
	//		//新規に生成したBlockを$current[Block]に記録しておく。
	//		Current::$current['Block'] = $block['Block'];
	//		return $block;
	//	}

/**
 * ブロックを作成する
 *
 * @return array 生成したブロック
 * @throws InternalErrorException
 */
	public function saveBlock() {
		$this->Block->create();
		$block = $this->Block->save(array(
			'room_id' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID),
			'plugin_key' => Inflector::underscore($this->plugin),
		));
		if (! $block) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		// ない場合は作成する
		if (! $this->_saveReservation($block)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		return $block;
	}

/**
 * ブロックを取得
 *
 * @return array
 */
	public function getBlock() {
		return $this->Block->find('first', [
			'recursive' => -1,
			'conditions' => [
				'room_id' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID),
				'plugin_key' => Inflector::underscore($this->plugin),
			]
		]);
	}

/**
 * ブロックが作成されているかどうか
 *
 * @return bool
 */
	public function isCreatedBlock() {
		return (bool)$this->Block->find('count', [
			'recursive' => -1,
			'conditions' => [
				'room_id' => Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID),
				'plugin_key' => Inflector::underscore($this->plugin),
			]
		]);
	}
}
