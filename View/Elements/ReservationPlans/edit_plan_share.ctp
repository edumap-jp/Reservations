<?php
/**
 * 予定編集（共有者選択） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Space', 'Rooms.Model');
?>
<?php
	$this->NetCommonsForm->unlockField('GroupsUser');

	//グループ管理、ユーザ選択helper
	//第1引数：項目名を指定（回覧先ユーザや共有先ユーザ等の指定を想定、
	//デフォルト値：ユーザ選択）
	$title = __d('reservations', 'Please select a person to share the schedule.');

	//第2引数：モデル名を指定（プラグインによってモデル名が異なるので
	//指定できるように対応、デフォルト値：GroupsUser）
	$pluginModel = 'GroupsUser';

	//第3引数：ルームIDを指定（引数に任意のルームIDを指定すると、
	//そのルームに参加しているユーザのみ選択可能になる、
	//デフォルト値：Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID)）
	//最終的にはSpace::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)になる予定だが、現状はそれだと
	//グループ管理が動かないため、暫定対応としてSpace::getRoomIdRoot(Space::PUBLIC_SPACE_ID)を指定
	//https://github.com/NetCommons3/Users/issues/61
	//
	//FIXME: グループ管理改修後、全会員を表すID (Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID))にする事
	$roomId = Space::getRoomIdRoot(Space::PUBLIC_SPACE_ID);

	//第4引数：ユーザIDの配列を指定（編集画面等で、登録時に選択したユー
	//ザを選択済みとして初期表示したい時に指定、デフォルト値：空配列）
	//なお、$selectUsersは、User->getUser(user_id,lang_id)の結果を順次格納した配列イメージ
	$selectUsers = $shareUsers;

	echo $this->GroupUserList->select($title, $pluginModel, $roomId, $selectUsers);

