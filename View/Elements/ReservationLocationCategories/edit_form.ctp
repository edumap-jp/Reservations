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
?>

<?php echo $this->element('Blocks.form_hidden'); ?>

<?php echo $this->Form->hidden('Reservation.id'); ?>
<?php echo $this->Form->hidden('Reservation.block_key'); ?>
<?php //echo $this->Form->hidden('ReservationSetting.use_workflow'); ?>
<?php //echo $this->Form->hidden('ReservationSetting.use_comment_approval'); ?>
<?php //echo $this->Form->hidden('ReservationFrameSetting.id'); ?>
<?php //echo $this->Form->hidden('ReservationFrameSetting.frame_key'); ?>
<?php //echo $this->Form->hidden('ReservationFrameSetting.articles_per_page'); ?>
<?php //echo $this->Form->hidden('ReservationFrameSetting.comments_per_page'); ?>

<?php //echo $this->NetCommonsForm->input('Reservation.name', array(
//		'type' => 'text',
//		'label' => __d('blogs', 'Reservation name'),
//		'required' => true,
//	)); ?>

<?php //echo $this->element('Blocks.public_type'); ?>

<?php //echo $this->NetCommonsForm->inlineCheckbox('ReservationSetting.use_comment', array(
//			'label' => __d('content_comments', 'Use comment')
//	)); ?>

<?php //echo $this->Like->setting('ReservationSetting.use_like', 'ReservationSetting.use_unlike');?>

<?php //echo $this->NetCommonsForm->inlineCheckbox('ReservationSetting.use_sns', array(
//	'label' => __d('blogs', 'Use sns')
//)); ?>

<?php
echo $this->element('Categories.edit_form', array(
	'categories' => isset($categories) ? $categories : null
));
?>
<?php echo $this->element('Blocks.modifed_info', array('displayModified' => true));
