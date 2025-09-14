(function($) {
    "use strict";

    // Example: Handle form submission via AJAX
    $('#md-add-member-form').on('submit', function(e) {
        e.preventDefault();

        var data = {
            action: 'md_add_member',
            nonce: MD_AJAX.nonce,
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            email: $('#email').val(),
            favorite_color: $('#favorite_color').val(),
            status: $('#status').val(),
        };

        $.post(MD_AJAX.ajaxurl, data, function(response) {
            alert(response.data); // Display response
            location.reload(); // Reload page to see new member
        });
    });

    // Example: Delete member
    $('.md-delete-member').on('click', function(e) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this member?')) return;

        var member_id = $(this).data('id');

        $.post(MD_AJAX.ajaxurl, {
            action: 'md_delete_member',
            nonce: MD_AJAX.nonce,
            member_id: member_id
        }, function(response) {
            alert(response.data);
            location.reload();
        });
    });

    // Handle Add Team Form Submission
    $('#md-add-team-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submit

        // Collect form data
        var data = {
            action: 'md_add_team',     // AJAX action
            nonce: MD_AJAX.nonce,      // Security nonce
            name: $('#team_name').val(),
            short_description: $('#team_description').val()
        };

        // Send AJAX request
        $.post(MD_AJAX.ajaxurl, data, function(response) {
            if(response.success){
                alert(response.data); // Success message
                location.reload();     // Reload to show new team
            } else {
                alert('Error: ' + response.data);
            }
        });
    });

})(jQuery);
