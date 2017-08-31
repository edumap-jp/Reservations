<?php
/**
 * ReservationLocationValidationBehavior.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Class ReservationLocationValidateBehavior
 */
class ReservationLocationValidateBehavior extends ReservationAppBehavior {

/**
 * 施設管理者のバリデート
 *
 * @param Model $model ReservationLocation
 * @param array $check チェック対象データ
 * @return bool
 */
	public function validateSelectUser($model, $check) {
		if ($model->data['ReservationLocation']['use_workflow']) {
			// 承認必要なら承認者必須
			$users = Hash::get($model->data, 'ReservationLocationsApprovalUser', false);
			if ($users) {
				return (count($users) > 0);
			}
			return false;
		}
		return true;
	}
}