<?php

namespace CopywriteCat\REST;

use CopywriteCat\Services\BlockUpdater;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ApprovalsController {
	public function register_routes(): void {
		register_rest_route(
			RestBootstrap::NS,
			'/slots/(?P<slotId>\\d+)/approve',
			[
				[
					'methods' => 'POST',
					'callback' => [ $this, 'approve' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
					'args' => [
						'level' => [ 'type' => 'string', 'required' => true ],
						'versionId' => [ 'type' => 'integer', 'required' => true ],
					],
				],
			],
		);
	}

	public function approve( WP_REST_Request $request ) {
		$slot_id = (int) $request['slotId'];
		$perm = Permissions::must_be_slot_member( $slot_id );
		if ( true !== $perm ) {
			return $perm;
		}
		$level = sanitize_key( (string) $request->get_param( 'level' ) );
		$version_id = (int) $request->get_param( 'versionId' );

		if ( ! in_array( $level, [ 'client', 'final' ], true ) ) {
			return new WP_Error( 'cwc_bad_request', 'level must be client or final', [ 'status' => 400 ] );
		}
		if ( ! $slot_id || ! $version_id ) {
			return new WP_Error( 'cwc_bad_request', 'slotId and versionId are required', [ 'status' => 400 ] );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'cwc_slot_versions';
		$version = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d AND slot_id = %d", $version_id, $slot_id ), ARRAY_A );
		if ( ! $version ) {
			return new WP_Error( 'cwc_not_found', 'Version not found for slot.', [ 'status' => 404 ] );
		}

		// Record approval meta on slot CPT.
		update_post_meta( $slot_id, 'approved_version_id', $version_id );
		update_post_meta( $slot_id, 'approved_text', (string) $version['draft_text'] );
		if ( 'client' === $level ) {
			update_post_meta( $slot_id, 'status', 'approved_client' );
			update_post_meta( $slot_id, 'client_approved_at', current_time( 'mysql' ) );
			update_post_meta( $slot_id, 'client_approved_by', get_current_user_id() );
		} else {
			update_post_meta( $slot_id, 'status', 'approved_final' );
			update_post_meta( $slot_id, 'final_approved_at', current_time( 'mysql' ) );
			update_post_meta( $slot_id, 'final_approved_by', get_current_user_id() );
		}

		// Push approved text into the block.
		$updated = ( new BlockUpdater() )->push_slot_approved_text_to_page( $slot_id );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		return new WP_REST_Response( [ 'ok' => true ] );
	}
}
