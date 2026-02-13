<?php

namespace CopywriteCat\REST;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class RestBootstrap {
	public const NS = 'copywrite-cat/v1';

	public function register(): void {
		( new SlotsController() )->register_routes();
		( new VersionsController() )->register_routes();
		( new CommentsController() )->register_routes();
		( new ApprovalsController() )->register_routes();
	}
}
