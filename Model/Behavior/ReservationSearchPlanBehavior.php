<?php
/**
 * ReservationSearchPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior');
App::uses('Space', 'Rooms.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');
/**
 * ReservationSearchPlanBehavior
 *
 * @property array $reservationWdayArray reservation weekday array 施設予約曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Reservations\Model\Behavior
 */
class ReservationSearchPlanBehavior extends ReservationAppBehavior {

/**
 * getPlans
 *
 * 予定一覧の取得
 * (共有予定とのマージ処理を簡易化するため$order は key => valの１要素のみ、ASC, DESC指定はないものとする)
 *
 * @param Model &$model 実際のモデル名
 * @param array $vars 施設予約設定情報
 * @param array $planParams  予定パラメータ
 * @param array $order ソートパラメータ
 * @return array 検索成功時 予定配列を返す。検索結果が０件の時は、空配列を返す。検索失敗した時はInternalError例外をthrowする。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function getPlans(Model &$model, $vars, $planParams, $order = array()) {
		// 探すのはis_activeかis_latestのものだけでよい
		$baseOptions = array(
			'conditions' => array(
				array(
					'OR' => array(
						'ReservationEvent.is_active' => true,
						'ReservationEvent.is_latest' => true,
					)
				)
			),
			'recursive' => 1,		//belongTo, hasOne, hasMany関係をもつ１階層上下を対象にする。
			//'order' => array($model->alias . '.start_date'),
			'order' => $order,
		);
		foreach ($planParams as $field => $val) {
			$key = $model->alias . '.' . $field;
			$field = trim($field);
			switch ($field) {
				case 'dtstart':
					$key = $key . ' >=';
					break;
				case 'dtend':
					$key = $key . ' <';
					break;
				case 'language_id':
					$key = 1;
					$vals = array(
						'OR' => array(
							$model->alias . '.' . $field => $val,
							$model->alias . '.is_translation' => false
						)
					);
					$val = $vals;
					break;
				default:
			}
			$baseOptions['conditions'][$key] = $val;
		}

		/////////////////////////////////////////
		// 時間以外の絞り込み条件をここに書く。

		//表示対象となるルームIDの一覧を取得し、IN条件を追加する.
		//
		$eventDotRoomId = $model->alias . '.room_id';

		//施設予約用の役割・権限情報一式取得
		if (!$model->Behaviors->hasMethod('prepareCalRoleAndPerm')) {
			$model->Behaviors->load('Reservations.ReservationRoleAndPerm');
		}
		$calRoleAndPerm = $model->prepareCalRoleAndPerm();

		//専用の各参照配列に代入
		$readableRoomIds = &$calRoleAndPerm['readableRoomIds'];
		//$rooms = &$calRoleAndPerm['rooms'];
		//$roleOfRooms = &$calRoleAndPerm['roleOfRooms'];
		//$roomInfos = &$calRoleAndPerm['roomInfos'];

		$options = $baseOptions;
		$options['conditions'][$eventDotRoomId] = $readableRoomIds;

		////$plans = $model->getWorkflowContents('all', $options);
		$plans = $model->find('all', $options);
		/*
		CakeLog::debug("DBG: options[" . print_r($options, true) . "]\n");
		foreach ($plans as $plan) {
			CakeLog::debug("DBG: event id[" . $plan['ReservationEvent']['id'] .
				"] key[" . $plan['ReservationEvent']['key'] .
				"] dtstart[" . $plan['ReservationEvent']['dtstart'] .
				"] room_id[" . $plan['ReservationEvent']['room_id'] .
				"] title[" . $plan['ReservationEvent']['title'] .
				"] status[" . $plan['ReservationEvent']['status'] .
				"] is_active[" . $plan['ReservationEvent']['is_active'] .
				"] is_latest[" . $plan['ReservationEvent']['is_latest'] .
				"] created_user[" . $plan['ReservationEvent']['created_user'] .
				"] modified_user[" . $plan['ReservationEvent']['modified_user'] .
				"]\n\n");
		}
		*/
		////plansを$calRoleAndPerm中の各配列を使いスクリーニングする
		////＝＞screenPlansUsingGetable方式に変えたので以下はやめた HASHI
		////$plans = $this->__screenPlans($model, $plans, $calRoleAndPerm);
		//スクリーニング方法をReservationEventのGetableEvnet()を使う方法に変えた HASHI
		//FUJI$roomPermRoles = $model->prepareCalRoleAndPerm();
		//FUJIReservationPermissiveRooms::$roomPermRoles = $roomPermRoles;
		$plans = $model->screenPlansUsingGetable($plans);

		$sharePlans = $this->_getSharePlans($model, $vars, $baseOptions);
		/////////////////////////////////////////
		//通常予定と仲間の予定をマージする。
		$mergedPlans = $this->__mergePlans($plans, $sharePlans, $order);

		return $mergedPlans;
	}

