<?php

namespace CopywriteCat\Services;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BlockUpdater {
	public function push_slot_approved_text_to_page( int $slot_id ) {
		$page_post_id = (int) get_post_meta( $slot_id, 'page_post_id', true );
		if ( ! $page_post_id ) {
			return new WP_Error( 'cwc_bad_slot', 'Slot does not have a page_post_id.', [ 'status' => 400 ] );
		}

		$post = get_post( $page_post_id );
		if ( ! $post ) {
			return new WP_Error( 'cwc_missing_page', 'Page/post not found for slot.', [ 'status' => 404 ] );
		}

		$approved_text = (string) get_post_meta( $slot_id, 'approved_text', true );
		$status = (string) get_post_meta( $slot_id, 'status', true );

		$blocks = parse_blocks( $post->post_content );
		$changed = false;
		$blocks = $this->walk_blocks( $blocks, $slot_id, $approved_text, $status, $changed );

		if ( ! $changed ) {
			return new WP_Error( 'cwc_block_not_found', 'Could not find matching slot block on the page.', [ 'status' => 404 ] );
		}

		$new_content = serialize_blocks( $blocks );
		wp_update_post(
			[
				'ID' => $page_post_id,
				'post_content' => $new_content,
			]
		);

		return true;
	}

	private function walk_blocks( array $blocks, int $slot_id, string $approved_text, string $status, bool &$changed ): array {
		foreach ( $blocks as &$block ) {
			if ( isset( $block['blockName'] ) && 'copywrite-cat/slot' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : [];
				if ( isset( $attrs['slotId'] ) && (int) $attrs['slotId'] === $slot_id ) {
					$block['attrs']['approvedText'] = $approved_text;
					$block['attrs']['status'] = $status;
					$changed = true;
				}
			}
			if ( ! empty( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = $this->walk_blocks( $block['innerBlocks'], $slot_id, $approved_text, $status, $changed );
			}
		}
		return $blocks;
	}
}
