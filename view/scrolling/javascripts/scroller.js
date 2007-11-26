$(document).ready( function () {
        //Attach scrollpane
        $('#scrolldisplay').jScrollPane({animateTo:true, animateInterval:10, animateStep:2});

        //Attach timer to scroll
        $.timer(100, function (timer) {
               $('#scrolldisplay')[0].scrollBy(parseInt($('#scrolldisplay').attr('rel')));
        });
        
        
});


