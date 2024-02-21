(function($) {
    "use strict"; // Start of use strict

    var $form = $( "#config" );
    var $inputLoan = $form.find( ".loan-size" );

    // Loan size add comas
    $($inputLoan).on('keyup', function(e) {

        var selection = window.getSelection().toString();
        if ( selection !== '' ) {
            return;
        }

        if ( $.inArray( e.keyCode, [38,40,37,39] ) !== -1 ) {
            return;
        }

        var $this = $( this );
        var input = $this.val();

        var input = input.replace(/[\D\s\._\-]+/g, "");

        input = input ? parseInt( input, 10 ) : 0;

        $this.val( function() {
            return ( input === 0 ) ? "" : input.toLocaleString( "en-US" );
        } );
    });


})(jQuery); // End of use strict