/**
 * _getSharePlans
 *
 * 共有予定を取得する
 *
 * @param Model $model モデル
 * @param array $vars 施設予約情報
 * @param array $baseOptions 基本検索条件
 * @return array
 */
	protected function _getSharePlans($model, $vars, $baseOptions) {
		if ($vars['ReservationFrameSetting']['is_myroom'] == false) {
			return array();
		}
		///////////////////////////////////////////////////////////////////////
		//自ユーザーを共有指定している他人のプライベート予定をとってくる。
		//ここは、上記のルームIDの範疇外になるので、別にfindして、plansに
		//マージすることとする。自分のルームID以外で、かつ、ShareUserが自分である
		//情報を取得する。

		if (empty($vars['myself'])) {
			return array();
		}
		$myself = $vars['myself'];

		//optionsの再設定
		$options = Hash::merge(array(
			//'recursive' => 1,	//baseOptionsで指定済
			'fields' => array(
				$model->alias . '.*',
				$model->ReservationEventShareUser->alias . '.*',
				////'count(' . $model->ReservationEventShareUser->alias . '.id) AS friend_share_plan',

				$model->ReservationRrule->alias . '.*',
				$model->Language->alias . '.*',
				$model->TrackableCreator->alias . '.id',
				$model->TrackableCreator->alias . '.handlename',
				$model->TrackableUpdater->alias . '.id',
				$model->TrackableUpdater->alias . '.handlename',
				//$model->ReservationEventContent->alias . '.*',
			),
			'conditions' => array(
				$model->ReservationEventShareUser->alias . '.id NOT' => null,
				//$model->alias . '.room_id' => $privateRoomIds,
				'NOT' => array(
						$model->alias . '.room_id' => $myself
				), // 自分以外のルーム
				$model->ReservationEventShareUser->alias . '.share_user' => Current::read('User.id'),
			),
			'joins' => array(
				array(
					'table' => $model->ReservationEventShareUser->table,
					'alias' => $model->ReservationEventShareUser->alias,
					'type' => 'LEFT',
					'conditions' => array(
						$model->ReservationEventShareUser->alias . '.reservation_event_id' .
						' = ' .
						$model->alias . '.id',
					),
				),
			),
			//'order' => $order,	//baseOptionsで指定済
		), $baseOptions);

		//Workflow条件について）
		//仲間の予定は、他人のプライベート予定なので、
		//getWorkflowContents()を使うと、一時保存中の他人のプライベート予定もとってきてしまう。
		//他人のプライベート予定の場合、
		//status=発行済かつis_active=trueのものだけが表示可能としてよい。
		//よって、conditionsにその条件を明示追加する。
		//
		$options['conditions'][$model->alias . '.status'] = WorkflowComponent::STATUS_PUBLISHED;
		$options['conditions'][$model->alias . '.is_active'] = true;
		$options['conditions'][$model->alias . '.exception_event_id'] = 0;	//除外でないもの

		$sharePlans = $model->getWorkflowContents('all', $options);
		foreach ($sharePlans as &$sharePlan) {
			//「仲間の予定」であることを擬似項目をつかって、マーキングしておく。
			$sharePlan['ReservationEvent']['pseudo_friend_share_plan'] = 1;
		}
		return $sharePlans;
	}
/**
 * __mergePlans
 *
 * 通常の表示予定と共有された他人のプライベート予定のマージ関数
 * (共有予定とのマージ処理を簡易化するため$order は key => valの１要素のみ、ASC, DESC指定はないものとする)
 *
 * @param array $plansA plansA
 * @param array $plansB plansB
 * @param array $order ソートパラメータ
 * @return array マージした配列を返す。
 * @SuppressWarnings(PHPMD)
 */
	private function __mergePlans($plansA, $plansB, $order) {
		list($orderModel, $orderField) = explode('.', array_shift($order));
		$planA = array_shift($plansA);
		$planB = array_shift($plansB);
		$mergedPlans = array();
		while ($planA !== null || $planB !== null) {
			if ($planA === null) {
				//plansAは終わった
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planB)) {
					$mergedPlans[] = $planB;
				}
				$planB = array_shift($plansB);
				continue;
			}

			if ($planB === null) {
				//plansBは終わった
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planA)) {
					$mergedPlans[] = $planA;
				}
				$planA = array_shift($plansA);
				continue;
			}

			if ($planA[$orderModel][$orderField] < $planB[$orderModel][$orderField]) {
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planA)) {
					$mergedPlans[] = $planA;
				}
				$planA = array_shift($plansA);
			} else {
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planB)) {
					$mergedPlans[] = $planB;
				}
				$planB = array_shift($plansB);
			}
		}

		return $mergedPlans;
	}

/**
 * __overwriteSameKeyEvent
 *
 * $mergedPlansの中に、$planと同一キーでid値がより大きいeventがあればplanを上書きする
 *
 * @param array &$mergedPlans mergedPlans
 * @param array $plan plan
 * @return bool 上書き実行されればtrue。上書き実行されなければfalse。
 */
	private function __overwriteSameKeyEvent(&$mergedPlans, $plan) {
		foreach ($mergedPlans as &$mergedPlan) {
			if ($mergedPlan['ReservationEvent']['key'] == $plan['ReservationEvent']['key']) {
				if ($mergedPlan['ReservationEvent']['id'] < $plan['ReservationEvent']['id']) {
					//key一致で後出のeventのidの方が大きければ上書きして抜ける。
					$mergedPlan = $plan;
					return true;
				}
			}
		}
		return false;
	}

}
