<?php
namespace MemberDirectory\App\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Rest
 *
 * This trait provides methods to register REST API routes and handle JSON responses in the WordPress plugin.
 *
 * @package 
 */
trait Rest {

	

	/**
	 * Registers a new REST API route.
	 *
	 * @param string $path The route path.
	 * @param array  $args The route arguments.
	 */
	public function register_route( $path, $args, $namespace ) {

		// // If a permission callback is specified in the arguments, set it correctly.
		if ( isset( $args['permission'] ) ) {
			$args['permission_callback'] = $args['permission'];
			unset( $args['permission'] );
		}

		// Register the route with the specified namespace, path, and arguments.
		register_rest_route( $namespace, $path, $args );
	}

	/**
	 * Sends a JSON response success message.
	 *
	 * @param mixed $data The data to encode as JSON and send.
	 * @param int   $status_code HTTP status code to send with the response. Default is 200.
	 */
	public function response_success( $data = null, $status_code = 200 ) {
		status_header( $status_code );
		wp_send_json_success( $data );
	}

	/**
	 * Sends a JSON response error message.
	 *
	 * @param mixed $data The data to encode as JSON and send.
	 * @param int   $status_code HTTP status code to send with the response. Default is 400.
	 */
	public function response_error( $data = null, $status_code = 400 ) {
		status_header( $status_code );
		wp_send_json_error( $data );
	}

	/**
	 * Sends a JSON response with arbitrary data.
	 *
	 * @param mixed $data The data to encode as JSON and send.
	 * @param int   $status_code HTTP status code to send with the response. Default is 200.
	 */
	public function response( $data, $status_code = 200 ) {
		status_header( $status_code );
		wp_send_json( $data );
	}

	/**
     * Validate server data before insert/update.
     *
     * @param array $data The server data to validate.
     * @param int|null $id Server ID (if updating, to exclude current record from uniqueness check).
     *
     * @return string|null Error message if validation fails, otherwise null.
     */
    public function validate_server_data( $data, $id = null, $table, $partial = false ) {
	    global $wpdb;

	    $required_fields = [
	        'name'       => 'Server name is required.',
	        'provider'   => 'Provider name is required.',
	        'status'     => 'Status is required.',
	        'ip_address' => 'IP Address is required.',
	        'cpu_cores'  => 'CPU cores data is required.',
	        'ram_mb'     => 'RAM data is required.',
	        'storage_gb' => 'Storage data is required.',
	    ];

	    // 🔹 For create: require all fields
	    // 🔹 For update: require only the fields that are present in $data
	    foreach ( $required_fields as $field => $error_message ) {
	        if ( ! $partial && empty( $data[ $field ] ) ) {
	            return $error_message;
	        }
	        if ( $partial && array_key_exists( $field, $data ) && empty( $data[ $field ] ) ) {
	            return $error_message;
	        }
	    }

	    // ✅ Validate uniqueness for name+provider
		if ( isset( $data['name'] ) || isset( $data['provider'] ) ) {
		    // Fetch current provider/name if one is missing (on update only)
		    if ( $id ) {
		        $current = $wpdb->get_row( $wpdb->prepare(
		            "SELECT name, provider FROM $table WHERE id = %d",
		            $id
		        ), ARRAY_A );
		    }

		    $name     = isset( $data['name'] ) ? $data['name'] : ( $current['name'] ?? null );
		    $provider = isset( $data['provider'] ) ? $data['provider'] : ( $current['provider'] ?? null );

		    if ( $name && $provider ) {
		        $query = $wpdb->prepare(
		            "SELECT id FROM $table WHERE name = %s AND provider = %s" . ( $id ? " AND id != %d" : "" ),
		            $id ? [ $name, $provider, $id ] : [ $name, $provider ]
		        );
		        if ( $wpdb->get_var( $query ) ) {
		            return 'Server name "' . $name  . '" must be unique per provider "' . $provider . '".';

		        }
		    }
		}


	    // ✅ Validate IP if provided
	    if ( isset( $data['ip_address'] ) ) {
	        if ( ! filter_var( $data['ip_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
	            return 'A valid IPv4 address is required.';
	        }

	        $query = $wpdb->prepare(
	            "SELECT id FROM $table WHERE ip_address = %s" . ( $id ? " AND id != %d" : "" ),
	            $id ? [ $data['ip_address'], $id ] : [ $data['ip_address'] ]
	        );
	        if ( $wpdb->get_var( $query ) ) {
	            return 'IP address must be unique.';
	        }
	    }

	    // ✅ Validate provider if provided
	    if ( isset( $data['provider'] ) ) {
	        $valid_providers = [ 'aws', 'digitalocean', 'vultr', 'other' ];
	        if ( ! in_array( $data['provider'], $valid_providers, true ) ) {
	            return 'Invalid provider. Allowed values: aws, digitalocean, vultr, other.';
	        }
	    }

	    // ✅ Validate status if provided
	    if ( isset( $data['status'] ) ) {
	        $valid_statuses = [ 'active', 'inactive', 'maintenance' ];
	        if ( ! in_array( $data['status'], $valid_statuses, true ) ) {
	            return 'Invalid status. Allowed values: active, inactive, maintenance.';
	        }
	    }

	    // ✅ Validate resources only if provided
	    if ( isset( $data['cpu_cores'] ) && ( $data['cpu_cores'] < 1 || $data['cpu_cores'] > 128 ) ) {
	        return 'CPU cores must be between 1 and 128.';
	    }

	    if ( isset( $data['ram_mb'] ) && ( $data['ram_mb'] < 512 || $data['ram_mb'] > 1048576 ) ) {
	        return 'RAM must be between 512 MB and 1,048,576 MB.';
	    }

	    if ( isset( $data['storage_gb'] ) && ( $data['storage_gb'] < 10 || $data['storage_gb'] > 1048576 ) ) {
	        return 'Storage must be between 10 GB and 1,048,576 GB.';
	    }

	    // ✅ All good
	    return null;
	}

	/**
	 * Get user ID from Authorization header.
	 *
	 * @param WP_REST_Request $request
	 * @return int|false User ID if valid, false otherwise.
	 */
	public function authenticate_request( $request ) {
	    $auth_header = $request->get_header( 'authorization' );

	    if ( ! $auth_header || stripos( $auth_header, 'Bearer ' ) !== 0 ) {
	        return $this->response_error( [ 'Please add authorization ', 
	        	 'In Headers add authorization: Bearer $token' ] );
	    }

	    // Extract token from "Bearer <token>"
	    $token = trim( str_ireplace( 'Bearer', '', $auth_header ) );

	    if ( empty( $token ) ) {
	        return $this->response_error( 'Please add authorization ' );
	    }

	    return $this->validate_token( $token ); 
	}

	public function check_permission( $request ) {
	    $user_id = $this->authenticate_request( $request );

	    if ( ! $user_id ) {
	    	$data = [
	    		'unauthorized', 
	    		'Invalid or expired token.' 
	    	];
	    	 return $this->response_error( $request, [ 'status' => 401 ] );
	        
	    }

	    // Optionally store the authenticated user ID for later
	    $request->set_param( 'user_id', $user_id );

	    return true;
	}

}
