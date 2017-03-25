<?php
/**
 * カテゴリー選択肢 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->Category->dropDownToggle(array(
	'empty' => true,
	'displayMenu' => true,
	'url' => array(
		'?' => array(
			'frame_id' => Current::read('Frame.id'),
			'style' => $vars['style'],
			'year' => $vars['year'],
			'month' => $vars['month'],
			'day' => $vars['day'],
		)
	),
));
