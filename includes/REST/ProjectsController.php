<?php

namespace CopywriteCat\REST;

use CopywriteCat\CPT\ProjectCPT;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ProjectsController {
	public function register_routes(): void {
		register_rest_route(
			RestBootstrap::NS,
			'/projects',
			[
				[
					'methods' => 'GET',
					'callback' => [ $this, 'list' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
				],
				[
					'methods' => 'POST',
					'callback' => [ $this, 'create' ],
					'permission_callback' => [ Permissions::class, 'must_be_designer' ],
					'args' => [
						'title' => [ 'type' => 'string', 'required' => true ],
						'clientUserIds' => [ 'type' => 'array', 'required' => false ],
						'designerUserIds' => [ 'type' => 'array', 'required' => false ],
					],
				],
			],
		);

		register_rest_route(
			RestBootstrap::NS,
			'/projects/(?P<id>\\d+)',
			[
				[
					'methods' => 'GET',
					'callback' => [ $this, 'get' ],
					'permission_callback' => [ Permissions::class, 'must_be_logged_in' ],
				],
				[
					'methods' => 'PATCH',
					'callback' => [ $this, 'update' ],
					'permission_callback' => [ Permissions::class, 'must_be_designer' ],
				],
			],
		);
	}

	public function list( WP_REST_Request $request ) {
		$user_id = get_current_user_id();
		$args = [
			'post_type' => ProjectCPT::CPT,
			'posts_per_page' => 200,
			'post_status' => 'any',
		];
		$q = new \WP_Query( $args );
		$items = [];
		foreach ( $q->posts as $p ) {
			if ( Permissions::is_project_member( $p->ID, $user_id ) ) {
				$items[] = $this->to_item( $p->ID );
			}
		}
		return new WP_REST_Response( [ 'items' => $items ] );
	}

	public function get( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		if ( ! Permissions::is_project_member( $id, get_current_user_id() ) ) {
			return new WP_Error( 'cwc_forbidden', 'Not a project member.', [ 'status' => 403 ] );
		}
		return new WP_REST_Response( [ 'item' => $this->to_item( $id ) ] );
	}

	public function create( WP_REST_Request $request ) {
		$title = sanitize_text_field( (string) $request->get_param( 'title' ) );
		$client_ids = $request->get_param( 'clientUserIds' );
		$designer_ids = $request->get_param( 'designerUserIds' );
		if ( ! is_array( $client_ids ) ) {
			$client_ids = [];
		}
		if ( ! is_array( $designer_ids ) ) {
			$designer_ids = [];
		}
		$designer_ids[] = get_current_user_id();
		$designer_ids = array_values( array_unique( array_map( 'intval', $designer_ids ) ) );
		$client_ids = array_values( array_unique( array_map( 'intval', $client_ids ) ) );

		$post_id = wp_insert_post(
			[
				'post_type' => ProjectCPT::CPT,
				'post_status' => 'publish',
				'post_title' => $title,
			]
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		update_post_meta( $post_id, 'client_user_ids', $client_ids );
		update_post_meta( $post_id, 'designer_user_ids', $designer_ids );
		update_post_meta( $post_id, 'settings_json', wp_json_encode( [ 'requireDesignerFinalApproval' => false ] ) );

		return new WP_REST_Response( [ 'item' => $this->to_item( (int) $post_id ) ], 201 );
	}

	public function update( WP_REST_Request $request ) {
		$id = (int) $request['id'];
		$params = $request->get_json_params();
		if ( ! is_array( $params ) ) {
			$params = [];
		}
		if ( isset( $params['title'] ) ) {
			$title = sanitize_text_field( (string) $params['title'] );
			wp_update_post( [ 'ID' => $id, 'post_title' => $title ] );
		}
		if ( isset( $params['clientUserIds'] ) && is_array( $params['clientUserIds'] ) ) {
			update_post_meta( $id, 'client_user_ids', array_values( array_unique( array_map( 'intval', $params['clientUserIds'] ) ) ) );
		}
		if ( isset( $params['designerUserIds'] ) && is_array( $params['designerUserIds'] ) ) {
			update_post_meta( $id, 'designer_user_ids', array_values( array_unique( array_map( 'intval', $params['designerUserIds'] ) ) ) );
		}

		return new WP_REST_Response( [ 'item' => $this->to_item( $id ) ] );
	}

	private function to_item( int $id ): array {
		return [
			'id' => $id,
			'title' => get_the_title( $id ),
			'clientUserIds' => (array) get_post_meta( $id, 'client_user_ids', true ),
			'designerUserIds' => (array) get_post_meta( $id, 'designer_user_ids', true ),
		];
	}
}
