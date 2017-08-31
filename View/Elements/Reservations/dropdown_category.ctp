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
/* @var $this View */

// '' => 'カテゴリ選択'
// '0' => カテゴリ無し
// 　となるように事前準備
$categories = $this->get('categories');
array_unshift($categories,
	[
		'Category' => [
			'id' => null,
		],
		'CategoriesLanguage' => [
			'name' => __d('categories', 'Select Category')
		]
	],
	[
		'Category' => [
			'id' => '0',
		],
		'CategoriesLanguage' => [
			'name' => __d('reservations', 'no category')
		]
	]
	);
$this->set('categories', $categories);
// CategoryHelper用にムリヤリcategory_id=""をセット
if (!isset($this->request->params['named']['category_id'])) {
	$isOverwrite = true;
	$this->request->params['named']['category_id'] = '';
}

echo $this->Category->dropDownToggle(array(
	'empty' => false,
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
// ムリヤリnamedを書き換えたので元にもどす
if (isset($isOverwrite)) {
	unset($this->request->params['named']['category_id']);
}