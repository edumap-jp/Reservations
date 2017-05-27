<?php
/**
 * reservation plan edit form ( hidden field ) template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$originEventId = $originRruleId = $originEventRecurrence = $originEventException = 0;
	$originEventKey = $originRruleKey = '';
	if (!empty($event)) {
		$originEventId = $event['ReservationEvent']['id'];
		$originEventKey = $event['ReservationEvent']['key'];
		$originEventRecurrence = $event['ReservationEvent']['recurrence_event_id'];
		$originEventException = $event['ReservationEvent']['exception_event_id'];

		$originRruleId = $event['ReservationRrule']['id'];
		$originRruleKey = $event['ReservationRrule']['key'];

		$originCreatedUser = $event['ReservationEvent']['created_user'];
	} else {
		if (!empty($this->request->data['ReservationActionPlan']['origin_event_id'])) {
			$originEventId = $this->request->data['ReservationActionPlan']['origin_event_id'];
		}
		if (!empty($this->request->data['ReservationActionPlan']['origin_event_key'])) {
			$originEventKey = $this->request->data['ReservationActionPlan']['origin_event_key'];
		}
		if (!empty($this->request->data['ReservationActionPlan']['origin_event_recurrence'])) {
			$originEventRecurrence = $this->request->data['ReservationActionPlan']['origin_event_recurrence'];
		}
		if (!empty($this->request->data['ReservationActionPlan']['origin_event_exception'])) {
			$originEventException = $this->request->data['ReservationActionPlan']['origin_event_exception'];
		}

		if (!empty($this->request->data['ReservationActionPlan']['origin_rrule_id'])) {
			$originRruleId = $this->request->data['ReservationActionPlan']['origin_rrule_id'];
		}
		if (!empty($this->request->data['ReservationActionPlan']['origin_rrule_key'])) {
			$originRruleKey = $this->request->data['ReservationActionPlan']['origin_rrule_key'];
		}

		$originCreatedUser = Hash::get($this->request->data, 'ReservationActionPlan.origin_created_user');
	}
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_event_id', array('value' => $originEventId));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_event_key', array('value' => $originEventKey));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_event_recurrence', array('value' => $originEventRecurrence));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_event_exception', array('value' => $originEventException));

	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_rrule_id', array('value' => $originRruleId));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_rrule_key', array('value' => $originRruleKey));

	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_created_user',
		array('value' => $originCreatedUser));

	//兄弟event数
	$countEventSiblings = 0;
	if (!empty($this->request->data['ReservationActionPlan']['origin_num_of_event_siblings'])) {
		$countEventSiblings = $this->request->data['ReservationActionPlan']['origin_num_of_event_siblings'];
	} else {
		if (!empty($eventSiblings)) {
			$countEventSiblings = count($eventSiblings);
		}
	}
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.origin_num_of_event_siblings',
		array('value' => $countEventSiblings));

	//全選択用に、繰返し先頭eventのeditボタのリンク生成用パラメータを保存しておく。
	//
	$firstSibYear = $firstSibMonth = $firstSibDay = $firstSibEventId = 0;
	if (!empty($this->request->data['ReservationActionPlan']['first_sib_event_id'])) {
		$firstSibEventId = $this->request->data['ReservationActionPlan']['first_sib_event_id'];
		$firstSibYear = $this->request->data['ReservationActionPlan']['first_sib_year'];
		$firstSibMonth = $this->request->data['ReservationActionPlan']['first_sib_month'];
		$firstSibDay = $this->request->data['ReservationActionPlan']['first_sib_day'];
	} else {
		if (!empty($firstSib)) {
			$firstSibEventId = $firstSib['ReservationActionPlan']['first_sib_event_id'];
			$firstSibYear = $firstSib['ReservationActionPlan']['first_sib_year'];
			$firstSibMonth = $firstSib['ReservationActionPlan']['first_sib_month'];
			$firstSibDay = $firstSib['ReservationActionPlan']['first_sib_day'];
		}
	}
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.first_sib_event_id', array('value' => $firstSibEventId));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.first_sib_year', array('value' => $firstSibYear));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.first_sib_month', array('value' => $firstSibMonth));
	echo $this->NetCommonsForm->hidden('ReservationActionPlan.first_sib_day', array('value' => $firstSibDay));

	/*
	// -- 以下のcapForViewOf1stSibによるデータすり替え方式は、--
	// -- 全変更選択時、繰返し先頭eventのeditボタンを擬似クリック方式にかえたので、削除. --

	//CakeLog::debug("DBG: capForViewOf1stSib[" . print_r($capForViewOf1stSib, true) . "]");
	//繰返しの最初のevent(第一子event）のcapForView情報(＝capForViewOf1stSib)の一部
	$sibFieldVals = array(
		'enable_time' => 0,
		'easy_start_date' => '',
		'easy_hour_minute_from' => '',
		'easy_hour_minute_to' => '',
		'detail_start_datetime' => '',
		'detail_end_datetime' => '',
		'timezone_offset' => '',
	);
	foreach ($sibFieldVals as $field => $val) {
		$fieldName = 'firstSibCap' . Inflector::camelize($field);	//変数名組立
		$$fieldName = $val;	//ここで変数を生成し、初期値代入
	}
	$sibFields = array_keys($sibFieldVals);	//filedだけの配列にする
	if (!empty($this->request->data['ReservationActionPlan']['first_sib_cap_detail_start_datetime'])){
		foreach ($sibFields as $field) {
			$fieldName = 'firstSibCap' . Inflector::camelize($field);
			$item = 'first_sib_cap_' . $field;
			if (!empty($this->request->data['ReservationActionPlan'][$item])){
				$$fieldName = $this->request->data['ReservationActionPlan'][$item];
			}
		}
	} else {
		if (!empty($capForViewOf1stSib)) {
			foreach ($sibFields as $field) {
				$fieldName = 'firstSibCap' . Inflector::camelize($field);
				$$fieldName = $capForViewOf1stSib['ReservationActionPlan'][$field];
			}
		}
	}
	foreach ($sibFields as $field) {
		$fieldName = 'firstSibCap' . Inflector::camelize($field);
		echo $this->NetCommonsForm->hidden('ReservationActionPlan.first_sib_cap_' . $field,
			array('value' => $$fieldName));
	}
	*/
?>

<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_start_date', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_hour_minute_from', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_hour_minute_to', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.is_detail', array('value' => '1' )); ?>

<?php /* ReservationActionPlan.is_repeat */ ?>

<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.repeat_freq', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_interval', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_byday', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.bymonthday', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_bymonth', array('value' => '' )); */ ?>

<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_term', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_count', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_until', array('value' => '' ); */
