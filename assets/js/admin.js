
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

    // Preview profile and cover images on change
    $('input[name="edit_profile_image"], input[name="edit_cover_image"]').on('change', function () {
        let preview = $(this).attr('name') === 'edit_profile_image' ? $('#md-edit-profile-preview') : $('#md-edit-cover-preview');
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.attr('src', e.target.result); // Update image preview
            };
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

    $(document).on('click', '.md-delete-member', function(e){
        e.preventDefault();
        const row = $(this).closest('tr');
        const memberId = $(this).data('id');

        // Store the member ID in hidden input
        $('#md-delete-member-id').val(memberId);

        // Show the modal
        $('#md-delete-member-modal').modal('show');
    });


    // Delete Member via AJAX
    $(document).on('click', '#md-confirm-delete-member', function(){
        const memberId = $('#md-delete-member-id').val();
        if (!memberId) return;

        md_modal(true);

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: 'POST',
            data: {
                action: 'md_delete_member',
                nonce: MD_AJAX.nonce,
                member_id: memberId
            },
            success: function(response){
                md_modal(false);
                if(response.success){
                    // Remove row from table
                    $('.md-member-row[data-id="' + memberId + '"]').remove();
                    $('#md-delete-member-modal').modal('hide');
                } else {
                    alert(response.data || 'Failed to delete member.');
                }
            },
            error: function(){
                md_modal(false);
                alert('Something went wrong!');
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

        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // alert(response.data.data); // your PHP success message

                    const member = response.data.member;
                    const row = $('.md-member-row[data-id="' + member.id + '"]');

                    // Update row data attributes
                    row.data('firstname', member.first_name)
                       .data('lastname', member.last_name)
                       .data('email', member.email)
                       .data('address', member.address)
                       .data('color', member.favorite_color)
                       .data('status', member.status)
                       .data('profile', member.profile_image)
                       .data('cover', member.cover_image);

                    // Update text fields
                    row.find('td:nth-child(4)').text(member.first_name + ' ' + member.last_name);
                    row.find('td:nth-child(5)').text(member.email);
                    row.find('td:nth-child(6)').text(member.address);
                    row.find('td:nth-child(7) span').css('background', member.favorite_color);
                    row.find('td:nth-child(8)').text(member.status);

                    // Update profile image
                    const profileCell = row.find('td:nth-child(2)');
                    if (member.profile_image) {
                        profileCell.html('<img src="' + member.profile_image + '" alt="Profile" style="width:40px;height:40px;border-radius:50%;cursor:pointer;">');
                    } else {
                        profileCell.html('<span class="text-muted">N/A</span>');
                    }

                    // Update cover image
                    const coverCell = row.find('td:nth-child(3)');
                    if (member.cover_image) {
                        coverCell.html('<img src="' + member.cover_image + '" alt="Cover" style="width:60px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;">');
                    } else {
                        coverCell.html('<span class="text-muted">N/A</span>');
                    }

                    // Hide modal
                    $('#md-edit-modal').modal('hide');
                } else {
                    alert(response.data.message || 'Update failed');
                }
            }

        });
    });



