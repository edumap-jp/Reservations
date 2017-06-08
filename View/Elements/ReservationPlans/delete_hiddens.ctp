<?php
/**
 * reservation plan edit form ( delete parts hidden field ) template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_start_date', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_hour_minute_from', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.easy_hour_minute_to', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.detail_start_datetime', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.detail_end_datetime', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.timezone', array('value' => Current::read('User.timezone') )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.is_detail', array('value' => '0' )); ?>

<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.location', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.contact', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.description', array('value' => '' )); ?>

<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.is_repeat', array('value' => '0' )); ?>

<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.repeat_freq', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_interval', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_byday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.bymonthday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_bymonth', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_term', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_count', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('ReservationActionPlan.rrule_until', array('value' => '' ));
