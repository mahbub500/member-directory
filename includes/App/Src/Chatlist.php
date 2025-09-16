<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Chatlist {

    use Hook;

    public function __construct() {
        $this->action( 'wp_head', [ $this, 'head' ] );        
    }

    public function chatlist() {
    global $wpdb;

    // Get all teams
    $teams = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}md_teams ORDER BY name ASC" );

    // Get all chats
    $chat_table = $wpdb->prefix . 'md_team_chat';
    $all_chats = $wpdb->get_results( "SELECT * FROM $chat_table ORDER BY created_at ASC" );

    // Organize chats by team
    $chats_by_team = [];
    foreach ( $all_chats as $chat ) {
        $chats_by_team[ $chat->team_id ][] = $chat;
    }
    ?>
    <div class="my-team-dashboard container my-4">
        <div class="row">
            <!-- Team List -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <h5 class="card-header bg-primary text-white mb-0">
                        <?php echo esc_html__( 'Teams', 'member-directory' ); ?>
                    </h5>
                    <ul class="list-group list-group-flush" id="team-list">
                        <?php if ( $teams ) : ?>
                            <?php foreach ( $teams as $team ) : ?>
                                <li class="list-group-item team-item" data-team-id="<?php echo esc_attr( $team->id ); ?>">
                                    <?php echo esc_html( $team->name ); ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li class="list-group-item text-muted"><?php esc_html_e( 'No teams found.', 'member-directory' ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <h5 class="card-header bg-secondary text-white mb-0">
                        <?php echo esc_html__( 'Chat', 'member-directory' ); ?>
                    </h5>
                    <div class="card-body" id="chat-box">
                        <p class="text-muted"><?php esc_html_e( 'Select a team to view chats.', 'member-directory' ); ?></p>

                        <?php if ( $chats_by_team ) : ?>
                            <?php foreach ( $chats_by_team as $team_id => $chats ) : ?>
                                <div class="team-chat" data-team-id="<?php echo esc_attr( $team_id ); ?>" style="display:none;">
                                    <ul class="list-group">
                                        <?php foreach ( $chats as $chat ) : 
                                            $user = get_userdata( $chat->sender_id );
                                            $name = $user ? $user->display_name : __( 'Unknown', 'member-directory' );
                                        ?>
                                            <li class="list-group-item">
                                                <strong><?php echo esc_html( $name ); ?>:</strong> 
                                                <?php echo esc_html( $chat->message ); ?>
                                                <br>
                                                <small class="text-muted"><?php echo esc_html( $chat->created_at ); ?></small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>  
    <?php
}


}