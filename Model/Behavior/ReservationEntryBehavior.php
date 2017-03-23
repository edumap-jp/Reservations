<?php
/**
 * ReservationEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');

/**
 * ReservationEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationEntryBehavior extends ReservationAppBehavior {

/**
 * Default settings
 *
 * 値が変わった時、発動する。
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		//'reservationRruleModel' => 'Reservations.ReservationRrule',
		//'fields' => array(
		//	'rrule_id' => 'reservation_rrule_id',
		//	),
		);

/**
 * Setup
 *
 * @param Model $model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(Model $model, $config = array()) {
		$this->settings[$model->alias] = Hash::merge($this->_defaults, $config);
	}

/**
 * Checks wether model has the required fields
 *
 * @param Model $model instance of model
 * @return bool True if $model has the required fields
 */
	protected function _hasReservationEntryFields(Model $model) {
		$fields = $this->settings[$model->alias]['fields'];
		return $model->hasField($fields['description']) && $model->hasField($fields['start_date']);
	}

	///**
	//* Bind relationship on the fly
	// *
	// * @param Model $model instance of model
	// * @param bool $cascade 削除時のカスケード指定
	// * @return void
	// */
	//public function beforeDelete(Model $model, $cascade = true) {
	//	$this->log("DBG : beforeDelete", LOG_DEBUG);
	//
	//	return parent::beforeDelete($model, $cascade);
	//}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @return void
 */
	public function afterDelete(Model $model) {
		$this->log('DBG : afterDelete', LOG_DEBUG);

		return parent::afterDelete($model);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param array $options オプション配列
 * @return void
 */
	public function beforeSave(Model $model, $options = array()) {
		$this->log('DBG: before Save', LOG_DEBUG);
		if (isset($this->reservationEntryIndicator)) {
			//施設予約登録指示子(insert|update)があれば、それに従う
			//$this->log('DBG: given reservationEntryIndicator[' . $this->reservationEntryIndicator . ']', LOG_DEBUG);
			return parent::beforeSave($model, $options);
		}

		//施設予約指示子がないので、自分で見つけ出す。
		//
		$linkPlugin = Current::read('Plugin.name');
		$linkPluginModel = $model->alias;
		$this->log('DBG: linkPluginModel[' . $linkPluginModel . ']', LOG_DEBUG);

		$vars = get_object_vars($model);
		$mdl = $vars[$model->alias];
		$linkPluginKey = $mdl->data[$model->alias]['key'];
		/* FUJI 意味のない処理？後でカットする
		$frameAndBlockInfo = array(
			'Frame.id' => Current::read('Frame.id'),
			'Block.id' => Current::read('Block.id'),
		);
		$linkPluginOhterInfos = serialize($frameAndBlockInfo);
		$this->log('DBG: linkPluginOhterInfos[' . $linkPluginOhterInfos . ']', LOG_DEBUG);
		*/
		$this->loadEventAndRruleModels($model);
		$params = array(
			'conditions' => array(
				'ReservationEvent.link_plugin' => $linkPlugin,
				//'ReservationEvent.link_plugin_model' => $linkPluginModel,
				'ReservationEvent.link_key' => $linkPluginKey,
			),
			'recursive' => -1,	//belongTo, hasOneの１跨ぎの関係までとってくる。
			'callbacks' => false
		);
		$count = $model->ReservationEvent->find('count', $params);
		if ($count > 0) {
			//既にlinkデータがあるので、update
			$this->reservationEntryIndicator = 'update';
		} else {
			//データがないので、insert
			$this->reservationEntryIndicator = 'insert';
		}
		//$this->log('DBG: i descid reservationEntryIndicator[' . $this->reservationEntryIndicator . ']', LOG_DEBUG);

		return parent::beforeSave($model, $options);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param bool $created 生成しかたどうか
 * @param array $options オプション配列
 * @return void
 */
	public function afterSave(Model $model, $created, $options = array()) {
		$this->log("DBG : afterSave", LOG_DEBUG);
		$this->log(
			'DBG: reservationEntryIndicator is[' . $this->reservationEntryIndicator . ']', LOG_DEBUG
		);
		//$this->log("DBG : All Current Props[" . print_r( Current::read(), true). "]", LOG_DEBUG);
		return;

		/*
		 * 以下、コーディング中。
		 *
		if (!$this->_hasReservationEntryFields($model)) {
			$this->log("DBG : nop", LOG_DEBUG);
			return;
		}

		$this->log("DBG : OK created[" . $created . "]", LOG_DEBUG);
		//$this->log("DBG :" . serialize($model->data), LOG_DEBUG);

		$fields = $this->settings[$model->alias]['fields'];
		//$this->log("DBG : description[" . $model->data[$model->alias][$fields['description']] . "]", LOG_DEBUG);
		//$this->log("DBG : start_date[" . $model->data[$model->alias][$fields['start_date']] . "]", LOG_DEBUG);

		if (!$model->Behaviors->hasMethod('insertPlan')) {
			$model->Behaviors->load('Reservations.ReservationInsertPlan');
		}
		$planParams = array(
			'description' => $model->data[$model->alias][$fields['description']],
			'start_date' => $model->data[$model->alias][$fields['start_date']],
		);

		$this->log("DBG : planParams[" . serialize($planParams) . "]", LOG_DEBUG);
		$model->insertPlan($planParams);
		*/
	}
}
