<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Team {

    use Hook;

    public function __construct() {        

        $this->register_ajax( 'md_add_team', [ $this, 'add_team' ] );

        $this->action( 'admin_footer', [$this, 'footer'] );
    }

    public function add_team() {
        // Check nonce
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'] ) ) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }

        // Validate input
        if (empty($_POST['name'])) {
            wp_send_json_error(['message' => 'Team name is required.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'md_teams';

        // Insert into database
        $wpdb->insert($table, [
            'name'              => sanitize_text_field($_POST['name']),
            'short_description' => sanitize_textarea_field($_POST['short_description']),
        ]);

        // Get last inserted ID
        $team_id = $wpdb->insert_id;

        // Prepare team data
        $team_data = [
            'id' => $team_id,
            'name' => sanitize_text_field($_POST['name']),
            'short_description' => sanitize_textarea_field($_POST['short_description']),
        ];

        // Return success with last inserted team
        wp_send_json_success([
            'message' => 'Team added successfully.',
            'team'    => $team_data
        ]);
    }


    public function teams_page() {        

        $page   = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
        $teams  = get_data('md_teams', $page, 10);
        
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
                                    <?php foreach( $teams['data'] as $t ): ?>
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
}