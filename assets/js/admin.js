


(function($) {
    "use strict";
    function md_modal(show = true) {
	    var $modal = $('.md-loader-modal');
	    if (!$modal.length) return;

	    if (show) {
	        $modal.show();  // show with fade-in effect
	    } else {
	        $modal.hide(); // hide with fade-out effect
	    }
	}

     // Add Member via AJAX
	$(document).on('submit', '#md-add-member-form', function(e) {
	    e.preventDefault();

	    const formData = new FormData(this); // includes files automatically
	    formData.append('action', 'md_add_member');
	    formData.append('nonce', MD_AJAX.nonce);

	    // Optional: show loader
	    md_modal(true);

	    $.ajax({
	        url: MD_AJAX.ajaxurl,
	        type: 'POST',
	        data: formData,
	        contentType: false,
	        processData: false,
	        success: function(response) {
	            md_modal(false);
	            if (response.success) {
	                alert(response.data);
	                location.reload();
	            } else {
	                alert(response.data || 'Error adding member.');
	            }
	        },
	        error: function() {
	            md_modal(false);
	            alert('Something went wrong!');
	        }
	    });
	});


    // Preview Profile Image
    $('input[name="profile_image"]').on('change', function() {
        const preview = $('#profile-image-preview');
        preview.empty();

        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" style="width:100px; height:100px; object-fit:cover; border-radius:8px;" />');
            }
            reader.readAsDataURL(file);
        }
    });

    // Preview Cover Image
    $('input[name="cover_image"]').on('change', function() {
        const preview = $('#cover-image-preview');
        preview.empty();

        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" style="width:150px; height:80px; object-fit:cover; border-radius:8px;" />');
            }
            reader.readAsDataURL(file);
        }
    });

    // Delete Member via AJAX
    $(document).on('click', '.md-delete-member', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this member?')) return;

        const memberId = $(this).data('id');
        if (!memberId) return;

        md_modal( true );

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
        md_modal( true );

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
