


(function($) {
    "use strict";
    function md_modal(show = true) {
	    if (show) {
	        $('#md-loader-modal').fadeIn(200);
	    } else {
	        $('#md-loader-modal').fadeOut(200);
	    }
	}

     // Add Member via AJAX
    $(document).on('submit', '#md-add-member-form', function(e) {
        e.preventDefault();

        const firstName = $('input[name="first_name"]').val();
		const lastName  = $('input[name="last_name"]').val();
		const email     = $('input[name="email"]').val();
		const color     = $('input[name="favorite_color"]').val();
		const status    = $('select[name="status"]').val();


        if (!firstName || !lastName || !email) {
            alert('First name, last name, and email are required.');
            return;
        }

        // Optional: show loading
        md_modal();

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: "POST",
            data: {
                action: 'md_add_member',
                nonce: MD_AJAX.nonce,
                first_name: firstName,
                last_name: lastName,
                email: email,
                favorite_color: color,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data || 'Error adding member.');
                }
                md_modal(false);
            },
            error: function() {
                alert('Something went wrong!');
                md_modal(false);
            }
        });
    });

    // Delete Member via AJAX
    $(document).on('click', '.md-delete-member', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this member?')) return;

        const memberId = $(this).data('id');
        if (!memberId) return;

        md_modal();

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: "POST",
            data: {
                action: 'md_delete_member',
                nonce: MD_AJAX.nonce,
                member_id: memberId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data || 'Error deleting member.');
                }
                md_modal(false);
            },
            error: function() {
                alert('Something went wrong!');
                md_modal(false);
            }
        });
    });

    // Delegated event for Add Team button / form submission
    $(document).on('submit', '#md-add-team-form', function(e) {
        e.preventDefault();

        const teamName = $('#team_name').val();
        const teamDesc = $('#team_description').val();

        if (!teamName) {
            alert('Team name is required.');
            return;
        }

        // Optional: show loading modal
        md_modal();

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: "POST",
            data: {
                nonce: MD_AJAX.nonce,
                action: 'md_add_team',
                name: teamName,
                short_description: teamDesc
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data); // Success message
                    location.reload();     // Reload to show new team
                } else {
                    alert(response.data || 'An error occurred.');
                }
                md_modal(false);
            },
            error: function() {
                alert('Something went wrong!');
                md_modal(false);
            }
        });
    });

})(jQuery);
