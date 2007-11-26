$(document).ready( function () {
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
                $('#toggle_config').html("&laquo; Hide Config Panel");
            },            
            function () {
                $('#config').addClass("hide");
                $('#toggle_config').html("Show Config Panel &raquo;");                
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
        
        //Configure 'start' event handler
        $('#start').bind('click', function(e) {
            $('#scrolldisplay')[0].scrollTo(0)
        });
        
});


