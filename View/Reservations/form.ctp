<?php echo $this->NetCommonsHtml->script([
	'/blogs/js/blogs.js',
	'/blogs/js/blogs_entry_edit.js',
]); ?>
<?php
$dataJson = json_encode(
	$this->NetCommonsTime->toUserDatetimeArray($this->request->data, array('BlogEntry.publish_start'))
);
?>
<div class="blogEntries form" ng-controller="Blogs" ng-init="init(<?php echo h($dataJson) ?>)">
	<article>
		<h1><?php echo __d('Reservations', '施設予約') ?></h1>
		<div class="panel panel-default">

			<?php echo $this->NetCommonsForm->create(
				'BlogEntry',
				array(
					'inputDefaults' => array(
						'div' => 'form-group',
						'class' => 'form-control',
						'error' => false,
					),
					'div' => 'form-control',
					'novalidate' => true
				)
			);
			?>
			<?php echo $this->NetCommonsForm->hidden('key'); ?>
			<?php echo $this->NetCommonsForm->hidden('Frame.id', array(
						'value' => Current::read('Frame.id'),
					)); ?>
			<?php echo $this->NetCommonsForm->hidden('Block.id', array(
				'value' => Current::read('Block.id'),
			)); ?>

			<div class="panel-body">

				<fieldset>

					<?php
					echo $this->TitleIcon->inputWithTitleIcon(
						'title',
						'BlogEntry.title_icon',
						array(
							'label' => __d('blogs', 'Title'),
							'required' => 'required',
						)
					);
					?>
                    <?php
                    // TODO set options
                    echo $this->NetCommonsForm->input(
						'room_id',
						[
							'label' => __d('reservations', '利用するグループ'),
							'type' => 'select',
                        ]
					);
                    // TODO 予約日（カレンダ合わせ）

                    ?>
					<?php /* 期日指定ラベル＋期間・時間指定のチェックボックス */ ?>
                    <div class="form-group" data-calendar-name="checkTime">
                        <div class='form-inline col-xs-12'>
							<?php
							echo $this->NetCommonsForm->label('', __d('calendars', 'Setting the date'), array(
								'required' => true));
							?>
                            &nbsp;
							<?php
							echo $this->NetCommonsForm->checkbox('CalendarActionPlan.enable_time', array(
								'label' => __d('calendars', 'Setting the time'),
								'class' => 'calendar-specify-a-time_' . $frameId,
								'div' => false,
								'ng-model' => $useTime,
								'ng-change' => 'toggleEnableTime(' . $frameId . ')',
								'ng-false-value' => 'false',
								'ng-true-value' => 'true',
								'ng-init' => (($this->request->data['CalendarActionPlan']['enable_time']) ? ($useTime . ' = true') : ($useTime . ' = false')),
							));
							?>
                        </div>
                    </div><!-- end form-group-->

                    <?php echo $this->NetCommonsForm->wysiwyg('BlogEntry.body1', array(
						'label' => __d('blogs', 'Body1'),
						'required' => true,
						'rows' => 12
					));?>


					<?php
					echo $this->NetCommonsForm->input('publish_start',
						array(
							'type' => 'datetime',
							'required' => 'required',
							'label' => __d('blogs', 'Published datetime'),
							'childDiv' => ['class' => 'form-inline'],
						)
					);
					?>

					<?php echo $this->Category->select('BlogEntry.category_id', array('empty' => true)); ?>


				</fieldset>

				<hr/>
				<?php echo $this->Workflow->inputComment('BlogEntry.status'); ?>

			</div>

			<?php echo $this->Workflow->buttons('BlogEntry.status'); ?>

			<?php echo $this->NetCommonsForm->end() ?>

			<?php if ($isEdit && $isDeletable) : ?>
				<div  class="panel-footer" style="text-align: right;">
					<?php echo $this->NetCommonsForm->create('BlogEntry',
						array(
							'type' => 'delete',
							'url' => NetCommonsUrl::blockUrl(
								array('controller' => 'blog_entries_edit', 'action' => 'delete', 'frame_id' => Current::read('Frame.id')))
						)
					) ?>
					<?php echo $this->NetCommonsForm->input('key', array('type' => 'hidden')); ?>

					<?php echo $this->Button->delete('', __d('net_commons', 'Deleting the %s. Are you sure to proceed?', __d('blogs', 'BlogEntry')));?>

					</span>
					<?php echo $this->NetCommonsForm->end() ?>
				</div>
			<?php endif ?>

		</div>

		<?php echo $this->Workflow->comments(); ?>

	</article>

</div>


