$(document).ready(function () {
    /**
     * Footer newsletter subscription
     */
    $('footer #newsletter-form').submit(function(e) {
        e.preventDefault();
        $('.newsletter-message').hide();
        $('#newsletter-pending').show();
        $.post($(this).data('url'), $(this).serialize(), function(data) {
            $('#newsletter-pending').hide();
            if (400 == data.status) {
                $('#newsletter-error').show();
            } else {
                $('#newsletter-success').show();
            }
        });
    });

});