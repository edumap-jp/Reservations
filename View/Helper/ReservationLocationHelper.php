<?php
/**
 * ReservationLocationHelper.php
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * Reservation location Helper
 *
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @package NetCommons\Reservations\View\Helper
 * @SuppressWarnings(PHPMD)
 */
class ReservationLocationHelper extends AppHelper {

/**
 * 使用するヘルパー
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'Rooms.Rooms',
	);

/**
 * 予約できる権限チェックボックス表示
 *
 * @param string $model モデル名
 * @param array $attributes Formヘルパーのオプション
 * @return string HTML
 */
	public function checkboxReservablePermission($model, $attributes = array()) {
		$html = '';

		if (! isset($this->_View->request->data[$model])) {
			return $html;
		}

		$html .= '<div class="form-inline">';
		foreach ($this->_View->request->data[$model] as $roleKey => $role) {
			if (! $role['default'] && $role['fixed']) {
				continue;
			}
			$html .= $this->__inputPermission($model, $roleKey, $attributes);
		}
		$html .= '</div>';

		return $html;
	}

/**
 * 予約できる権限のチェックボックス表示
 *
 * @param string $model モデル名
 * @param string $roleKey ロールキー
 * @param array $attributes Formヘルパーのオプション
 * @return string HTML
 */
	private function __inputPermission($model, $roleKey, $attributes = array()) {
		$html = '';
		$html .= '<div class="checkbox checkbox-inline">';

		$fieldName = $model . '.' . $roleKey;

		if (! Hash::get($this->_View->request->data, $fieldName . '.fixed')) {
			$html .= $this->NetCommonsForm->hidden($fieldName . '.id');
		}

		$options = Hash::merge(array(
			'div' => false,
			'disabled' => (bool)Hash::get($this->_View->request->data, $fieldName . '.fixed'),
		), $attributes);
		if (! $options['disabled']) {
			$options['ng-click'] = 'clickRole($event, \'' . Inflector::variable($roleKey) . '\')';
		}

		$options['label'] = $this->Rooms->roomRoleName(
			$roleKey, ['help' => true, 'roles' => $this->_View->viewVars['roles']]
		);
		$options['escape'] = false;

		$html .= $this->NetCommonsForm->checkbox($fieldName . '.value', $options);

		$html .= '</div>';
		return $html;
	}

}
