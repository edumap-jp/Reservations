<?php
/**
 * reservation plan edit form ( delete parts ) template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php App::uses('ReservationAppBehavior', 'Reservations.Model/Behavior'); ?>

<?php
	//ここの削除メッセージは、ユーザが扱いを三択しその結果で変わるが、そこはJavaScriptで制御する。
	//ここで代入している値は初期値。
	if ($originEventId > 0) {
		$isRecurrenceVal = ($isRecurrence) ? '1' : '0';
		if (count($eventSiblings) > 1 ||
				(isset($this->request->data['ReservationActionPlan']['origin_num_of_event_siblings']) &&
				$this->request->data['ReservationActionPlan']['origin_num_of_event_siblings'] > 1)) {
			//繰返しあり、３選択時の削除

			$options = array(
				'onclick' => false,
				'ng-click' => "showRepeatConfirmEx(" . $frameId . ", 'repeatdelete', \$event," .
					"'" . $event['ReservationEvent']['key'] . "'," .
					$firstSibEventId . "," . $originEventId . "," . $isRecurrenceVal . ");",
			);
		} else {
			//繰返しなしの時の削除

			$options = array(
				'onclick' => false,
				'ng-click' => "showRepeatConfirmEx(" . $frameId . ", 'singledelete', \$event," .
					"'" . $event['ReservationEvent']['key'] . "'," .
					$firstSibEventId . "," . $originEventId . "," . $isRecurrenceVal . ");",
			);
		}
		echo $this->Button->delete('', '', $options);
	}


