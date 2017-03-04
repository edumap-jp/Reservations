<article class="block-setting-body">

	<?php //echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_FRAME_SETTING); ?>
	<?php echo $this->BlockTabs->main('location_settings'); ?>

    <div class="tab-content">
		<?php /* 施設予約にはBLOCK_TAB_SETTINGは無し */ ?>

		<?php //echo $this->element('Blocks.edit_form', array(
		//	'model' => 'ReservationFrameSetting',
		//	'callback' => 'Reservations.ReservationFrameSettings/edit_form',
		//	'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
		//)); ?>

        <div class="text-right nc-table-add">
            <?php echo $this->LinkButton->add(__d('reservations', '追加'), ['action' => 'add', 'frame_id' => Current::read('Frame.id')]); ?>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('category_id'); ?></th>
                    <th><?php echo $this->Paginator->sort('location_name'); ?></th>
                    <th><?php echo $this->Paginator->sort('add_authority'); ?></th>
                    <th><?php echo $this->Paginator->sort('time_table'); ?></th>
                    <th><?php echo $this->Paginator->sort('start_time'); ?></th>
                    <th><?php echo $this->Paginator->sort('end_time'); ?></th>
                    <th><?php echo $this->Paginator->sort('use_private'); ?></th>
                    <th><?php echo $this->Paginator->sort('use_auth_flag'); ?></th>
                    <th><?php echo $this->Paginator->sort('use_all_rooms'); ?></th>
                    <th><?php echo $this->Paginator->sort('display_sequence'); ?></th>
                    <th><?php echo $this->Paginator->sort('created_user'); ?></th>
                    <th><?php echo $this->Paginator->sort('created'); ?></th>
                    <th><?php echo $this->Paginator->sort('modified_user'); ?></th>
                    <th><?php echo $this->Paginator->sort('modified'); ?></th>

                </tr>
                </thead>
                <?php foreach ($reservationLocations as $reservationLocation): ?>
                    <tr>
                        <td>
							<?php echo $this->Html->link($reservationLocation['Category']['id'], array('controller' => 'categories', 'action' => 'view', $reservationLocation['Category']['id'])); ?>
                        </td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['location_name']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['add_authority']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['time_table']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['start_time']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['end_time']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['use_private']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['use_auth_flag']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['use_all_rooms']); ?>&nbsp;</td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['display_sequence']); ?>&nbsp;</td>
                        <td>
							<?php echo $this->Html->link($reservationLocation['TrackableCreator']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableCreator']['id'])); ?>
                        </td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['created']); ?>&nbsp;</td>
                        <td>
							<?php echo $this->Html->link($reservationLocation['TrackableUpdater']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableUpdater']['id'])); ?>
                        </td>
                        <td><?php echo h($reservationLocation['ReservationLocation']['modified']); ?>&nbsp;</td>
                    </tr>
                <?php endforeach;?>
            </table>

        </div>

    </div><!--end tab-content-->
</article>


TODO　FAQのような並び替えボタン


<div class="table-responsive"><table class="table table-hover">				<thead>
        <tr>
            <th></th>						<th class="nc-table-text"><a href="/faqs/faq_blocks/index/sort:BlocksLanguage.name/direction:asc?frame_id=13">FAQ名</a></th><th></th>						<th class="nc-table-numeric"><a href="/faqs/faq_blocks/index/sort:Block.content_count/direction:asc?frame_id=13">件数</a></th>						<th class="nc-table-center"><a href="/faqs/faq_blocks/index/sort:Block.public_type/direction:asc?frame_id=13">状態</a></th>						<th class="nc-table-handle"><a href="/faqs/faq_blocks/index/sort:TrackableUpdater.handlename/direction:asc?frame_id=13">更新者</a></th>						<th class="nc-table-datetime"><a href="/faqs/faq_blocks/index/sort:Block.modified/direction:asc?frame_id=13">更新日</a></th>					</tr>
        </thead>
        <tbody>
        <tr class="active">							<td><div class="block-index"><div class="radio"><label class="control-label"><input type="radio" name="data[Frame][block_id]" id="FrameBlockId9" value="9" checked="checked" onclick="submit()" ng-click="sending=true" ng-disabled="sending"></label></div></div></td>							<td class="nc-table-text">Block - FAQ</td><td><a href="/faqs/faq_blocks/edit/9?frame_id=13" class="btn btn-primary nc-btn-style btn-xs"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> <span class="hidden-xs">編集</span></a></td>							<td class="nc-table-numeric">3</td>							<td class="nc-table-center">公開</td>							<td class="nc-table-handle"><a href="#" ng-controller="Users.controller" ng-click="showUser($event, '1')" class="ng-scope"><img src="/users/users/download/1/avatar/thumb?" class="user-avatar-xs" alt=""> システム管理者</a></td>							<td class="nc-table-datetime">18:27</td>						</tr>									</tbody>
    </table></div>

<div class="reservationLocations index">
	<h2><?php echo __('Reservation Locations'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($reservationLocations as $reservationLocation): ?>
	<tr>
		<td><?php echo h($reservationLocation['ReservationLocation']['id']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['key']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($reservationLocation['Language']['id'], array('controller' => 'languages', 'action' => 'view', $reservationLocation['Language']['id'])); ?>
		</td>
		<td>
			<?php echo $this->Html->link($reservationLocation['Category']['id'], array('controller' => 'categories', 'action' => 'view', $reservationLocation['Category']['id'])); ?>
		</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['location_name']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['add_authority']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['time_table']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['start_time']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['end_time']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['use_private']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['use_auth_flag']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['use_all_rooms']); ?>&nbsp;</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['display_sequence']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($reservationLocation['TrackableCreator']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableCreator']['id'])); ?>
		</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['created']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($reservationLocation['TrackableUpdater']['id'], array('controller' => 'users', 'action' => 'view', $reservationLocation['TrackableUpdater']['id'])); ?>
		</td>
		<td><?php echo h($reservationLocation['ReservationLocation']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $reservationLocation['ReservationLocation']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $reservationLocation['ReservationLocation']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $reservationLocation['ReservationLocation']['id']), null, __('Are you sure you want to delete # %s?', $reservationLocation['ReservationLocation']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Reservation Location'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Languages'), array('controller' => 'languages', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Language'), array('controller' => 'languages', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Categories'), array('controller' => 'categories', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Category'), array('controller' => 'categories', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Trackable Creator'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
