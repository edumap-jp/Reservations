<?php
/**
 * ReservationFrameSetting Model
 *
 * @property Room $Room
 * @property ReservationFrameSetting $ReservationFrameSetting
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationsAppModel', 'Reservations.Model');
App::uses('ReservationsComponent', 'Reservations.Controller/Component');	//constを使うため
App::uses('Space', 'Rooms.Model');

/**
 * ReservationFrameSetting Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\Model
 */
class ReservationFrameSetting extends ReservationsAppModel {

/**
 * getFrameSetting関数が何度も繰り返し呼び出された時のための保持変数
 *
 * @var array
 */
	protected $_getFrameSettingData = array();

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',	// key,origin_id あったら動作し、
									// なくても無害なビヘイビア

		'NetCommons.Trackable',	// TBLが Trackable項目セット(created_user＋modified_user)を
								// もっていたらTrackable(人の追跡可能）とみなされる。
								// Trackableとみなされたたら、created_userに対応する
								// username,handle(TrackableCreator)が、
								// modified_userに対応するusername,hanldle(TrackableUpdator)が、
								// belongToで自動追加され、取得データにくっついてくる。
								// なお、created_user, modified_userがなくても無害なビヘイビアである。

		//'Workflow.Workflow',	// TBLに 承認項目セット
								// (status + is_active + is_latest + language_id + (origin_id|key) )があれば、
								// 承認TBLとみなされる。
								// 承認TBLのINSERTの時だけ働く。UPDATEの時は働かない。
								// status===STATUS_PUBLISHED（公開）の時だけINSERTデータのis_activeがtrueになり、
								//	key,言語が一致するその他のデータはis_activeがfalseにされる。
								// is_latestは(statusに関係なく)INSERTデータのis_latestがtrueになり、
								//	key,言語が一致するその他のデータはis_latestがfalseにされる。
								//
								// なお、承認項目セットがなくても無害なビヘイビアである。

		//'Workflow.WorkflowComment', // $model->data['WorkflowComment'] があれば働くし、
								// なくても無害なビヘイビア。
								// $model->data['WorkflowComment'] があれば、このTBLにstatusがあること
								//（なければ、status=NULLで突っ込みます）

		'Reservations.ReservationValidate',
		'Reservations.ReservationApp',	//baseビヘイビア
		'Reservations.ReservationInsertPlan', //Insert用
		'Reservations.ReservationUpdatePlan', //Update用
		'Reservations.ReservationDeletePlan', //Delete用
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Frame' => array(
			'className' => 'Frames.Frame',
			'foreignKey' => 'frame_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

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
			'display_type' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invalid request.'),
				),
				'rule2' => array(
					'rule' => array('inList', ReservationsComponent::$reservationTypes),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'timeline_base_time' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invalid request.'),
				),
				'rule2' => array(
					'rule' => array('comparison', '>=', ReservationsComponent::CALENDAR_TIMELINE_MIN_TIME),
					'message' => __d('net_commons', 'Invalid request.'),
				),
				'rule3' => array(
					'rule' => array('comparison', '<=', ReservationsComponent::CALENDAR_TIMELINE_MAX_TIME),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'display_start_time_type' => array(
				'rule1' => array(
					'rule' => 'boolean',
					'required' => true,
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * saveFrameSetting
 *
 * @param array $data save data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveFrameSetting($data) {
		//トランザクションBegin
		$this->begin();
		try {
			// フレーム設定のバリデート
			$this->set($data);
			if (! $this->validates()) {
				CakeLog::error(serialize($this->validationErrors));

				$this->rollback();
				return false;
			}

			//フレームの登録
			//バリデートは前で終わっているので第二引数=false
			$data = $this->save($data, false);
			if (! $data) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			$this->commit();
		} catch (Exception $ex) {
			$this->rollback($ex);
		}

		return $data;
	}

/**
 * setDefaultValue
 *
 * @param array &$data save data
 * @return void
 * @throws InternalErrorException
 */
	public function setDefaultValue(&$data) {
		$default = $this->getDefaultFrameSetting();
		$data = Hash::merge($data, $default);
	}

/**
 * getFrameSetting
 *
 * @return array 施設予約表示形式情報
 */
	public function getFrameSetting() {
		$frameId = Current::read('Frame.id');
		if (isset($this->_getFrameSettingData[$frameId])) {
			return $this->_getFrameSettingData[$frameId];
		}
		$frameSetting = $this->find('first', array(
			'recursive' => -1,
			'conditions' => array('frame_key' => Current::read('Frame.key'))
		));
		if (! $frameSetting) {
			$frameSetting = $this->getDefaultFrameSetting();
		}
		$this->_getFrameSettingData[$frameId] = $frameSetting;
		return $frameSetting;
	}

/**
 * getDefaultFrameSetting
 *
 * @return array 施設予約表示形式デフォルト情報
 */
	public function getDefaultFrameSetting() {
		//frame_key,room_idは明示的に設定されることを想定し、setDefaultではなにもしない。
		return $this->create(array(
			$this->alias => array(
				'display_type' => ReservationsComponent::RESERVATION_DISP_TYPE_DEFAULT,
				'timeline_base_time' => ReservationsComponent::CALENDAR_TIMELINE_DEFAULT_BASE_TIME,
				'id' => null,
			)
		));
	}
}
