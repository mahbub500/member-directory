/*Teamp Chat system*/

jQuery(document).ready(function($){

    let teamId = $('#team-chat-form').data('team-id');

    // alert( teamId );

    function loadMessages(){
        $.ajax({
            url: MD_AJAX.ajaxurl,
            type: 'GET',
            data: { action: 'md_get_team_messages', team_id: teamId, nonce: MD_AJAX.nonce },
            success: function(res){
                console.log( res );
                if(res.success){
                    let html = '';
                    res.data.forEach(msg => {
                        html += `
                            <div class="mb-2 d-flex align-items-start">
                                <img src="${msg.image}" alt="#" class="rounded-circle me-2" width="30" height="50">
                                <div>
                                    <strong>${msg.sender}</strong> 
                                    <small class="text-muted">${msg.time}</small><br>
                                    ${msg.message}
                                </div>
                            </div>
                        `;
                    });
                    $('#chat-messages').html(html).scrollTop($('#chat-messages')[0].scrollHeight);
                }
            }
        });
    }

    $('#team-chat-form').on('submit', function(e){
        e.preventDefault();
        let message = $('#chat-message-input').val();
        if(message.trim() === '') return;

        $.post(MD_AJAX.ajaxurl, {
            action: 'md_send_team_message',
            team_id: teamId,
            message: message,
            nonce: MD_AJAX.nonce
        }, function(res){
            if(res.success){
                $('#chat-message-input').val('');
                loadMessages();
            } else {
                alert(res.data.message);
            }
        });
    });

    if(teamId){
        loadMessages();
        setInterval(loadMessages, 3000);
    }
});