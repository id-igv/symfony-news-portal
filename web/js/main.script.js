$( document ).ready( function() {
    var subscriptionCookie = "showSubscribtionForm=none";
    
    $( "#js-go-top" ).hide();
    
    function makeThemCry() {
        var show = document.cookie;
        
        if ( show.indexOf( subscriptionCookie ) === -1) {
            document.getElementById( 'sub-form' ).style.display='block';
            dontShowSubscribe();
        }
    }
    
    function dontShowSubscribe() {
        document.cookie = subscriptionCookie;
    }
    
    setTimeout(makeThemCry, 15000);
    
    $( document ).scroll( function() {
        if ( $( document ).scrollTop() > 50) {
            $( "#js-go-top" ).show();
        }
        else {
            $( "#js-go-top" ).hide();
        }
    });
    
    $( document ).scrollTop( function() {
        $( "#js-go-top" ).show();
    });
    
    // here is the place for before-closing-page-event
    // $( window ).bind( 'unload', function( event ) {
    //     var leave = confirm( 'Вы действительно хотите покинуть сайт?') ;
        
    //     if (leave) {
    //         event.preventDefault();
    //     }
    // });
});