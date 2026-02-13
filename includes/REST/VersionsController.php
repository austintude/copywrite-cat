<?php

namespace CopywriteCat\REST;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class VersionsController {
	public function register_routes(): void {
		register_rest_route(
			RestBootstrap::NS,
			'/slots/(?P<slotId>\\d+)/versions',
			[
				[
					'methods' => 'GET',
					'callback' => [ $this, 'list' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
				],
				[
					'methods' => 'POST',
					'callback' => [ $this, 'create' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
					'args' => [
						'draftText' => [ 'type' => 'string', 'required' => true ],
						'source' => [ 'type' => 'string', 'required' => false ],
					],
				],
			],
		);
	}

	public function list( WP_REST_Request $request ) {
		global $wpdb;
		$slot_id = (int) $request['slotId'];
		$table = $wpdb->prefix . 'cwc_slot_versions';
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE slot_id = %d ORDER BY id DESC LIMIT 100", $slot_id ), ARRAY_A );
		return new WP_REST_Response( [ 'items' => array_map( [ $this, 'to_item' ], $rows ) ] );
	}

	public function create( WP_REST_Request $request ) {
		global $wpdb;
		$slot_id = (int) $request['slotId'];
		$text = (string) $request->get_param( 'draftText' );
		$source = sanitize_key( (string) ( $request->get_param( 'source' ) ?: 'manual' ) );

		if ( ! $slot_id || '' === trim( $text ) ) {
			return new WP_Error( 'cwc_bad_request', 'slotId and draftText are required.', [ 'status' => 400 ] );
		}

		$table = $wpdb->prefix . 'cwc_slot_versions';
		$wpdb->insert(
			$table,
			[
				'slot_id' => $slot_id,
				'created_at' => current_time( 'mysql' ),
				'created_by' => get_current_user_id(),
				'source' => $source,
				'draft_text' => wp_kses_post( $text ),
			],
			[ '%d', '%s', '%d', '%s', '%s' ]
		);

		$id = (int) $wpdb->insert_id;
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
		return new WP_REST_Response( [ 'item' => $this->to_item( $row ) ], 201 );
	}

	private function to_item( array $row ): array {
		return [
			'id' => (int) $row['id'],
			'slotId' => (int) $row['slot_id'],
			'createdAt' => $row['created_at'],
			'createdBy' => (int) $row['created_by'],
			'source' => $row['source'],
			'draftText' => $row['draft_text'],
		];
	}
}
