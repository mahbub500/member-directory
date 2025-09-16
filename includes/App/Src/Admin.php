<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\App\Src\Member;
use MemberDirectory\App\Src\Team;
use MemberDirectory\App\Src\Assign;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Admin {

    use Hook;

    public function __construct() {
        $this->action( 'admin_menu', [ $this, 'register_menus' ] );
        $this->action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        $this->action( 'admin_footer', [ $this, 'footer' ] );       

    }    

    public function enqueue_assets() {

        wp_enqueue_script('jquery');

        // jQuery UI (for draggable/droppable)
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        wp_enqueue_style(
            'jquery-ui-css',
            'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css'
        );

        wp_enqueue_style(
            'md-admin-css',
            MD_ASSETS_URL . 'css/admin.css',
            [],
            filemtime(MD_PLUGIN_DIR . 'assets/css/admin.css')
        );

        // Plugin Admin JS using constant
        wp_enqueue_script(
            'md-admin',
            MD_ASSETS_URL . 'js/admin.js', // Use MD_ASSETS_URL
            ['jquery', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            time(),
            true
        );
        // CSS
        wp_enqueue_style(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
        );

        // JS
        wp_enqueue_script(
            'bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            [],
            null,
            true
        );

        // Localize AJAX
        wp_localize_script(
            'md-admin',
            'MD_AJAX',
            [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce(),
            ]
        );
    }


    public function register_menus() {
        $member = new Member();
        $team   = new Team();
        $assign = new Assign();
        $chatlist = new Chatlist();
        add_menu_page(
            __( 'Member Directory', 'member-directory' ),
            __( 'Member Directory', 'member-directory' ),
            'manage_options',
            'members',
            [ $member, 'members_page' ], // callback from Member class
            'dashicons-groups',
            26
        );

        add_submenu_page(
            'members',
            __( 'Members', 'member-directory' ),
            __( 'Members', 'member-directory' ),
            'manage_options',
            'members',
            [ $member, 'members_page' ]
        );

        add_submenu_page(
            'members',
            __( 'Teams', 'member-directory' ),
            __( 'Teams', 'member-directory' ),
            'manage_options',
            'teams',
            [ $team, 'teams_page' ]
        );

        add_submenu_page(
            'members',
            __( 'Assign', 'member-directory' ),
            __( 'Assign', 'member-directory' ),
            'manage_options',
            'assign',
            [ $assign, 'assign_page' ]
        );

        add_submenu_page(
            'members',
            __( 'Chat List', 'member-directory' ),
            __( 'Chat List', 'member-directory' ),
            'manage_options',
            'chatlist',
            [ $chatlist, 'chatlist' ]
        );
    }

    /** ================= TEAMS ================= */
    

    public function footer() {
        ?>        
        <div class="md-loader-modal" style="display: none;">
            <div class="md-loader-box">
                <div class="md-loader-circle"></div>
                <p>Please wait...</p>
            </div>
        </div>
        <?php
    }    
}
