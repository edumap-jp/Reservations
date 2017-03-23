<?php
/**
 * 施設選択ドロップダウン
 */
$ReservationLocation = ClassRegistry::init('Reservations.ReservationLocation');
$locations = $ReservationLocation->getLocations();
$locationList = Hash::combine($locations,
	'{n}.ReservationLocation.key',
	'{n}.ReservationLocation.location_name',
	'{n}.ReservationLocation.category_id'
);

$locationsOptions = [];
$locationsOptions[__d('reservations', 'カテゴリ無し')] = $locationList[0];
foreach ($categories as $category) {
	$locationsOptions[$category['CategoriesLanguage']['name']] =
							Hash::get($locationList, $category['Category']['id'], []);
}
$locationKey = $this->request->query('location_key');
?>

<div class="form-inline"
    ng-controller="Reservations.selectLocation"
	ng-init="initialize(<?php echo h(json_encode(array('locations' => $locations, 'frameId' => $frameId, 'selectedLocation' => $locationKey))); ?>)">

	<?php
		//debug($locationKey);
		$displayStyle = $this->request->query('style');
		echo $this->NetCommonsForm->input('location_key', [
			'type' => 'select',
			//'class' => 'form-inline',
			'options' => $locationsOptions,
			'ng-model' => 'selectedLocation',
			//'selected' => $locationKey,
			'ng-change' => 'changeLocation(\'' . $displayStyle . '\')',
		]);
	?>
	<a href="" data-toggle="popover" data-placement="bottom" title="" data-trigger="focus" data-content="
							<dl>
							<dt><?php echo __d('reservations', '利用時間'); ?></dt><dd>{{selectLocation.ReservationLocation.openText}}</dd>
							<dt><?php echo __d('reservations', '施設管理者'); ?></dt><dd>{{selectLocation.ReservationLocation.contact}}</dd>
							</dl>
							<p>{{selectLocation.ReservationLocation.description}}</p>
	" data-original-title="{{selectLocation.ReservationLocation.location_name}}"><?php echo __d('reservations', '詳細'); ?></a>
<?php
	$html = '<script type="text/javascript">' .
		'$(function () { $(\'[data-toggle="popover"]\').popover({html: true}) });</script>';
	echo $html;
?>
</div>
