<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Ajax {

    use Hook;

    public function __construct() {
        $this->register_ajax( 'md_add_member', [ $this, 'add_member' ] );

        // $this->register_ajax( 'md_add_member', [ $this, 'ajax_add_member' ] );
    }

    /** ================= AJAX ================= */
    public function add_member() {
        // check_ajax_referer( 'md_nonce', 'security' );
        global $wpdb;

        $table = $wpdb->prefix . 'md_members';
        $wpdb->insert( $table, [
            'first_name'    => sanitize_text_field($_POST['first_name']),
            'last_name'     => sanitize_text_field($_POST['last_name']),
            'email'         => sanitize_email($_POST['email']),
            'favorite_color'=> sanitize_text_field($_POST['favorite_color']),
            'status'        => sanitize_text_field($_POST['status']),
        ] );

        wp_send_json_success([ 'message' => 'Member added successfully.' ]);
    }

    public function ajax_add_team() {
        check_ajax_referer( 'md_nonce', 'security' );
        global $wpdb;

        $table = $wpdb->prefix . 'md_teams';
        $wpdb->insert( $table, [
            'name'              => sanitize_text_field($_POST['name']),
            'short_description' => sanitize_textarea_field($_POST['short_description']),
        ] );

        wp_send_json_success([ 'message' => 'Team added successfully.' ]);
    }
}