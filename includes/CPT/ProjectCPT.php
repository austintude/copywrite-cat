<?php

namespace CopywriteCat\CPT;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ProjectCPT {
	public const CPT = 'cwc_project';

	public function register(): void {
		register_post_type(
			self::CPT,
			[
				'label' => 'Copywriter Cat Projects',
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
