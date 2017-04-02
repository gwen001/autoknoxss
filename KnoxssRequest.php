<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class KnoxssRequest
{
	const KNOXSS_URL = 'https://knoxss.me/pro';
	
	public $verbosity = 0;
	public $child_id = 0;
	public $request_id = 0;

	public $user_agent = '';
	public $timeout = 20;
	public $cookies = '';
	public $cookie_file = '';
	public $wpnonce = '';
	public $addon = 1;
	public $auth = '';
	
	public $target = '';
	public $post = '';
	
	public $result;
	public $result_code;
	
	
	public function __construct()
	{
		$this->cookie_file = tempnam( '/tmp', 'cook_' );
	}

	
	public function getChildId() {
		return $this->child_id;
	}
	public function setChildId( $v ) {
		$this->child_id = (int)$v;
		return true;
	}

	
	public function getRequestId() {
		return $this->request_id;
	}
	public function setRequestId( $v ) {
		$this->request_id = (int)$v;
		return true;
	}

	
	public function getVerbosity() {
		return $this->verbosity;
	}
	public function setVerbosity( $v ) {
		$this->verbosity = (int)$v;
		return true;
	}

	
	public function getUserAgent() {
		return $this->user_agent;
	}
	public function setUserAgent( $v ) {
		$this->user_agent = trim( $v );
	}


	public function getCookies() {
		return $this->cookies;
	}
	public function setCookies( $v ) {
		$this->cookies = trim( $v );
		return true;
	}
	
	
	public function getWPnonce() {
		return $this->wpnonce;
	}
	public function setWPnonce( $v ) {
		$this->wpnonce = trim( $v );
		return true;
	}
	
	
	public function _urlencode( $str )
	{
		return str_replace( '&', '%26', $str );
	}

	
	public function go()
	{
		$this->_println( 'Testing: '.$this->target, 0 );
		$this->target = str_replace( '&', '%26', $this->target );
		$post = 'target='.$this->_urlencode($this->target).'&_wpnonce='.$this->wpnonce.'&addon='.$this->addon.'&auth='.$this->auth;
		if( strlen($this->post) ) {
			$post .= '&post='.$this->_urlencode($this->post);
		}
		$this->_println( 'With post: '.$post, 0 );
		
		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, self::KNOXSS_URL );
		curl_setopt( $c, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt( $c, CURLOPT_CUSTOMREQUEST, 'POST' );
		curl_setopt( $c, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $c, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $c, CURLOPT_COOKIE, $this->cookies );
		curl_setopt( $c, CURLOPT_POST, true );
		curl_setopt( $c, CURLOPT_POSTFIELDS, $post );
		$this->result = curl_exec( $c );
		//var_dump($this->result);
		$this->result_code = curl_getinfo( $c, CURLINFO_HTTP_CODE );;
		
		return $this->result_code;
	}
	
	/*
	public function getCookies()
	{
		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, self::KNOXSS_URL );
		curl_setopt( $c, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt( $c, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt( $c, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $c, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
		//curl_setopt( $c, CURLOPT_COOKIE, $this->cookies );
		curl_setopt( $c, CURLOPT_COOKIEJAR, $this->cookie_file );
		curl_setopt( $c, CURLOPT_COOKIEFILE, $this->cookie_file );
		$result = curl_exec( $c );
		//var_dump($result);
		
		$m = preg_match( '#<input type="hidden" name="_wpnonce" value="(.*)">#', $result, $matches );
		//var_dump( $matches );
		if( !$m ) {
			return false;
		} else {
			return $matches[1];
		}
	}
	*/
	
	public function getNonce()
	{
		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, self::KNOXSS_URL );
		curl_setopt( $c, CURLOPT_TIMEOUT, $this->timeout );
		curl_setopt( $c, CURLOPT_CUSTOMREQUEST, 'GET' );
		curl_setopt( $c, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $c, CURLOPT_USERAGENT, $this->user_agent );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $c, CURLOPT_COOKIE, $this->cookies );
		//curl_setopt( $c, CURLOPT_COOKIEJAR, $this->cookie_file );
		//curl_setopt( $c, CURLOPT_COOKIEFILE, $this->cookie_file );
		$result = curl_exec( $c );
		//var_dump( $result );

		$m = preg_match( '#<input type="hidden" name="_wpnonce" value="(.*)">#', $result, $matches );
		
		//var_dump( $matches );
		if( !$m ) {
			$this->wpnonce = false;
		} else {
			$this->wpnonce = $matches[1];
		}

		return $this->wpnonce;
	}
	
	
	public function result()
	{
		//$this->result = file_get_contents( dirname(__FILE__).'/r' );
		//var_dump( $this->result );
		
		if( $this->result_code != 200 ) {
			$this->_println( "Error contacting KNOXSS! (".$this->result_code.")", 1, 'yellow' );
			return 1;
		}
		
		$r = preg_match( "#<script>window.open\('(.*)', '', 'top=380, left=870, width=400, height=250'\);</script>#i", $this->result, $matches );
		//var_dump( $matches );
		if( $r ) {
			$this->_print( 'XSS found: ', 2, 'red' );
			$this->_println( $matches[1] );
			return 0;
		}

		$r = preg_match( "#No XSS found by KNOXSS#i", $this->result );
		if( $r ) {
			$this->_println( 'Looks safe.', 0, 'green' );
			return 0;
		}

		$r = preg_match( "#network issues#i", $this->result );
		if( $r ) {
			$this->_println( 'Cannot contact target!', 1, 'orange' );
			return 1;
		}
		
		$this->_println( 'Cannot interpret result!', 1, 'light_purple' );
		return 1;
	}

	
	private function _print( $txt, $lvl, $color='white' )
	{
		if( $lvl >= $this->verbosity ) {
			$txt = '['.$this->child_id.'.'.$this->request_id.'] ' . $txt;
			Utils::_print( $txt, $color );
		}
	}
	private function _println( $txt, $lvl, $color='white' )
	{
		$this->_print( $txt, $lvl, $color );
		if( $lvl >= $this->verbosity ) {
			echo "\n";
		}
	}
}
