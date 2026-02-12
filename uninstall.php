<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Early dev: no-op.
// Later: delete options, CPT content, and custom tables (carefully; confirm with user before destructive removal).
