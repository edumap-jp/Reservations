<?php
/**
 * 施設の選択肢 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Ryuji AMANO <ryuji@ryus.co.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

$locationList = Hash::combine($locations,
	'{n}.ReservationLocation.key',
	'{n}.ReservationLocation.location_name'
	//'{n}.ReservationLocation.category_id'
);
$commonUrl = array(
	'?' => array(
		'frame_id' => Current::read('Frame.id'),
		'style' => $vars['style'],
		'year' => $vars['year'],
		'month' => $vars['month'],
		'day' => $vars['day'],
	)
);
if (isset($this->request->named['category_id'])) {
	$commonUrl['category_id'] = $this->request->named['category_id'];
}
?>

<div class="dropdown btn-group">
	<button type="button" class="btn btn-default category-dropdown-toggle dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
		<div class="clearfix">
			<div class="pull-left nc-category-ellipsis">
				<?php echo h($locationList[$vars['location_key']]); ?>
			</div>
			<div class="pull-right">
				<span class="caret"></span>
			</div>
		</div>
	</button>
	<ul class="dropdown-menu" role="menu">
		<?php foreach ($locationList as $key => $locationName) : ?>
			<li>
				<?php
					echo $this->NetCommonsHtml->link(
						$locationName, Hash::merge($commonUrl, array('?' => ['location_key' => $key]))
					);
				?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
