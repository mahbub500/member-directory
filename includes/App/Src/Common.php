<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Common {

    use Hook;

    public function __construct() {
        $this->register_ajax( 'md_get_team_messages', [ $this, 'team_messages' ] );        
        $this->register_ajax( 'md_send_team_message', [ $this, 'send_team_message' ] );        
    }

    public function team_messages(){
        if ( ! isset($_GET['nonce']) || ! wp_verify_nonce($_GET['nonce'] ) ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        $team_id = intval( $_GET[ 'team_id' ] );     
        global $wpdb;
        $table = $wpdb->prefix . 'md_team_chat';

        $messages = $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM $table WHERE team_id=%d ORDER BY created_at ASC", $team_id )
        );

        $output = [];
        foreach ( $messages as $msg ) {
            $user_id    = $msg->sender_id;
            $output[] = [
                'sender'    => get_user_full_name(  $user_id ),
                'image'     => get_user_meta( $user_id, 'profile_image', true ),
                'message'   => esc_html( $msg->message ),
                'time'      => date( 'H:i', strtotime( $msg->created_at ) ),
            ];
        }

        wp_send_json_success( $output );
    }

    public function send_team_message() {

        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'] ) ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        $user_id = get_current_user_id();
        $team_id = intval($_POST['team_id']);
        $message = sanitize_text_field($_POST['message']);

        $team_id = intval( $_POST[ 'team_id' ] );

        if ( empty($message) || empty($team_id) ) wp_send_json_error(['message' => 'Message cannot be empty.']);

        global $wpdb;
        $table = $wpdb->prefix . 'md_team_chat';

        $wpdb->insert(
            $table,
            [
                'team_id'   => $team_id,
                'sender_id' => $user_id,
                'message'   => $message,
                'created_at'=> current_time('mysql'),
            ],
            ['%d','%d','%s','%s']
        );

        wp_send_json_success([
            'message' => $message,
            'sender'  => get_user_full_name($user_id),
            'time'    => current_time('H:i')
        ]);
    }
}