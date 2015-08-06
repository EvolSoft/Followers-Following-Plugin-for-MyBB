/*
 * Followers/Following Plugin for MyBB (v1.0) by EvolSoft
 * Developer: EvolSoft
 * Website: http://www.evolsoft.tk
 * Date: 05/08/2015 02:15 PM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * Licensed under GNU General Public License v3 (https://github.com/EvolSoft/Followers-Following-Plugin-for-MyBB/blob/master/LICENSE)
 */

var ffplugin = {
		execute : function(uid){
			var target = event.target || event.srcElement;
			 $.ajax({
			        url : $(target).attr('href') + "&ajax=1",
			        dataType : 'json',
			        context : target,
			        success : function(data){
						if(data['status'] == "true"){
							$(this).text("Unfollow");
							$(this).attr('href', $(this).attr('href').replace("follow", "unfollow"));
						}else if(data['status'] == "false"){
							$(this).text("Follow");
							$(this).attr('href', $(this).attr('href').replace("unfollow", "follow"));
						}
			        }
			 });
			 event.preventDefault ? event.preventDefault() : event.returnValue = false;
		},
		getQueryByName : function(name) {
		    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
		    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
		        results = regex.exec(location.search);
		    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}
}
