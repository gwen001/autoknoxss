<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

abstract class Request
{
	const EXCLUDE_EXT = [
		'gif', 'jpg', 'jpeg', 'png',
		'woff2', 'woff', 'ttf',
		'js', 'css',
		'mp3', 'mp4', 'mpeg',
		'pdf', 'doc', 'txt',
		'tar', 'tgz', 'tar.gz', 'gz', 'zip', 'rar',
	];
		
	public $key = '';
	
	public $url;
	public $post = '';

	
	protected function __construct() {
	}
}
