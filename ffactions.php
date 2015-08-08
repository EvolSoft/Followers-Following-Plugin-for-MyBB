<?php

/*
 * Followers/Following Plugin for MyBB (v1.0) by EvolSoft
 * Developer: EvolSoft
 * Website: http://www.evolsoft.tk
 * Date: 08/08/2015 04:12 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'ffsystem.php');

require_once "./global.php";


if($mybb->user['uid'] == 0){
	error_no_permission();
}

if(isset($mybb->input['action']) && isset($mybb->input['uid'])){
	if(strtolower($mybb->input['action']) == "follow"){
		if(get_user($mybb->input['uid']) == null){
			if(isset($mybb->input['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => 0));
			}else{
				error("Can't add this user to your followers list: User not found.");
			}
		}else{
			if(mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $mybb->input['uid'] . "'")) == 0){
				$db->query("INSERT INTO " . TABLE_PREFIX . "ffplugin (following, follower) VALUES ('" . $mybb->user['uid'] . "', '" . $mybb->input['uid'] . "')");
				if($mybb->settings['ffplugin_em'] == 1){
					if(function_exists("myalerts_info")){
						$myalertsinfo = myalerts_info();
						if($myalertsinfo['version'] >= "2.0.0"){
							$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ffplugin_myalerts');
							if ($alertType != null && $alertType->getEnabled()) {
								$alert = new MybbStuff_MyAlerts_Entity_Alert($mybb->input['uid'], $alertType, 0);
								MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
							}
						}
					}
				}
				if(isset($mybb->input['ajax'])){
					header("Content-type: application/json;");
					echo json_encode(array("status" => "true", "followers" => countf($mybb->input['uid'], false)));
				}else{
					redirect("member.php?action=profile&uid=" . $mybb->input['uid'], "You are now following " . getName($mybb->input['uid']) . ".");
				}
			}else{
				if(isset($mybb->input['ajax'])){
					header("Content-type: application/json;");
					echo json_encode(array("status" => "true", "followers" => countf($mybb->input['uid'], false)));
				}else{
					error("You are already following this user.");
				}
			}
		}
	}elseif(strtolower($mybb->input['action']) == "unfollow"){
		if(mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $mybb->input['uid'] . "'")) == 0){
			if(isset($mybb->input['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => countf($mybb->input['uid'], false)));
			}else{
				error("You are not following this user.");
			}
		}else{
			$db->query("DELETE FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $mybb->input['uid'] . "'");
			if(isset($mybb->input['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => countf($mybb->input['uid'], false)));
			}else{
				redirect("member.php?action=profile&uid=" . $mybb->input['uid'], "You are not following " . getName($mybb->input['uid']) . " anymore.");
			}
		}
	}elseif(strtolower($mybb->input['action']) == "showfollowinglist"){
		if(isset($mybb->input['page'])){
			$page = $mybb->input['page'];
		}else{
			$page = 0;
		}
		if(get_user($mybb->input['uid']) == null){
			drawModal("Following (Page " . ($page + 1) . ")", 500, "<tr><td class=\"trow1\" colspan=\"2\"><em>Can't get the following list of this user: User not found.</em></td></tr>");
		}else{
			drawModal("Following (Page " . ($page + 1) . ")", 500, processModalList($mybb->input['uid'], true, $page));
		}
	}elseif(strtolower($mybb->input['action']) == "showfollowerslist"){
		if(isset($mybb->input['page'])){
			$page = $mybb->input['page'];
		}else{
			$page = 0;
		}
		if(get_user($mybb->input['uid']) == null){
			drawModal("Followers (Page " . ($page + 1) . ")", 500, "<tr><td class=\"trow1\" colspan=\"2\"><em>Can't get the followers list of this user: User not found.</em></td></tr>");
		}else{
			drawModal("Followers (Page " . ($page + 1) . ")", 500, processModalList($mybb->input['uid'], false, $page));
		}
	}else{
		error("You are trying to perform an invalid action.");
	}
}else{
	error("You are trying to perform an invalid action.");
}
?>
