<?php

namespace CopywriteCat\REST;

use CopywriteCat\CPT\SlotCPT;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SlotsController {
	public function register_routes(): void {
		register_rest_route(
			RestBootstrap::NS,
			'/slots',
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
						'pagePostId' => [ 'type' => 'integer', 'required' => true ],
						'label' => [ 'type' => 'string', 'required' => true ],
						'slotType' => [ 'type' => 'string', 'required' => true ],
						'blockClientId' => [ 'type' => 'string', 'required' => false ],
					],
				],
			],
		);

		register_rest_route(
			RestBootstrap::NS,
			'/slots/(?P<id>\\d+)',
			[
				[
					'methods' => 'PATCH',
					'callback' => [ $this, 'update' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
				],
			],
		);
	}

	public function list( WP_REST_Request $request ) {
		$page_post_id = (int) $request->get_param( 'pagePostId' );
		$args = [
			'post_type' => SlotCPT::CPT,
			'posts_per_page' => 200,
			'post_status' => 'any',
			'meta_query' => $page_post_id ? [ [ 'key' => 'page_post_id', 'value' => $page_post_id ] ] : [],
		];
		$q = new \WP_Query( $args );

		$items = array_map(
			function ( $p ) {
				return $this->to_item( $p->ID );
			},
			$q->posts
		);

		return new WP_REST_Response( [ 'items' => $items ] );
	}

	public function create( WP_REST_Request $request ) {
		$page_post_id = (int) $request->get_param( 'pagePostId' );
		$label = sanitize_text_field( (string) $request->get_param( 'label' ) );
		$slot_type = sanitize_key( (string) $request->get_param( 'slotType' ) );
		$block_client_id = sanitize_text_field( (string) $request->get_param( 'blockClientId' ) );

		$post_id = wp_insert_post(
			[
				'post_type' => SlotCPT::CPT,
				'post_status' => 'publish',
				'post_title' => $label,
			]
		);

		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'page_post_id', $page_post_id );
		update_post_meta( $post_id, 'label', $label );
		update_post_meta( $post_id, 'slot_type', $slot_type );
		update_post_meta( $post_id, 'status', 'not_started' );
		if ( $block_client_id ) {
			update_post_meta( $post_id, 'block_client_id', $block_client_id );
		}

		return new WP_REST_Response( [ 'item' => $this->to_item( (int) $post_id ) ], 201 );
	}

	public function update( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		$post = get_post( $id );
		if ( ! $post || SlotCPT::CPT !== $post->post_type ) {
			return new WP_Error( 'cwc_not_found', 'Slot not found.', [ 'status' => 404 ] );
		}

		$allowed = [ 'label', 'slotType', 'status' ];
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = [];
		}

		if ( isset( $params['label'] ) ) {
			$label = sanitize_text_field( (string) $params['label'] );
			wp_update_post( [ 'ID' => $id, 'post_title' => $label ] );
			update_post_meta( $id, 'label', $label );
		}
		if ( isset( $params['slotType'] ) ) {
			update_post_meta( $id, 'slot_type', sanitize_key( (string) $params['slotType'] ) );
		}
		if ( isset( $params['status'] ) ) {
			update_post_meta( $id, 'status', sanitize_key( (string) $params['status'] ) );
		}

		return new WP_REST_Response( [ 'item' => $this->to_item( $id ) ] );
	}

	private function to_item( int $id ): array {
		return [
			'id' => $id,
			'label' => (string) get_post_meta( $id, 'label', true ),
			'slotType' => (string) get_post_meta( $id, 'slot_type', true ),
			'status' => (string) get_post_meta( $id, 'status', true ),
			'pagePostId' => (int) get_post_meta( $id, 'page_post_id', true ),
			'approvedText' => (string) get_post_meta( $id, 'approved_text', true ),
		];
	}
}
