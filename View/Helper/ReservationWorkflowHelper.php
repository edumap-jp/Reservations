<?php
/**
 * ReservationWorkflow Helper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');
App::uses('ReservationPermissiveRooms', 'Reservations.Utility');

/**
 * Reservation Workflow Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservation\View\Helper
 */
class ReservationWorkflowHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
	);

/**
 * Check deletable permission
 *
 * @param array $data Model data
 * @return bool True is deletable data
 */
	public function canDelete($data) {
		$model = ClassRegistry::init('Reservations.ReservationEvent');
		$canDel = $model->canDeleteContent($data);
		return $canDel;
	}
}
