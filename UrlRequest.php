<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class UrlRequest extends Request
{
	public function __construct( $url )
	{
		parent::__construct();
		$this->url = trim( $url );
	}
	
	
	public function parseParameters()
	{
		$this->parseParametersGET();
	}
	

	public function parseParametersGET()
	{
		$this->_url = parse_url( $this->url );
		//var_dump($this->_url);

		if( isset($this->_url['query']) && $this->_url['query'] != '' ) {
			$this->_url['query'] = $this->explodeParameters( $this->_url['query'] );
		}
		//var_dump($this->_url);
	}
	
	
	private function explodeParameters( $str )
	{
		if( !strlen($str) ) {
			return false;
		}
		
		$t_params = [];
		$tmp = explode( '&', $str );
		
		foreach( $tmp as $p ) {
			$tmp2 = explode( '=', $p );
			$k = array_shift( $tmp2 );
			if( count($tmp2) < 1 ) {
				$t_params[ $k ] = '';
			} else {
				$t_params[ $k ] = implode( '=', $tmp2 );
			}
		}
		
		ksort( $t_params );
		
		return $t_params;
	}
	
	
	public function generateKey()
	{
		$str = [];
		$str[] = $this->url;
		//var_dump( $str );
		
		$this->key = base64_encode( serialize($str) );
	}
	
	
	public static function loadDatas( $source )
	{
		if( !is_file($source) ) {
			return false;
		}
		
		$t_datas = [];
		$tmp = file( $source, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES );
		
		foreach( $tmp as $u ) {
			$ur = new UrlRequest( $u );
			$ur->generateKey();
			$t_datas[] = $ur;
		}
		
		return $t_datas;
	}
}
