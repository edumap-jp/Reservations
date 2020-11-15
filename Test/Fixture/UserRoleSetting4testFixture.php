<?php
/**
 * Room4testFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('UserRoleSettingFixture', 'UserRoles.Test/Fixture');

/**
 * Room4testFixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Rooms\Test\Fixture
 */
class UserRoleSetting4testFixture extends UserRoleSettingFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'UserRoleSetting';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'user_role_settings';

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => '10',
			'role_key' => 'general_user',
			'origin_role_key' => 'general_user',
			'use_private_room' => '1',
		],

		// プライベートルームを使えないロール
		[
			'id' => '11',
			'role_key' => 'custom_user',
			'origin_role_key' => 'general_user',
			'use_private_room' => '0',
		],
	];

}
