<?php
/**
 * ReservationSettings edit template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->element('Categories.edit_form_common');
$categories = NetCommonsAppController::camelizeKeyRecursive($this->data['Categories']);
?>

<?php echo $this->element('Blocks.form_hidden'); ?>
<?php echo $this->Form->hidden('Reservation.id'); ?>
<?php echo $this->Form->hidden('Reservation.block_key'); ?>

<div ng-controller="Categories" ng-init="initialize(<?php echo h(json_encode(['categories' => $categories])); ?>)">
	<div class="form-group clearfix">
		<div class="pull-left">
			<?php echo $this->NetCommonsForm->error('category_name'); ?>
		</div>

		<div class="pull-right">
			<?php echo $this->Button->add(__d('net_commons', 'Add'), ['ng-click' => 'add()', 'type' => 'button']); ?>
		</div>
	</div>

	<?php echo $this->element('Categories.edit_form_categories'); ?>
</div>
