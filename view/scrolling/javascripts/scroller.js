$(document).ready( function () {
        
       // Calculate height of components other than scrolldisplay div
       var totalHeight = $('#header').outerHeight() +
                         $('.tabconfig').outerHeight() +
                         $('#theader').outerHeight() +
                         $('.footer').outerHeight();
       
        // Adjust height of scrolldisplay to maximise height
        $('#scrolldisplay').height($(window).outerHeight() - totalHeight);
        
        // Attach scrollpane
        $('#scrolldisplay').jScrollPane({animateTo:true, animateInterval:10, animateStep:2});
		
        // Attach timer to scroll
        $.timer(100, function (timer) {
               $('#scrolldisplay')[0].scrollBy(parseInt($('#scrolldisplay').attr('rel')));
        });
        
        // Zebra tables
        $("tr:nth-child(odd)").addClass("odd");
        
        //Configure 'Show Config' link
        $('#toggle_config').toggle(         
            function () {
                $('#config').removeClass("hide");
                $('#toggle_config').html("&laquo; Hide");
            },            
            function () {
                $('#config').addClass("hide");
                $('#toggle_config').html("Settings &raquo;");                
            }
        );
        
        //Configure 'slower' event handler
        $('#slower').bind('click', function(e) {
            speed =  parseInt($('#scrolldisplay').attr('rel'));
            if (speed > 0)
               $('#scrolldisplay').attr({
                   rel: speed - 1
               });
        });
        
        //Configure 'faster' event handler
        $('#faster').bind('click', function(e) {
            speed =  parseInt($('#scrolldisplay').attr('rel'));
            if (speed < 10)
               $('#scrolldisplay').attr({
                   rel: speed + 1
               });
        });
        
        //Configure 'top' event handler
        $('#top').bind('click', function(e) {
            $('#scrolldisplay')[0].scrollTo(0);
        });
        
		//CSS doesn't work. IE doesn't work. FF doesn't work some of the time.
		//But we have ways...
		var width=$('#theader').width()*1.01533151;
		$('#theader').width(width);
		$('#teamhead').width($('#team1').width());
		$('#venuehead').width($('#venue1').width());
		$('#open_govhead').width($('#open_gov1').width());
		$('#open_opphead').width($('#open_opp1').width());
		$('#close_govhead').width($('#close_gov1').width());
		$('#close_opphead').width($('#close_opp1').width());
		$('#chairhead').width($('#chair1').width());
		$('#panelistshead').width($('#panelists1').width());
		$('#traineehead').width($('#trainee1').width());
        
});


