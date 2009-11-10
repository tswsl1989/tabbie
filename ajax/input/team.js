/* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

$(document).ready(function() {
	
	function teamactivetoggle(xml){
		$('team',xml).each(function() {
			team=$("#team"+$(this).find("team_id").text());
			team.text($(this).find("active").text());
			team.parent().removeClass("inactive");
			if(team.text()=="N"){
				team.parent().addClass("inactive");
			}
		});
		updatecounts();
	}
	
	function updatecounts(){
		$('#activecount').text($("#totalcount").text() - $(".inactive").length);
	}
	
	function failuremsg(text){
		$('failure').html(text);
	}
	
	$('.activetoggle').click(function(){
		$.ajax({
			url: "controller/input/team.php",
			data: {
				team_id: $(this).attr("id").substring(4),
				action: "ACTIVETOGGLE",
			},
			type: 'POST',
			success: teamactivetoggle,
			failure: failuremsg,
		});
	});
});


