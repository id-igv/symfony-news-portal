$( document ).ready( function() {
    function handleSuccess( response ) {
        var data = response.data;
        console.log( data );
        
        $("#js-search-tags").empty();
		
        data.forEach( function( element ) {
            var tag = JSON.parse( element );
            $( "#js-search-tags" ).append( "<li><a class='w3-button' href='" + tag.url + "'>" + tag.name + "</a></li>" ); 
        });
		
        $( "#js-search-tags" ).show();
    }
    
    function handleFail( response ) {
        $( "#js-search-tags" ).empty();
        $( "#js-search-tags" ).append( "<li>Ничего не найдено</li>" );
        $( "#js-search-tags" ).show();
    }
    
    $( "#js-search" ).on( {
        keyup: function( event ) {
            if ( $( "#js-search" ).val().length >= 2 ) {
                var data =  $( this ).serialize();
                
                $.ajax({
                    type: 'POST',
                    url: '{{ path("searchbar") }}',
                    data: data,
                    statusCode: {
                        200: handleSuccess,
                        404: handleFail
                    }
                });
            }
        },
        
        blur: function( event ) {
            if ( $( "#js-search" ).val().length == 0) {
                $( "#js-search" ).val('');
                $( "#js-search-tags" ).hide();
            }
        }
    });
});