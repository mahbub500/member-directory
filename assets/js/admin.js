


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

		            // Dynamically add new member to the table
		            const member = response.data.member; // You should return the newly added member's data from PHP
		            
		            // Create the table row
		            let newRow = `
		                <tr class="md-member-row"
		                    data-id="${member.id}"
		                    data-firstname="${member.first_name}"
		                    data-lastname="${member.last_name}"
		                    data-email="${member.email}"
		                    data-address="${member.address}"
		                    data-color="${member.favorite_color}"
		                    data-status="${member.status}"
		                    data-profile="${member.profile_image}"
		                    data-cover="${member.cover_image}"
		                >
		                    <td>${member.id}</td>
		                    <td>${member.profile_image ? `<img src="${member.profile_image}" alt="Profile" style="width:40px;height:40px;border-radius:50%;cursor:pointer;">` : `<span class="text-muted">N/A</span>`}</td>
		                    <td>${member.cover_image ? `<img src="${member.cover_image}" alt="Cover" style="width:60px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;">` : `<span class="text-muted">N/A</span>`}</td>
		                    <td>${member.first_name} ${member.last_name}</td>
		                    <td>${member.email}</td>
		                    <td>${member.address || ''}</td>
		                    <td><span style="background:${member.favorite_color};padding:5px 15px;display:inline-block;border-radius:4px;"></span></td>
		                    <td>${member.status}</td>
		                    <td>
		                        <button class="btn btn-sm btn-primary md-edit-member" data-id="${member.id}">Edit</button>
		                        <button class="btn btn-sm btn-danger md-delete-member" data-id="${member.id}">Delete</button>
		                    </td>
		                </tr>
		            `;

		            // Append to table body
		            $('#md-members-list').append(newRow);

		            // Optionally, reset the form
		            $('#md-add-member-form')[0].reset();
		            $('#profile-image-preview').html('');
		            $('#cover-image-preview').html('');

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

    // Click on entire row
    $(document).on('click', '.md-member-row', function(e){
	    // If clicked element is Edit or Delete button, do nothing
	    if ($(e.target).hasClass('md-edit-member') || $(e.target).hasClass('md-delete-member')) return;

	    var row = $(this);

	    // Populate modal
	    $('#md-modal-profile').attr('src', row.data('profile') || '');
	    $('#md-modal-cover').attr('src', row.data('cover') || '');
	    $('#md-modal-name').text(row.data('firstname') + ' ' + row.data('lastname'));
	    $('#md-modal-email').text(row.data('email'));
	    $('#md-modal-address').text(row.data('address'));
	    $('#md-modal-color').css('background', row.data('color'));
	    $('#md-modal-status').text(row.data('status'));

	    // Show modal
	    $('#md-image-modal').modal('show');
	});




    // Delete Member via AJAX
    $(document).on('click', '.md-delete-member', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete this member?')) return;

        const memberId = $(this).data('id');
        console.log( memberId );
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
                    $('.md-member-row[data-id="' + memberId + '"]').remove();
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

    $(document).on('click', '.md-edit-member', function() {
    	const row = $(this).closest('.md-member-row');

	    $('#md-edit-id').val(row.data('id'));
	    $('#md-edit-firstname').val(row.data('firstname'));
	    $('#md-edit-lastname').val(row.data('lastname'));
	    $('#md-edit-email').val(row.data('email'));
	    $('#md-edit-address').val(row.data('address'));
	    $('#md-edit-color').val(row.data('color'));
	    $('#md-edit-status').val(row.data('status'));

	    // Load profile & cover images from data attributes
	    $('#md-edit-profile-preview').attr('src', row.data('profile'));
	    $('#md-edit-cover-preview').attr('src', row.data('cover'));

	    $('#md-edit-modal').modal('show');
	});

    $(document).on('submit', '#md-edit-member-form', function(e) {
    e.preventDefault();

    let formData = new FormData(this);
    formData.append('action', 'md_update_member');
    formData.append('nonce', MD_AJAX.nonce);

    $.ajax({
        url: MD_AJAX.ajaxurl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert(response.data.message);

                const member = response.data.member;
                const row = $('.md-member-row[data-id="' + member.id + '"]');

                // Update table row values
                row.data('firstname', member.first_name)
                   .data('lastname', member.last_name)
                   .data('email', member.email)
                   .data('address', member.address)
                   .data('color', member.favorite_color)
                   .data('status', member.status)
                   .data('profile', member.profile_image)
                   .data('cover', member.cover_image);

                row.find('td:nth-child(4)').text(member.first_name + ' ' + member.last_name);
                row.find('td:nth-child(5)').text(member.email);
                row.find('td:nth-child(6)').text(member.address);
                row.find('td:nth-child(7) span').css('background', member.favorite_color);
                row.find('td:nth-child(8)').text(member.status);

                $('#md-edit-modal').modal('hide');
            } else {
                alert(response.data.message || 'Update failed');
            }
        }
    });
});




})(jQuery);
