<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

abstract class Request
{
	const EXCLUDE_EXT = [
		'ico', 'gif', 'jpg', 'jpeg', 'png', 'svg',
		'woff2', 'woff', 'ttf',
		'js', 'css',
		'mp3', 'mp4', 'mpeg',
		'pdf', 'doc', 'txt',
		'xml', 'sql',
		'tar', 'tgz', 'tar.gz', 'gz', 'zip', 'rar',
	];
		
	public $key = '';
	
	public $url;
	public $post = '';

	
	protected function __construct() {
	}
}
