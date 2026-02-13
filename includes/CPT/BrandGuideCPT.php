<?php

namespace CopywriteCat\CPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class BrandGuideCPT {
	public const CPT = 'cwc_brand_guide';

	public function register(): void {
		register_post_type(
			self::CPT,
			[
				'label' => 'Copywriter Cat Brand Guides',
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'supports' => [ 'title' ],
				'capability_type' => 'post',
				'map_meta_cap' => true,
			]
		);
	}
}
