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
        
});


