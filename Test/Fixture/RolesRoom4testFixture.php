<?php
/**
 * RolesRoom4testFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('RolesRoomFixture', 'Rooms.Test/Fixture');

/**
 * RolesRoom4testFixture
 *
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @package NetCommons\Rooms\Test\Fixture
 */
class RolesRoom4testFixture extends RolesRoomFixture {

/**
 * Model name
 *
 * @var string
 */
	public $name = 'RolesRoom';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'roles_rooms';

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		//パブリックスペース
		array('id' => '1', 'room_id' => '2', 'role_key' => 'room_administrator'),
		array('id' => '2', 'room_id' => '2', 'role_key' => 'chief_editor'),
		array('id' => '3', 'room_id' => '2', 'role_key' => 'editor'),
		array('id' => '4', 'room_id' => '2', 'role_key' => 'general_user'),
		array('id' => '5', 'room_id' => '2', 'role_key' => 'visitor'),
		//パブリックスペース、別ルーム(room_id=5)
		array('id' => '6', 'room_id' => '5', 'role_key' => 'room_administrator'),
		array('id' => '11', 'room_id' => '5', 'role_key' => 'chief_editor'),
		array('id' => '12', 'room_id' => '5', 'role_key' => 'editor'),
		array('id' => '13', 'room_id' => '5', 'role_key' => 'general_user'),
		array('id' => '14', 'room_id' => '5', 'role_key' => 'visitor'),
		//パブリックスペース、別ルーム(room_id=6、ブロックなし)
		array('id' => '7', 'room_id' => '6', 'role_key' => 'room_administrator'),
		//コミュニティスペース、別ルーム(room_id=7, 準備中)
		array('id' => '8', 'room_id' => '7', 'role_key' => 'room_administrator'),
		//プライベートスペース、別ルーム(room_id=8, プライベートルーム)
		array('id' => '9', 'room_id' => '8', 'role_key' => 'room_administrator'),
		//パブリックスペース、サブサブルーム(room_id=9)
		array('id' => '10', 'room_id' => '9', 'role_key' => 'room_administrator'),
		//コミュニティスペース
		array('id' => '15', 'room_id' => '4', 'role_key' => 'room_administrator'),
		array('id' => '16', 'room_id' => '4', 'role_key' => 'general_user'),
	);

}
