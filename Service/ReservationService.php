<?php
/**
 * ReservationService.php
 *
 * @author   Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('ReservationRepeatService', 'Reservations.Service');

/**
 * Class ReservationService
 */
class ReservationService {

/**
 * 施設に対する予約が重複している日付を返す。
 * 重複がなければ空配列
 *
 * @param string $locationKey 施設キー
 * @param string $startDateTime 開始日時
 * @param string $endDateTime 終了日時
 * @param string $timezone タイムゾーン
 * @param array $rrule rrule
 * @return array 予約が重複している日付のリスト
 */
	public function getOverlapReservationDate(
		$locationKey,
		$startDateTime,
		$endDateTime,
		$timezone,
		$rrule
	) {
		$overlapDates = [];

		//繰り返しの日付リストを生成
		$repeatService = new ReservationRepeatService();
		$startDate = date('Y-m-d', strtotime($startDateTime));
		$timeLength = strtotime($endDateTime) - strtotime($startDateTime); // 予約の時間幅

		$repeatDateSet = $repeatService->getRepeatDateSet($rrule, $startDate);
		// 日付、開始時刻、終了時刻にわける
		$startTime = date('H:i:s', strtotime($startDateTime));

		// 繰り返しの数だけ重複をチェックする
		foreach ($repeatDateSet as $checkDate) {
			// 繰り返し生成日付＋時刻でチェックする開始日時、終了日時を生成
			$checkStartDateTime = $checkDate . ' ' . $startTime;
			$checkEndDateTime = date('Y-m-d H:i:s', strtotime($checkStartDateTime) + $timeLength);
			if ($this->_existOverlapReservation(
				$locationKey,
				$checkStartDateTime,
				$checkEndDateTime,
				$timezone
			)
			) {
				// 重複予約があれば重複日リストに追加
				$overlapDates[] = $checkDate;
			}
		}
		return $overlapDates;
	}

/**
 * 重複する予約があるかチェック
 *
 * @param string $locationKey 施設キー
 * @param string $startDateTime 開始日時
 * @param string $endDateTime 終了日時
 * @param string $inputTimeZone 入力日時のタイムゾーン
 * @return bool true 重複有り　false 重複無し
 */
	protected function _existOverlapReservation(
		$locationKey,
		$startDateTime,
		$endDateTime,
		$inputTimeZone
	) {
		// この時点ではユーザタイム

		// サーバタイムに変換
		// $locationKeyで指定された施設に対して予約があるかをis_active=1 or (is_latest =1 AND 承認待ち）の中からさがす
		// └is_latestも入れてるのは、未承認の仮予約でも重複させないため。

		// サーバタイムに変換
		$netCommonsTime = new NetCommonsTime();
		$startDateTime = $netCommonsTime->toServerDatetime($startDateTime, $inputTimeZone);
		$endDateTime = $netCommonsTime->toServerDatetime($endDateTime, $inputTimeZone);

		$startDateTime = date('YmdHis', strtotime($startDateTime));
		$endDateTime = date('YmdHis', strtotime($endDateTime));
		// 存在チェック
		$this->loadModels(['ReservationEvent' => 'Reservations.ReservationEvent']);
		$conditions = [
			'ReservationEvent.location_key' => $locationKey,
			// workflow
			[
				// isActive
				// isLatestは承認申請中だけ（差し戻しと一時保存はチェックしない）
				'OR' => [
					'ReservationEvent.is_active' => 1,
					[
						'ReservationEvent.is_latest' => 1,
						'ReservationEvent.status' => WorkflowComponent::STATUS_APPROVAL_WAITING,
					]
				]
			],
			[
				'OR' => [
					[
						// 始点が指定した範囲にあったら時間枠重複
						'ReservationEvent.dtstart >' => $startDateTime,
						'ReservationEvent.dtstart <' => $endDateTime,
					],
					[
						// 終点が指定した範囲にあったら時間枠重複
						'ReservationEvent.dtend >' => $startDateTime,
						'ReservationEvent.dtend <' => $endDateTime,

					],
					[
						// 始点、終点ともそれぞれ指定範囲の前と後だったら時間枠重複
						'ReservationEvent.dtstart <' => $startDateTime,
						'ReservationEvent.dtend >' => $endDateTime,
					],
				]
			],

		];
		$exist = $this->ReservationEvent->find('count', ['conditions' => $conditions]);
		return ($exist) ? true : false;
	}
}