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
	
	function displaystriketable(xml){
		$('#striketable').html("<tr><th>Team/University</th></tr>");
		$('strike',xml).each(function() {
			univ_name=$(this).find("univ_name").text();
			univ_code=$(this).find("team_code").text();
			strike_id=$(this).find("strike_id").text();
			$('#striketable').append("<tr><td>"+univ_name+" "+univ_code+"</td><td id="+strike_id+" class='remove'>Delete</td</tr>");
		});
	};
	
	function updatecounts(){
		$('#activecount').text($("#totalcount").text() - $(".inactive").length);
	}
	
	function adjudactivetoggle(xml){
		$('adjudicator',xml).each(function() {
			adjudicator=$("#adjud"+$(this).find("adjud_id").text());
			adjudicator.text($(this).find("active").text());
			adjudicator.parent().removeClass("inactive");
			if(adjudicator.text()=="N"){
				adjudicator.parent().addClass("inactive");
			}
		});
		updatecounts();
	}
	
	function failuremsg(text){
		$('failure').html(text);
	}
	
	$("#addstrike").click(function(){
	 	$.ajax({
			url: "controller/input/strike.php",
			data: {
				adjud_id: $("#adjud_id").val(),
				univ_id: $("#add_univ_id").val(),
				team_code: $("#add_team_code").val(),
				action: "ADD",
			}, 
			type: 'POST',
			success: displaystriketable,
			failure: failuremsg,
		});
	});
	
	$.post("controller/input/strike.php",{adjud_id: $("#adjud_id").val()}, displaystriketable);
	$('.remove').live("click", (function(){
	 	$.post("controller/input/strike.php",
			{
				adjud_id: $("#adjud_id").val(),
				strike_id: $(this).attr("id"),
				action: "DELETE",
			},
			displaystriketable
		);
	}));
	
	$('.activetoggle').click(function(){
		$.ajax({
			url: "controller/input/adjud.php",
			data: {
				adjud_id: $(this).attr("id").substring(5),
				action: "ACTIVETOGGLE",
			},
			type: 'POST',
			success: adjudactivetoggle,
			failure: failuremsg,
		});
	});
});


