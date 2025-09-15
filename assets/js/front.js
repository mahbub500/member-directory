/*Teamp Chat system*/

jQuery(document).ready(function($){

     // Restore last active tab from cookie
    var activeTab = getCookie("activeTab");
    if (activeTab) {
        var tabTrigger = $('#teamTabs button[data-bs-target="' + activeTab + '"]');
        if (tabTrigger.length) {
            var tab = new bootstrap.Tab(tabTrigger[0]);
            tab.show();
        }
    }

    // Save active tab on click
    $('#teamTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e){
        var tabId = $(e.target).data("bs-target");
        setCookie("activeTab", tabId, 7); // Save for 7 days
    });

    // Cookie helpers
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    }

    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

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