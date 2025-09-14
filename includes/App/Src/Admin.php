<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
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
        add_menu_page(
            __( 'Member Directory', 'member-directory' ),
            __( 'Member Directory', 'member-directory' ),
            'manage_options',
            'md-members',
            [ $this, 'members_page' ],
            'dashicons-groups',
            26
        );

        add_submenu_page(
            'md-members',
            __( 'Members', 'member-directory' ),
            __( 'Members', 'member-directory' ),
            'manage_options',
            'md-members',
            [ $this, 'members_page' ]
        );

        add_submenu_page(
            'md-members',
            __( 'Teams', 'member-directory' ),
            __( 'Teams', 'member-directory' ),
            'manage_options',
            'md-teams',
            [ $this, 'teams_page' ]
        );
    }

    /** ================= MEMBERS ================= */
    public function members_page() {
    global $wpdb;
    $members = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}md_members");
    ?>
    <div class="container-fluid p-4">
        <h1 class="mb-4"><?php esc_html_e('Members', 'member-directory'); ?></h1>

        <div class="row g-4">
            <!-- Left column: Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">Add Member</div>
                    <div class="card-body">
                        <form id="md-add-member-form" class="row g-3" enctype="multipart/form-data">
                            <?php wp_nonce_field('md_nonce', 'security'); ?>

                            <!-- First Name -->
                            <div class="col-12">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>

                            <!-- Last Name -->
                            <div class="col-12">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <!-- Profile Image -->
                            <div class="col-12">
                                <label class="form-label">Profile Image</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                <div id="profile-image-preview" style="margin-top:10px;"></div>
                            </div>

                            <!-- Cover Image -->
                            <div class="col-12">
                                <label class="form-label">Cover Image</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <div id="cover-image-preview" style="margin-top:10px;"></div>
                            </div>

                            <!-- Address -->
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>

                            <!-- Favorite Color -->
                            <div class="col-6">
                                <label class="form-label">Favorite Color</label>
                                <input type="color" name="favorite_color" class="form-control form-control-color" value="#000000">
                            </div>

                            <!-- Status -->
                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">Add Member</button>
                            </div>
                        </form>

                        <!-- Message Container -->
                        <div id="md-member-message" class="mt-3"></div>
                    </div>
                </div>
            </div>


            <!-- Right column: Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-dark text-white">All Members</div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th><th>Name</th><th>Email</th><th>Color</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="md-members-list">
                                <?php foreach( $members as $m ): ?>
                                    <tr>
                                        <td><?php echo esc_html($m->id); ?></td>
                                        <td><?php echo esc_html($m->first_name . ' ' . $m->last_name); ?></td>
                                        <td><?php echo esc_html($m->email); ?></td>
                                        <td><span style="background:<?php echo esc_attr($m->favorite_color); ?>;padding:5px 15px;display:inline-block;"></span></td>
                                        <td><?php echo esc_html($m->status); ?></td>
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
                        <table class="table table-striped table-hover align-middle">
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
