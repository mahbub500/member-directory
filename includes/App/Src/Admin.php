<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\App\Src\Member;
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
            [ 'jquery' ],
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
            [ $this, 'teams_page' ]
        );
    }

    /** ================= TEAMS ================= */
    public function teams_page() {
    global $wpdb;
    $teams = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}md_teams");
    ?>
    <div class="container-fluid p-4">
        <h1 class="mb-4"><?php esc_html_e('Teams', 'member-directory'); ?></h1>

        <div class="row g-4">
            <!-- Left column: Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">Add Team</div>
                    <div class="card-body">
                        <form id="md-add-team-form" class="row g-3">
                            <?php wp_nonce_field( 'md_nonce', 'security' ); ?>
                            <div class="col-12">
                                <label class="form-label">Team Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Short Description</label>
                                <textarea name="short_description" class="form-control"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-success w-100">Add Team</button>
                            </div>
                        </form>
                        <div id="md-team-message" class="mt-3"></div>
                    </div>
                </div>
            </div>

            <!-- Right column: Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">All Teams</div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-hover align-middle ">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th><th>Name</th><th>Description</th>
                                </tr>
                            </thead>
                            <tbody id="md-teams-list">
                                <?php foreach( $teams as $t ): ?>
                                    <tr>
                                        <td><?php echo esc_html($t->id); ?></td>
                                        <td><?php echo esc_html($t->name); ?></td>
                                        <td><?php echo esc_html($t->short_description); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

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
