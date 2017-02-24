<?php
/**
 * RolesRoomsUser4testFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('RolesRoomsUserFixture', 'Rooms.Test/Fixture');

/**
 * RolesRoomsUser4testFixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Rooms\Test\Fixture
 */
class RolesRoomsUser4testFixture extends RolesRoomsUserFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'RolesRoomsUser';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'roles_rooms_users';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		//パブリックスペース
		// * ルームID=2、ユーザID=1
		array(
			'id' => '1',
			'roles_room_id' => '1',
			'user_id' => '1',
			'room_id' => '2',
		),
		// * ルームID=2、ユーザID=2
		array(
			'id' => '2',
			'roles_room_id' => '2',
			'user_id' => '2',
			'room_id' => '2',
		),
		// * ルームID=2、ユーザID=3
		array(
			'id' => '3',
			'roles_room_id' => '3',
			'user_id' => '3',
			'room_id' => '2',
		),
		// * ルームID=2、ユーザID=4
		array(
			'id' => '4',
			'roles_room_id' => '4',
			'user_id' => '4',
			'room_id' => '2',
		),
		// * ルームID=2、ユーザID=5
		array(
			'id' => '5',
			'roles_room_id' => '5',
			'user_id' => '5',
			'room_id' => '2',
		),
		//パブリックスペース、別ルーム(room_id=5)
		array(
			'id' => '6',
			'roles_room_id' => '6',
			'user_id' => '1',
			'room_id' => '5',
			'last_accessed' => '2015-06-17 00:00:00',
			'previous_accessed' => '2014-06-17 00:00:00',
		),
		//パブリックスペース、別ルーム(room_id=6、ブロックなし)
		array(
			'id' => '7',
			'roles_room_id' => '7',
			'user_id' => '1',
			'room_id' => '6',
		),
		//別ルーム(room_id=7, 準備中)
		array(
			'id' => '8',
			'roles_room_id' => '8',
			'user_id' => '1',
			'room_id' => '7',
		),
		//別ルーム(room_id=8, プライベートルーム)
		array(
			'id' => '9',
			'roles_room_id' => '9',
			'user_id' => '1',
			'room_id' => '8',
		),
		//サブサブルーム(room_id=9)
		array(
			'id' => '10',
			'roles_room_id' => '10',
			'user_id' => '1',
			'room_id' => '9',
		),
		//---add start----
		array(
			'id' => '11',
			'roles_room_id' => '15', //管理者
			'user_id' => '1',
			'room_id' => '4', //コミュニティ
		),
		array(
			'id' => '12',
			'roles_room_id' => '16', //一般
			'user_id' => '2',
			'room_id' => '4', //コミュニティ
		),
		array(
			'id' => '13',
			'roles_room_id' => '16', //一般
			'user_id' => '3',
			'room_id' => '4', //コミュニティ
		),
		array(
			'id' => '14',
			'roles_room_id' => '16', //一般
			'user_id' => '4',
			'room_id' => '4', //コミュニティ
		),
		array(
			'id' => '15',
			'roles_room_id' => '16', //一般
			'user_id' => '5',
			'room_id' => '4', //コミュニティ
		),
		//---add end-----
		//---add start----
		//array(
		//	'id' => '16',
		//	'roles_room_id' => '9', //管理者
		//	'user_id' => '2',
		//	'room_id' => '8', //プライベート
		//),
		//---add end-----
	);

}
