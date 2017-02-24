<?php
?>

<?php
/**
 * 月（大）の予定表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Calendars.scripts');
?>
<?php if (Current::permission('content_creatable')) : ?>
    <div class="pull-right">
		<?php
		$addUrl = array(
			//'controller' => 'blog_entries_edit',
			'action' => 'add',
			'frame_id' => Current::read('Frame.id')
		);
		echo $this->Button->addLink('',
			$addUrl,
			array('tooltip' => __d('reservations', '予約')));
		?>
    </div>
<?php endif ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">
	<?php
	echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'lmonthly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>
	<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperationsWrap('month', 'top', $vars);	?>
    <div class="row"><!--全体枠-->
        <div class="col-xs-12 col-sm-12">
            <table class='calendar-monthly-table'>
                <tbody>
                <tr class="hidden-xs">
                    <td class='calendar-col-week-head calendar-monthly-line-0'>&nbsp;</td>
                    <td class='calendar-col-day-head calendar-monthly-line-1'><span class='calendar-sunday h4'><?php echo __d('calendars', 'Sun'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-2'><span class='h4'><?php echo __d('calendars', 'Mon'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-3'><span class='h4'><?php echo __d('calendars', 'Tue'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-4'><span class='h4'><?php echo __d('calendars', 'Wed'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-5'><span class='h4'><?php echo __d('calendars', 'Thu'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-6'><span class='h4'><?php echo __d('calendars', 'Fri'); ?></span></td>
                    <td class='calendar-col-day-head calendar-monthly-line-7'><span class='calendar-saturday h4'><?php echo __d('calendars', 'Sat'); ?></span></td>
                </tr>
				<?php echo $this->CalendarMonthly->makeLargeMonthyBodyHtml($vars); ?>
				<?php $calendarLinePlans = $this->CalendarMonthly->getLineData() ?>
                </tbody>
            </table>
            <div
                    ng-controller="CalendarsMonthlyLinePlan"
                    ng-style="initialize(<?php echo h(json_encode(array('calendarLinePlans' => $calendarLinePlans))) ?>)"
                    resize>
            </div>
        </div>
    </div><!--全体枠END-->
	<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperationsWrap('month', 'bottom', $vars);	?>
    <!-- 予定の内容 -->
	<?php
	//echo $this->CalendarLegend->getCalendarLegend($vars);
	?>
    <div class="row text-center calendar-backto-btn">
		<?php echo $this->CalendarUrl->getBackFirstButton($vars); ?>
    </div>
</article>
