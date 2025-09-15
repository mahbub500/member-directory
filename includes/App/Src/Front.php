<?php
namespace MemberDirectory\App\Src;
use MemberDirectory\Traits\Hook;
use MemberDirectory\Helper\Utility;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Front {

    use Hook;

    public function __construct() {
        $this->action( 'wp_head', [ $this, 'head' ] );
        $this->action( 'template_redirect', [ $this, 'user_profile_template' ] );
    }

    public function head(){
        
        // Utility::pri( $tm->ID );
        // get_team_members( 4 );
    }

    public function user_profile_template(){
         if ( ! is_user_logged_in() ) {
            return;
        }

        global $wp;

        // Get the requested path
        $requested_slug = sanitize_text_field( $wp->request ); // e.g., 'john_doe'

        // Skip if empty
        if ( empty( $requested_slug ) ) {
            return;
        }

        // Check for first_name_last_name pattern
        if ( strpos( $requested_slug, '_' ) === false ) {
            return;
        }

        $parts = explode('_', $requested_slug);
        if ( count( $parts ) < 2 ) {
            return;
        }

        $first_name = ucfirst( $parts[0] );
        $last_name  = ucfirst( $parts[1] );

        // Query WP user by first_name and last_name
        $user_query = get_users([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key'     => 'first_name',
                    'value'   => $first_name,
                    'compare' => '='
                ],
                [
                    'key'     => 'last_name',
                    'value'   => $last_name,
                    'compare' => '='
                ]
            ],
            'number' => 1
        ]);

        if ( empty( $user_query ) ) {
            wp_die( esc_html__( 'User not found.', 'member-directory' ) );
        }

        $user = $user_query[0];

        // Get meta
        $cover_img   = get_user_meta( $user->ID, 'cover_image', true ) ?: 'https://via.placeholder.com/800x200';
        $address     = get_user_meta( $user->ID, 'address', true );
        $color       = get_user_meta( $user->ID, 'favorite_color', true );
        $status      = get_user_meta( $user->ID, 'status', true );

        // Load the template
        get_header(); 
        ?>
        <div class="container my-5">
            <a href="javascript:history.back()" class="btn btn-secondary mb-3">
                &larr; <?php esc_html_e('Go Back','member-directory'); ?>
            </a>
            <div class="card shadow-sm">
                <div class="card-header p-0" style="height:200px; background-image: url('<?php echo esc_url($cover_img); ?>'); background-size: cover; background-position: center;"></div>
                <div class="card-body text-center">
                    <img src="<?php echo get_user_profile_image( $user->ID) ; ?>" alt="<?php echo esc_attr($user->display_name); ?>" class="rounded-circle border border-3 border-white mb-3" style="width:120px; height:120px; margin-top:-60px;">
                    <h3 class="fw-bold"><?php echo esc_html($user->first_name . ' ' . $user->last_name); ?></h3>
                    <p class="text-muted"><?php echo esc_html($user->user_email); ?></p>
                    <?php if($address): ?><p><strong><?php esc_html_e('Address','member-directory'); ?>:</strong> <?php echo esc_html($address); ?></p><?php endif; ?>
                    <?php if($color): ?><p><strong><?php esc_html_e('Favorite Color','member-directory'); ?>:</strong> <span style="display:inline-block;width:20px;height:20px;background:<?php echo esc_attr($color); ?>;border-radius:50%;"></span></p><?php endif; ?>
                    <?php if($status): ?><p><strong><?php esc_html_e('Status','member-directory'); ?>:</strong> <?php echo esc_html($status); ?></p><?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        get_footer(); 
        exit; 
    }
}