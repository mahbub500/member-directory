<?php
namespace MemberDirectory\Traits;
use MemberDirectory\Traits\Rest;

trait Token {

    use Rest;

    /**
     * Generate a new token for a user after login.
     *
     * @param int $user_id The user ID.
     * @return string The generated token.
     */
    public function generate_token( $user_id ) {
        // Generate a random string
        $token = wp_generate_password( 32, false, false );

        // Save token in user meta with an expiration (optional: 1 day)
        update_user_meta( $user_id, '_auth_token', $token );
        update_user_meta( $user_id, '_auth_token_expiry', time() + HOUR_IN_SECONDS );

        return $token;
    }

    /**
     * Validate the given authentication token.
     *
     * - Finds the user by token (stored in user meta).
     * - Checks if the token has expired.
     * - Applies rate limiting via check_rate_limit().
     *
     * @param string $token  The authentication token.
     * @return int|WP_Error  Returns user ID if valid, or response_error() if invalid/expired/rate-limited.
     */
    public function validate_token( $token ) {
        // Find user by token
        $users = get_users( [
            'meta_key'   => '_auth_token',
            'meta_value' => $token,
            'number'     => 1,
            'fields'     => 'ID',
        ] );

        if ( empty( $users ) ) {
            return $this->response_error([
                'Token Invalid Sign in again and send it with `Authorization: Bearer <token>'
                
            ]);
        }

        $user_id = $users[0];

        // 🔹 Check expiry
        $expiry  = get_user_meta( $user_id, '_auth_token_expiry', true );
        if ( $expiry && time() > $expiry ) {
            delete_user_meta( $user_id, '_auth_token' );
            delete_user_meta( $user_id, '_auth_token_expiry' );
            return $this->response_error( 'Token expired. Please log in again.' );
        }

        // 🔹 Call rate limit checker
        $rate_check = $this->check_rate_limit( $user_id );
        if ( $rate_check !== true ) {
            return $rate_check; // already a response_error()
        }

        return $user_id;
    }
    /**
     * Apply a simple rate limit for API requests.
     *
     * - Limits requests per user within a given time window.
     * - Uses WordPress transients to track request counts.
     *
     * @param int $user_id  The user ID to check rate limit for.
     * @return true|WP_Error  Returns true if allowed, or response_error() if limit exceeded.
     */
    public function check_rate_limit( $user_id ) {
        $key    = 'rate_limit_' . $user_id;
        $limit  = 20;   // max requests
        $window = MINUTE_IN_SECONDS;   // seconds

        $count = get_transient( $key ) ?: 0;
        if ( $count >= $limit ) {
            return $this->response_error(
                'Rate limit exceeded. You can send max 10 requests per minute.'
            );
        }

        set_transient( $key, $count + 1, $window );
        return true;
    }

    /**
     * Get the auth token for a user if not expired.
     *
     * @param int $user_id The user ID.
     * @return string|false Token string if valid, false otherwise.
     */
    public function get_token_by_user_id( $user_id ) {
        $token  = get_user_meta( $user_id, '_auth_token', true );
        $expiry = get_user_meta( $user_id, '_auth_token_expiry', true );

        // No token found
        if ( ! $token ) {
            return $this->response_error(
                'Token Not Found'
            );
        }

        // Token expired
        if ( $expiry && time() > $expiry ) {
            delete_user_meta( $user_id, '_auth_token' );
            delete_user_meta( $user_id, '_auth_token_expiry' );
            return $this->response_error(
                'Token expired.'
            );
        }

        // Token exists and valid
        return $token;
    }
}
