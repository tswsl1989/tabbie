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

function get_display_lock(msg){
	if(display_lock == 0){
		display_lock = 1;
		return 1;
		//console.log("Acquired display lock: " + msg);
	} else {
		return 0;
		//console.log("Failed to acquire display lock: " + msg);
	}
}

function release_display_lock(msg){
	if(display_lock){
		display_lock=0;
		//console.log("Display lock released by " +msg);
		return 1;
	} else {
		//console.log("Attempt to release display lock: lock not engaged. From " +msg);
	}
}

function strike_check(adjud_id, debate_id, lockrelease) {
	var returnval = 0;
    $.post("controller/draw/draw.php", 
		{
			action: 'CHECKSTRIKE',
			adjud_id: adjud_id,
			debate_id: debate_id
		},
		function(xml) {
			//console.log("Strike response was: " + xml);
			should_strike=0;
			$('strike', xml).each(function() {
				if ($(this).find("adjud_id").text() == adjud_id) {
					should_strike=1;
				}
			});
			if(should_strike){
				//console.log("Striking adjudicator " + adjud_id);
				$('#A'+adjud_id).addClass('strike');
			} else {
				//console.log("Destriking adjudicator " + adjud_id);
				$('#A'+adjud_id).removeClass('strike');
			}
			returnval = 1;
		}
   );
	return returnval;
}


function adjudicatorshuffle(xml) {
    $('adjudicator', xml).each(function() {
        var id = $(this).find("id").text();
        var ranking = $(this).find("ranking").text();
        var status = $(this).find("status").text();
        var trainee = $(this).find("trainee").text();
        var name = $(this).find("name").text();
        var debate_id = $(this).find("debate_id").text();
        var adjud = $('#A' + id);
		//console.log("Received Adjudicator "  + id + " (" + name +") with status " + status);
        if (adjud.length > 0) {
            //Found relevant adjudicator.
            if (adjud.parent().parent().parent().attr('id') == ('D' + debate_id)) {
                //Adjudicator is in the right debate
                if (adjud.parent().children(':first').attr('id') == ('A' + id)) {
                    //Adjudicator is positioned as chair
                    if (status == 'chair') {
                        //Adjudicator should be positioned as chair: nothing to do
                        //console.log("Adjudicator " + id + " exists on location as chair.");
                        return;
                    } else {
                        //Adjudicator should be removed from the chair
                        adjud.parent().append(adjud);
                        adjud.removeClass('chair');
                        //console.log("Adjudicator " + id + " was on location but erroneously chair. Demoted.");
                        return;
                    }
                } else {
                    //Adjudicator is not positioned as chair
                    if (status == 'chair') {
                        //Adjudicator should be positioned as chair but isn't: promote
						adjud.parent().children().removeClass('chair'); //Remove the chair class from the others(!)
                        adjud.parent().prepend(adjud);
                        adjud.addClass('chair');
                        //console.log("Adjudicator " + id + " was on location but erroneously panelist. Promoted.");
                        return;
                    } else {
                        //Adjudicator isn't positioned as chair and shouldn't be
                        //console.log("Adjudicator " + id + " exists on location as panelist.");
                        return;
                    }
                }
            } else {
                //Adjudicator does exist, but is in the wrong debate.
                if (status == 'chair') {
                    $('#D' + debate_id + ' td ul').prepend(adjud);
                    adjud.addClass('chair');
					strike_check(id, debate_id);
					//console.log("Adjudicator " + id + " moved to debate " + debate_id + " as chair.");
                    return;
                } else {
                    $('#D' + debate_id + ' td ul').append(adjud);
                    adjud.removeClass('chair');
					strike_check(id, debate_id);
					//console.log("Adjudicator " + id + " moved to debate " + debate_id );
                    return;
                }
            }
        }
        //Should we have got to this point, the adjudicator does not exist.
        //The adjudicator doesn't exist(!), they need to be created
        var adjudicator = ($("<li/>").attr('id', 'A' + id).addClass("adjudicator").html(name + " <span class='ranking'>" + ranking + "</span>"));
        //now find where it's meant to go and either prepend it (for the chair) or append it (for anything else)
		if (status == 'chair') {
            adjudicator.addClass("chair");
            $('#D' + debate_id + ' td ul').prepend(adjudicator);
            //console.log("New adjudicator created with id " + id + " in debate " + debate_id + " as chair.");
        } else {
            $('#D' + debate_id + ' td ul').append(adjudicator);
            //console.log("New adjudicator created with id " + id + " in debate " + debate_id);
        }
        if (trainee == 'trainee') {
            adjudicator.addClass("trainee");
        }
		strike_check(id, debate_id);
    });
    d = new Date();
    time = d.getTime() / 1000;
    //console.log("Variable time is now: " + time);
	release_display_lock("adjudicatorshuffle");
}

