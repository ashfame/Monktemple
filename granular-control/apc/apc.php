<?php

// Check if APC is available
if ( ! function_exists( 'apc_cache_info' ) ) {
	die('APC is not installed/running');
}

// Operational flags
$delete_only_expired = true;
$clear_all_cached = false;
$preload_cache = true; // If true, will force update cache instead of removing them

$files_dir = '/data/sites/stagingclean/www/';

$stale_files = array();
$uncleared_files = array();

// Get APC Cache info
$data = apc_cache_info();

if ( empty( $data['cache_list'] ) ) {
	die('no cached files');
}

foreach ( $data['cache_list'] as $file ) {

	if ( $clear_all_cached ) {
		$stale_files[] = $file['filename'];
		continue;
	}

	if ( $delete_only_expired ) {
		// collect files whose cache has expired
		if ( ! empty( $file['mtime'] ) && ! empty( $file['filename'] ) && ( ! file_exists( $file['filename'] ) || (int) $file['mtime'] < filemtime( $file['filename'] ) ) ) {
			$stale_files[] = $file['filename'];
		}
	}
}

if ( $preload_cache ) {
	foreach ( $stale_files as $file ) {
		if ( apc_compile_file( $file ) === false )
			$uncleared_files[] = $file;
	}
} else {
	if ( ! empty( $stale_files ) ) {
		if ( apc_delete_file( $stale_files ) === false )
			$uncleared_files[] = $file;
	}
}