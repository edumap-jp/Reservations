<?php
/**
 * Reservation Permission Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Reservation Permission Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Reservations\View\Helper
 */
class ReservationPermissionHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
		'Rooms.Rooms'
	);

/**
 * getSpaceSelectTabStart
 *
 * @param array $space space information
 * @return string
 */
	public function getSpaceSelectTabStart($space = null) {
		$html = '';

		if ($space) {
			$title = $this->Rooms->roomName($space);
		} else {
			$title = __d('reservations', 'All the members');
		}
		//$html = '<tab heading="' . $title . '">';
		//$html = '<uib-tab-heading="' . $title . '">';
		$html = '<uib-tab heading="' . h($title) . '">';
		return $html;
	}
/**
 * getSpaceSelectTabEnd
 *
 * @param array $space space information
 * @return string
 */
	public function getSpaceSelectTabEnd($space = null) {
		//return '</tab>';
		//return '</uib-tab-heading>';
		return '</uib-tab>';
	}
/**
 * getPermissionCells
 *
 * @param int $spaceId space id
 * @param array $roomBlock room block permission information
 * @return string
 */
	public function getPermissionCells($spaceId, $roomBlock) {
		if (! $this->__canEditBlockRolePermission($roomBlock)) {
			return '<td colspan="' . count($this->_View->viewVars['defaultRoles']) . '"></td>';
		}
		$permission = 'content_creatable';
		$fieldName = $spaceId . '.' . $roomBlock['Room']['id'] . '.BlockRolePermission.content_creatable';
		$html = '';
		foreach ($roomBlock['BlockRolePermission']['content_creatable'] as $roleKey => $role) {
			if (! $role['value'] && $role['fixed']) {
				continue;
			}
			$html .= '<td class="text-center">';
			if (! $role['fixed']) {
				$html .= $this->NetCommonsForm->hidden($fieldName . '.' . $roleKey . '.id');
				$html .= $this->NetCommonsForm->hidden($fieldName . '.' . $roleKey . '.roles_room_id');
				$html .= $this->NetCommonsForm->hidden($fieldName . '.' . $roleKey . '.block_key');
				$html .= $this->NetCommonsForm->hidden($fieldName . '.' . $roleKey . '.permission');
			}

			$options = array(
				'div' => false,
				'disabled' => (bool)$role['fixed']
			);
			if (! $options['disabled']) {
				$options['ng-click'] =
					'clickRole($event, \'' . $permission . '\', \'' . Inflector::variable($roleKey) . '\')';
			}
			$html .= $this->NetCommonsForm->checkbox($fieldName . '.' . $roleKey . '.value', $options);
			$html .= '</td>';
		}
		return $html;
	}
/**
 * getUseWorkflowCells
 *
 * @param int $spaceId space id
 * @param array $roomBlock room block permission information
 * @return string
 */
	public function getUseWorkflowCells($spaceId, $roomBlock) {
		if (! $this->__canEditBlockRolePermission($roomBlock)) {
			return '<td></td>';
		}
		$roomId = $roomBlock['Room']['id'];
		$fieldNameBase = $spaceId . '.' . $roomId . '.Reservation.';
		$html = '<td class="text-center">';
		$html .= $this->NetCommonsForm->hidden($fieldNameBase . 'block_key');
		$html .= $this->NetCommonsForm->hidden($fieldNameBase . 'id');
		$options = array(
			'div' => false,
		);
		$html .= $this->NetCommonsForm->checkbox($fieldNameBase . 'use_workflow', $options);
		$html .= '</td>';
		return $html;
	}
/**
 * __canEditBlockRolePermission
 *
 * @param array $roomBlock room block permission information
 * @return string
 */
	private function __canEditBlockRolePermission($roomBlock) {
		$roomRoleKey = $roomBlock['RolesRoom']['role_key'];
		if (! $roomBlock['BlockRolePermission']['block_permission_editable'][$roomRoleKey]['value']) {
			return false;
		}
		return true;
	}
}