function adjudicator_reset() {
	//does not respect display lock
    $.post("controller/draw/adjud.php", {
        action: 'LIST',
        time: 0
    },
    adjudicatorshuffle);
}

function adjudicator_update() {
	if(get_display_lock("adjudicator_update")){
	    $.post("controller/draw/adjud.php", {
	        action: 'LIST',
	        time: time - 10
	    },
	    adjudicatorshuffle);
	}
}

function free_adjudicator_update() {
	//console.log("FREE ADJUDICATOR UPDATE");
	//if(get_display_lock("free_adjudicator_update")){
	    $.post("controller/draw/adjud.php", {
	        action: 'LIST',
	        free: 1,
	    },
	    adjudicatorshuffle);
	//}
}

function add_judge_to_panel(adjud_id, debate_id) {
    //console.log("add_judge_to_panel(" + adjud_id + "," + debate_id + ")");
    $.ajax({
        type: "POST",
        url: "controller/draw/adjud.php",
        data: "action=ADD&adjud_id=" + adjud_id + "&debate_id=" + debate_id,
        error: function() {
            //console.log("Failed to perform judge add. Resyncing.");
            $("judgelist").sortable('disable');
            adjudicator_reset();
            $("judgelist").sortable('enable');
        }
    });
}

function add_judge_as_chair(adjud_id, debate_id) {
    //console.log("add_judge_as_chair(" + adjud_id + "," + debate_id + ")");
    $.ajax({
        type: "POST",
        url: "controller/draw/adjud.php",
        data: "action=CHAIR&adjud_id=" + adjud_id + "&debate_id=" + debate_id,
        success: function() {
            },
        error: function() {
            //console.log("Failed to perform judge add. Resyncing.");
            $("judgelist").sortable('disable');
            adjudicator_reset();
            $("judgelist").sortable('enable');
        }
    });
}

function colour_draw(){
	$('.firstcat').toggleClass("firstcatactual");
	$('.secondcat').toggleClass("secondcatactual");
	$('.thirdcat').toggleClass("thirdcatactual");
}


$(document).ready(function() {
	display_lock=0;
    time = 11;
    $(".judgelist").sortable({
        placeholder: ".placeholder",
        connectWith: $('.judgelist'),
        beforeStop: function(event, ui) {
			get_display_lock('sortable beforeStop');
            var adjud_id = ui.item.attr('id').substring(1);
            var debate = ui.item.parent().parent().parent();
            var debate_id = debate.attr('id').substring(1);
            if (ui.item.hasClass("chair")) {
                $.ajax({
                    type: "POST",
                    url: "controller/draw/adjud.php",
                    data: "action=LIST&adjud_id=" + adjud_id,
                    async: false,
                    //otherwise other parts of the script execute
                    success: function(xml) {
                        $('adjudicator', xml).each(function() {
                            var old_debate_id = $(this).find("debate_id").text();
                            var old_debate_new_chair_id = $('#D' + old_debate_id + ' td ul :nth-child(1)').attr('id').substring(1);
							add_judge_as_chair(old_debate_new_chair_id, old_debate_id);
							$('#A'+old_debate_new_chair_id).addClass('chair');
                        });
                    },
                    error: function() {
                        //console.log("Failed to determine judge origin.");
                        adjudicator_reset();
                    }
                });
                ui.item.removeClass("chair");
            }
            if (ui.item.parent().children(':first').attr('id').substring(1) == adjud_id) {
                //Moved judge is chair of new panel
                ui.item.parent().children().removeClass("chair");
                ui.item.addClass('chair');
                add_judge_to_panel(adjud_id, debate_id);
                add_judge_as_chair(adjud_id, debate_id);
            } else {
                add_judge_to_panel(adjud_id, debate_id);
            }
			//In all cases
			strike_check(adjud_id, debate_id);  //wait for strike_check to return before proceeding
			release_display_lock();
        }
    }).disableSelection();
    //adjudicator_reset();
	free_adjudicator_update();
    $(".resetbutton").click(adjudicator_update);
    $(".rankingbutton").click(function() {
        $('.ranking').toggle();
    });
	$(".colourbutton").click(colour_draw);
	colour_draw();
    setInterval('adjudicator_update()',1000);
	setInterval('free_adjudicator_update()',5000);
});