/*JQuery code for Team member*/

    $(document).on('submit', '#md-add-team-form', function(e) {
        e.preventDefault();

        const formData = new FormData(this); // automatically includes 'name' and 'short_description'
        formData.append('action', 'md_add_team');
        formData.append('nonce', MD_AJAX.nonce);

        // Debug: check formData
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        // Optional loader
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
                    // alert(response.data.message || 'Team added successfully');

                    // Append new team to table dynamically
                    const team = response.data.team;
                    $('#md-teams-list').append(`
                        <tr>
                            <td>${team.id}</td>
                            <td>${team.name}</td>
                            <td>${team.short_description}</td>
                        </tr>
                    `);

                    // Reset form
                    $('#md-add-team-form')[0].reset();
                } else {
                    alert(response.data.message || 'Error adding team.');
                }
            },
            error: function(err) {
                md_modal(false);
                console.error(err);
                alert('Something went wrong!');
            }
        });
    });

    $(document).on('click', '.md-edit-team', function(){
        const row = $(this).closest('tr');
        $('#md-edit-team-id').val(row.data('id'));
        $('#md-edit-team-name').val(row.data('name'));
        $('#md-edit-team-description').val(row.data('description'));
        $('#md-edit-team-modal').modal('show');
    });

    // Save Edit
    $(document).on('submit', '#md-edit-team-form', function(e){
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'md_edit_team');
        formData.append('nonce', MD_AJAX.nonce);

        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.success){
                    const team = response.data.team;
                    const row = $('.md-team-row[data-id="'+team.id+'"]');
                    row.data('name', team.name).data('description', team.short_description);
                    row.find('td:nth-child(2)').text(team.name);
                    row.find('td:nth-child(3)').text(team.short_description);

                    $('#md-edit-team-modal').modal('hide');
                } else {
                    alert(response.data.message || 'Update failed');
                }
            },
            error: function(){
                alert('Something went wrong!');
            }
        });
    });

    // Open Delete Modal
    $(document).on('click', '.md-delete-team', function(){
        const row = $(this).closest('tr');
        $('#md-delete-team-id').val(row.data('id'));
        $('#md-delete-team-modal').modal('show');
    });

    // Confirm Delete
    $(document).on('click', '#md-confirm-delete-team', function(){
        const team_id = $('#md-delete-team-id').val();
        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: 'POST',
            data: {
                action: 'md_delete_team',
                id: team_id,
                nonce: MD_AJAX.nonce
            },
            success: function(response){
                if(response.success){
                    $('.md-team-row[data-id="'+team_id+'"]').remove();
                    $('#md-delete-team-modal').modal('hide');
                } else {
                    alert(response.data.message || 'Delete failed');
                }
            },
            error: function(){
                alert('Something went wrong!');
            }
        });
    });

    /*Drag and Drop*/

    // Make members draggable
    $('#members-list .member-item').draggable({
        helper: 'clone', // clone element
        revert: 'invalid', // revert if not dropped on droppable
        cursor: 'move'
    });

    // Make team member lists droppable
    $('.team-members').droppable({
        accept: '#members-list .member-item',
        hoverClass: 'ui-state-hover',
        drop: function(event, ui) {
            const memberId = $(ui.draggable).data('id');
            const teamId = $(this).closest('.team-container').data('team-id');

            // Check if member already exists in team
            if ($(this).find(`li[data-id="${memberId}"]`).length) {
                alert('Member already in this team.');
                return;
            }

            // AJAX call to assign member
            $.ajax({
                url: MD_AJAX.ajaxurl,
                type: 'POST',
                data: {
                    action: 'md_assign_to_team',
                    nonce: MD_AJAX.nonce,
                    member_id: memberId,
                    team_id: teamId
                },
                success: function(response) {
                    if (response.success) {
                        // Add member to team list
                        const memberName = $(ui.draggable).text();
                        $(`.team-container[data-team-id="${teamId}"] .team-members`).append(
                            `<li class="list-group-item member-item" data-id="${memberId}">${memberName}</li>`
                        );
                        console.log('✅ Member assigned successfully');
                    } else {
                        alert(response.data.message || 'Failed to assign member.');
                    }
                },
                error: function() {
                    alert('Something went wrong!');
                }
            });
        }
    });
    


})(jQuery);

jQuery(document).on('click', '.md-remove-member', function() {
    const memberId = jQuery(this).data('member-id');
    const teamId = jQuery(this).data('team-id');
    const $li = jQuery(this).closest('li');

    jQuery.ajax({
        url: MD_AJAX.ajaxurl,
        type: 'POST',
        data: {
            action: 'md_remove_from_team',
            nonce: MD_AJAX.nonce,
            member_id: memberId,
            team_id: teamId
        },
        success: function(response) {
            if (response.success) {
                $li.remove(); // remove from DOM
                console.log('Member removed successfully');
            } else {
                alert(response.data.message || 'Failed to remove member.');
            }
        },
        error: function() {
            alert('Something went wrong!');
        }
    });
});





    


