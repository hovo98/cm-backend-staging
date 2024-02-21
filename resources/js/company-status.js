(function($) {
    "use strict"; // Start of use strict

    // On change submit form
    $(".company-status").on('change', function(e) {
        $(this).closest('form').submit();
    });

})(jQuery); // End of use strict
