<?php

/*
 * Followers/Following Plugin for MyBB (v1.0) by EvolSoft
 * Developer: EvolSoft
 * Website: http://www.evolsoft.tk
 * Date: 08/08/2015 04:29 PM (UTC)
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

if(!defined("IN_MYBB")){
	die("Direct initialization of this file is not allowed.<br><br>Please make sure IN_MYBB is defined.");
}

include "ffplugin/ffplugin_myalertsformatter.php";

$plugins->add_hook('global_start', 'start');
$plugins->add_hook("member_profile_end", "process_profile");
$plugins->add_hook("usercp_end", "buddy_list");
$plugins->add_hook("index_end", "buddy_list");

function ffplugin_info(){
	return array(
	        "name" => "Followers/Following Plugin for MyBB",
	        "description" => "A MyBB plugin to implement a XenForo like followers/following system",
	        "website" => "http://www.evolsoft.tk",
	        "author" => "EvolSoft",
	        "authorsite" => "http://www.evolsoft.tk",
	        "version" => "1.0",
	        "guid" => "",
	        "compatibility" => "18*"
	    );
}

function ffplugin_install(){
	global $mybb, $db, $cache;
	//List template
	$template = "<br><table border=\"0\" cellspacing=\"0\" cellpadding=\"5\" class=\"tborder tfixed\"><tbody><tr><td colspan=\"{\$ffplugin_colspan}\" class=\"thead\"><strong>{\$ffplugin_title} </strong>{\$ffplugin_badge}</td></tr><tr>{\$ffplugin_content}</tr><tr><td class=\"trow2\" colspan=\"{\$ffplugin_colspan}\" style=\"text-align: right\"><a href=\"\" onclick=\"MyBB.popupWindow('ffactions.php?action={\$ffplugin_action}&uid={\$ffplugin_uid}', null, true); return false;\">Show all</a></td></tr></table>";
	$insert_array = array(
			"title" => "ffplugin_list",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	//Modal List template
	$template = "{\$ffplugin_content}</tr>";
	$insert_array = array(
			"title" => "ffplugin_mlist",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	//Item template
	$template = "<td style=\"text-align:center;\" class=\"trow1\" colspan=\"{\$ffplugin_avatar_colspan}\"><a href=\"{\$ffplugin_profilepage}\"><img src=\"{\$ffplugin_avatar}\" width=\"{\$ffplugin_avatar_width}\" height=\"{\$ffplugin_avatar_height}\"></img><br>{\$ffplugin_username}</a></td>";
	$insert_array = array(
			"title" => "ffplugin_item",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	//Modal Item template
	$template = "<tr><td class=\"trow1\" width=\"1%\"><a href=\"{\$ffplugin_profilepage}\"><img src=\"{\$ffplugin_avatar}\" width=\"{\$ffplugin_avatar_width}\" height=\"{\$ffplugin_avatar_height}\"></a></td><td class=\"trow1\"><a href=\"{\$ffplugin_profilepage}\">{\$ffplugin_username}</a><div><span class=\"smalltext\">{\$ffplugin_usertitle}<br></span><span class=\"smalltext\"><strong>Posts:</strong> {\$user['postnum']} <strong>Threads:</strong> {\$user['threadnum']}<br></span></div></td></tr>";
	$insert_array = array(
			"title" => "ffplugin_mitem",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	//Badge template
	$template = "<a style=\"display: inline; color: #000; text-shadow: 0px 0px 3px #777; border-radius: 5px; background: #DDD; padding: 0px 5px 1px; margin-left: 2px; cursor: pointer;\" id=\"{\$ffplugin_badge_id}\">{\$ffplugin_num}</a>";
	$insert_array = array(
			"title" => "ffplugin_badge",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	//Button template
	$template = "<a class=\"button small_button\" href=\"ffactions.php?action={\$ffplugin_action}&uid={\$ffplugin_uid}\" onclick=\"ffplugin.execute({\$ffplugin_uid})\">{\$ffplugin_status}</a>";
	$insert_array = array(
			"title" => "ffplugin_btn",
			"template" => $db->escape_string($template),
			"sid" => "-1",
			"version" => "",
			"dateline" => time()
	);
	$db->insert_query("templates", $insert_array);
	require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
	find_replace_templatesets("headerinclude", "#" . preg_quote('<script type="text/javascript" src="{$mybb->asset_url}/jscripts/ffplugin.js"></script>') . "#i", '');
	find_replace_templatesets("headerinclude", "#" . preg_quote('{$stylesheets}') . "#i", "<script type=\"text/javascript\" src=\"{\$mybb->asset_url}/jscripts/ffplugin.js\"></script>\n{\$stylesheets}");
	find_replace_templatesets("member_profile", "#" . preg_quote('{$ffplugin_following}') . "#i", '');
	find_replace_templatesets("member_profile", "#" . preg_quote('{$ffplugin_followers}') . "#i", '');
	find_replace_templatesets("member_profile", "#" . preg_quote('{$ffplugin_btn}') . "#i", '');
	find_replace_templatesets("member_profile", "#" . preg_quote('{$contact_details}') . "#i", '{$contact_details}{$ffplugin_following}{$ffplugin_followers}');
	find_replace_templatesets("member_profile", "#" . preg_quote('{$buddy_options}') . "#i", '{$ffplugin_btn}{$buddy_options}');
	//** Followers/Following Plugin Settings Group **//
	$ffplugin_group = array(
			"gid" => "NULL",
			"name" => "ffplugin",
			"title" => "Followers/Following Plugin for MyBB",
			"description" => "Followers/Following Plugin settings",
			"disporder" => "1",
			"isdefault" => "0",
	);
	$db->insert_query("settinggroups", $ffplugin_group);
	$gid = $db->insert_id();
	//Enable setting
	$ffplugin_setting = array(
			"name" => "ffplugin_enable",
			"title" => "Do you want to enable Followers/Following Plugin?",
			"description" => "",
			"optionscode" => "yesno",
			"value" => "1",
			"disporder" => 1,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Disable buddy list
	$ffplugin_setting = array(
			"name" => "ffplugin_blist",
			"title" => "Do you want to disable the default MyBB buddy list?",
			"description" => "It''s recommended to disable this feature when using this plugin",
			"optionscode" => "yesno",
			"value" => "1",
			"disporder" => 2,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Enable MyAlerts support
	$ffplugin_setting = array(
			"name" => "ffplugin_em",
			"title" => "Do you want to enable MyAlerts support?",
			"description" => "You must install MyAlerts plugin to enable MyAlerts integration",
			"optionscode" => "yesno",
			"value" => "1",
			"disporder" => 3,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Number of displayed users per row
	$ffplugin_setting = array(
			"name" => "ffplugin_nodu",
			"title" => "How many users per row do you want to see on the Following/Followers list?",
			"description" => "",
			"optionscode" => "numeric",
			"value" => "5",
			"disporder" => 4,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Number of columns
	$ffplugin_setting = array(
			"name" => "ffplugin_col",
			"title" => "How many columns do you want to see on the Following/Followers list?",
			"description" => "",
			"optionscode" => "numeric",
			"value" => "2",
			"disporder" => 5,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Number of displayed rows in modal
	$ffplugin_setting = array(
			"name" => "ffplugin_nodr",
			"title" => "How many rows do you want to see on the Following/Followers modal?",
			"description" => "",
			"optionscode" => "numeric",
			"value" => "6",
			"disporder" => 6,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Avatar Size
	$ffplugin_setting = array(
			"name" => "ffplugin_as",
			"title" => "Avatar Size",
			"description" => "It''s recommended to set square resolutions like 32x32, 48x48, 50x50, 64x64, 100x100",
			"optionscode" => "text",
			"value" => "48x48",
			"disporder" => 7,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	//Show Name
	$ffplugin_setting = array(
			"name" => "ffplugin_snam",
			"title" => "Do you want to show the name of the user in the following/followers lists?",
			"description" => "",
			"optionscode" => "yesno",
			"value" => "1",
			"disporder" => 8,
			"gid" => intval($gid),
	);
	//Show Number
	$ffplugin_setting = array(
			"name" => "ffplugin_sn",
			"title" => "Do you want to show the number of all following/followers?",
			"description" => "",
			"optionscode" => "yesno",
			"value" => "1",
			"disporder" => 9,
			"gid" => intval($gid),
	);
	$db->insert_query("settings", $ffplugin_setting);
	rebuild_settings();
}

function ffplugin_is_installed(){
	global $db;
	return $db->table_exists("ffplugin");
}


function ffplugin_activate(){
	global $db;
	if(!$db->table_exists($prefix . "ffplugin")) {
		$db->query("CREATE TABLE " . TABLE_PREFIX . "ffplugin (following VARCHAR(100), follower VARCHAR(100))");
	}
	//MyAlerts stuff
	if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')){
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
		if(!$alertTypeManager){
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}
		$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
		$alertType->setCode('ffplugin_myalerts');
		$alertType->setEnabled(true);
		$alertType->setCanBeUserDisabled(true);
		$alertTypeManager->add($alertType);
	}
}

function ffplugin_deactivate(){
	//MyAlerts stuff
	if(class_exists('MybbStuff_MyAlerts_AlertTypeManager')){
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
		if(!$alertTypeManager) {
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
		}
		$alertTypeManager->deleteByCode('ffplugin_myalerts');
	}
}

function ffplugin_uninstall(){
	global $mybb, $db, $cache;
	$db->drop_table("ffplugin");
	$db->delete_query("templates", "title = 'ffplugin'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name LIKE '%ffplugin%'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='ffplugin'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_list'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_mlist'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_item'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_mitem'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_badge'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='ffplugin_btn'");
	rebuild_settings();
}

function start(){
	global $mybb, $db, $lang, $formatterManager;
	if(class_exists('MybbStuff_MyAlerts_AlertFormatterManager')) {
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
		if(!$formatterManager){
			$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}
		$formatterManager->registerFormatter(new FFPluginMyAlertsFormatter($mybb, $lang, 'ffplugin_myalerts'));
	}
}

function process_profile(){
    global $db, $mybb, $templates, $ffplugin_following, $ffplugin_followers, $ffplugin_title, $ffplugin_btn, $buddy_options, $myprofile_buddylist, $ffplugin_content, $ffplugin_action, $ffplugin_uid, $ffplugin_status, $ffplugin_badge, $ffplugin_badge_id, $ffplugin_num, $ffplugin_colspan, $ffplugin_profilepage, $ffplugin_avatar, $ffplugin_avatar_width, $ffplugin_avatar_height, $ffplugin_username, $user;
    require_once MYBB_ROOT . "/inc/adminfunctions_templates.php";
    if(ffplugin_is_installed()){
	    if($mybb->settings['ffplugin_sn'] == 1){
	    	if(countf($mybb->input['uid'], true) > 0){
	    		eval('$ffplugin_num = "' . countf($mybb->input['uid'], true) . '";');
	    		eval('$ffplugin_badge_id = "following";');
	    		eval('$ffplugin_badge = "' . $templates->get('ffplugin_badge') . '";');
	    		eval('$ffplugin_colspan = "' . $mybb->settings['ffplugin_nodu'] . '";');
	    		eval('$ffplugin_title = "Following";');
	    		eval('$ffplugin_action = "showfollowinglist";');
	    		eval('$ffplugin_uid = "' . $mybb->input['uid'] . '";');
	    		processList($mybb->input['uid'], true);
	    		eval('$ffplugin_following = "' . $templates->get('ffplugin_list') . '";');
	    	}
	    	if(countf($mybb->input['uid'], false) > 0){
		    	eval('$ffplugin_badge_id = "followers";');
		    	eval('$ffplugin_num = "' . countf($mybb->input['uid'], false) . '";');
		    	eval('$ffplugin_badge = "' . $templates->get('ffplugin_badge') . '";');
		    	eval('$ffplugin_colspan = "' . $mybb->settings['ffplugin_nodu'] . '";');
		    	eval('$ffplugin_title = "Followers";');
		    	eval('$ffplugin_action = "showfollowerslist";');
		    	eval('$ffplugin_uid = "' . $mybb->input['uid'] . '";');
		    	processList($mybb->input['uid'], false);
		    	eval('$ffplugin_followers = "' . $templates->get('ffplugin_list') . '";');
	    	}
	    }else{
	    	eval('$ffplugin_num = "";');
	    	eval('$ffplugin_badge = "";');
	    	eval('$ffplugin_title = "Following";');
	    	eval('$ffplugin_action = "showfollowinglist";');
	    	eval('$ffplugin_uid = "' . $mybb->input['uid'] . '";');
	    	processList($mybb->input['uid'], true);
	    	eval('$ffplugin_following = "' . $templates->get('ffplugin_list') . '";');
	    	eval('$ffplugin_title = "Followers";');
	    	eval('$ffplugin_action = "showfollowerslist";');
	    	eval('$ffplugin_uid = "' . $mybb->input['uid'] . '";');
	    	processList($mybb->input['uid'], false);
	    	eval('$ffplugin_followers = "' . $templates->get('ffplugin_list') . '";');
	    }
	   	if($mybb->user['uid'] != 0 && $mybb->input['uid'] != $mybb->user['uid']){
	   		check($mybb->input['uid']);
	   		eval('$ffplugin_btn = "' .  $templates->get('ffplugin_btn') . '";');
	   	}else{
	   		eval('$ffplugin_btn = "";');
	   	}
		if($mybb->settings['ffplugin_blist'] == 1){
			eval('$buddy_options = "";');
			eval('$myprofile_buddylist = "";');
		}
    }
}


function check($uid){
	global $db, $mybb, $templates, $ffplugin_action, $ffplugin_uid, $ffplugin_status;
	if(ffplugin_is_installed()){
		if(mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $uid . "'")) == 0){
			eval('$ffplugin_action = "follow";');
			eval('$ffplugin_uid = "' . $uid . '";');
			eval('$ffplugin_status = "Follow";');
		}else{
			eval('$ffplugin_action = "unfollow";');
			eval('$ffplugin_uid = "' . $uid . '";');
			eval('$ffplugin_status = "Unfollow";');
		}
	}
}

function countf($uid, $following){
	global $db;
	if($following){
		return mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $uid . "'"));
	}else{
		return mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE follower='" . $uid . "'"));
	}
}

function getName($uid){
	global $db;
	return format_name(get_user($uid)['username'], get_user($uid)['usergroup'], get_user($uid)['displaygroup']);
}

function getUserTitle($uid){
	
}

function processList($uid, $following){
	global $db, $mybb, $templates, $ffplugin_content, $ffplugin_profilepage, $ffplugin_avatar, $ffplugin_avatar_colspan, $ffplugin_avatar_width, $ffplugin_avatar_height, $ffplugin_username, $user;
	$list = array();
	$content = "";
	if($following){
		$list = mysqli_fetch_all($db->query("SELECT follower FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $uid . "'"));
	}else{
		$list = mysqli_fetch_all($db->query("SELECT following FROM " . TABLE_PREFIX . "ffplugin WHERE follower='" . $uid . "'"));
	}
	$status = true;
	$n = 0;
	for($i = 0; $i < $mybb->settings['ffplugin_col']; $i++){
		for($l = 0; $l < $mybb->settings['ffplugin_nodu']; $l++){
			$n = $i * $mybb->settings['ffplugin_nodu'] + $l;
			$status = isset($list[$n]);
			if($status){
				$user = get_user($list[$i][0]);
				eval('$ffplugin_username = "' . addslashes(getName($list[$n][0])) . '";');
				eval('$ffplugin_profilepage = "member.php?action=profile&uid=' . $list[$n][0] .'";');
				eval('$ffplugin_avatar_colspan = "1";');
				eval('$ffplugin_avatar_width = "' . explode('x', $mybb->settings['ffplugin_as'])[0] . '";');
				eval('$ffplugin_avatar_height = "' . explode('x', $mybb->settings['ffplugin_as'])[1] . '";');
				if(get_user($list[$n][0])['avatar'] == null){
					eval('$ffplugin_avatar = "images/default_avatar.png";');
				}else{
					eval('$ffplugin_avatar = "' . get_user($list[$n][0])['avatar'] . '";');
				}
				eval('$content .= "' . $templates->get('ffplugin_item') . '";');
			}else{
				break;
			}
		}
		if(!$status){
			if($mybb->settings['ffplugin_nodu'] - ($n - ($i * $mybb->settings['ffplugin_nodu'])) != $mybb->settings['ffplugin_nodu']){
				eval("\$content .= \"<td class=\\\"trow1\\\" colspan=\\\"" . ($mybb->settings['ffplugin_nodu'] - ($n - ($i * $mybb->settings['ffplugin_nodu']))) . "\\\"></td>\";");
			}
			break;
		}
		eval('$content .= "</tr><tr>";');
	}
	eval('$ffplugin_content = "$content";');
}

function processModalList($uid, $following, $page = 0){
	global $db, $mybb, $templates, $ffplugin_content, $ffplugin_profilepage, $ffplugin_avatar, $ffplugin_avatar_colspan, $ffplugin_avatar_width, $ffplugin_avatar_height, $ffplugin_username, $user, $ffplugin_usertitle;
	require_once MYBB_ROOT . "/inc/functions_user.php";
	$list = array();
	$content = "";
	if($following){
		$list = mysqli_fetch_all($db->query("SELECT follower FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $uid . "'"));
	}else{
		$list = mysqli_fetch_all($db->query("SELECT following FROM " . TABLE_PREFIX . "ffplugin WHERE follower='" . $uid . "'"));
	}
	for($i = ($page * $mybb->settings['ffplugin_nodr']); $i < ($page * $mybb->settings['ffplugin_nodr'] + $mybb->settings['ffplugin_nodr']); $i++){
		if(isset($list[$i])){
			$user = get_user($list[$i][0]);
			$usertitle = $usertitle;
			eval('$ffplugin_username = "' . addslashes(getName($list[$i][0])) . '";');
			eval('$ffplugin_usertitle = "' . get_usertitle($list[$i][0]) . '";');
			eval('$ffplugin_profilepage = "member.php?action=profile&uid=' . $list[$i][0] .'";');
			eval('$ffplugin_avatar_colspan = "1";');
			eval('$ffplugin_avatar_width = "' . explode('x', $mybb->settings['ffplugin_as'])[0] . '";');
			eval('$ffplugin_avatar_height = "' . explode('x', $mybb->settings['ffplugin_as'])[1] . '";');
			if(get_user($list[$i][0])['avatar'] == null){
				eval('$ffplugin_avatar = "images/default_avatar.png";');
			}else{
				eval('$ffplugin_avatar = "' . get_user($list[$i][0])['avatar'] . '";');
			}
			eval('$content .= "' . $templates->get('ffplugin_mitem') . '";');
		}else{
			eval('$content .= "</tr><tr>";');
			break;
		}
	}
	if(isset($list[$page * $mybb->settings['ffplugin_nodr'] + $mybb->settings['ffplugin_nodr']])){
		if($following){
			eval("\$content .= \"<tr><td class=\\\"trow2\\\" colspan=\\\"2\\\" style=\\\"text-align: right\\\"><a class=\\\"button small_button\\\" rel=\\\"modal:close\\\" href=\\\"\\\" onclick=\\\"MyBB.popupWindow('ffactions.php?action=showfollowinglist&uid=" . $uid . "&page=" . ($page + 1) . "', null, true); return false;\\\">More...</a></td></tr>\";");
		}else{
			eval("\$content .= \"<tr><td class=\\\"trow2\\\" colspan=\\\"2\\\" style=\\\"text-align: right\\\"><a class=\\\"button small_button\\\" rel=\\\"modal:close\\\" href=\\\"\\\" onclick=\\\"MyBB.popupWindow('ffactions.php?action=showfollowerslist&uid=" . $uid . "&page=" . ($page + 1) . "', null, true); return false;\\\">More...</a></td></tr>\";");
		}
	}
	return $content;
}

function drawModal($title, $width = 400, $content){
	echo '<div class="modal" style="width: ' . $width . 'px"><div style="overflow-y: auto;"><table cellspacing="0" cellpadding="5" class="tborder"><tbody><tr><td class="thead" colspan="2"><div><strong>' . $title . '</strong></div></td></tr>' . $content . '</tbody></table></div></div>';
}

function buddy_list(){
	global $mybb, $buddy_options, $buddyrequestspmcheck, $buddyrequestsautocheck;
	if(ffplugin_is_installed()){
		if($mybb->settings['ffplugin_blist'] == 1){
			eval('$buddy_options = "";');
			eval('$buddyrequestspmcheck = "";');
			eval('$buddyrequestsautocheck = "";');
		}
	}
}
?>
