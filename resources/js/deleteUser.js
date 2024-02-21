(function($) {
    "use strict"; // Start of use strict

    // On change submit form
    $('.js-delete-user').on('click', function(e) {
        const form = $('delete-user__popup');

        form.prevObject[0].forms['delete-user'].action = `/users/${$(this).data('id')}/delete`;
        $('.delete-user__popup').addClass('delete-user__popup--active');

    });

    $('.js-sub-user').on('click', function(e) {
         const form = $('sub-user__popup');

         form.prevObject[0].forms['sub-user'].action = `/users/${$(this).data('id')}/giftSub`;
         $('.sub-user__popup').addClass('delete-user__popup--active');

    });

    $(".js-close-popup").on('click', function(e) {
        $('.delete-user__popup').removeClass('delete-user__popup--active');
    });

    $(".close-sub-modal").on('click', function(e) {
        $('.sub-user__popup').removeClass('delete-user__popup--active');
    });

})(jQuery); // End of use strict
