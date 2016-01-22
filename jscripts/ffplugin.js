/*
 * Followers/Following Plugin for MyBB (v1.0) by EvolSoft
 * Developer: EvolSoft
 * Website: http://www.evolsoft.tk
 * Date: 22/01/2016 07:45 PM (UTC)
 * Copyright & License: (C) 2015-2016 EvolSoft
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
