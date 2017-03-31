<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class BurpRequest extends Request
{
	public $time;
	public $_url;
	public $host;
	public $host_ip;
	public $protocol;
	public $method;
	public $path;
	public $extension;
	public $request;
	public $status;
	public $responseLength;
	public $mimetype;
	public $response;
	public $post;
	public $_post;
	public $comment;
	
	
	public function __construct( $item )
	{
		parent::__construct();

		$this->time = (string)$item->time;
		$this->url = (string)$item->url;
		$this->host = (string)$item->host;
		$this->host_ip = (string)$item->host->attributes()[0];
		$this->port = (int)$item->port;
		$this->protocol = (string)$item->protocol;
		$this->method = (string)$item->method;
		$this->path = (string)$item->path;
		$this->extension = (string)$item->extension;
		$this->request = (string)$item->request;
		$this->request_base64 = (bool)$item->request->attributes()[0];
		$this->status = (int)$item->status;
		$this->responseLength = (int)$item->responseLength;
		$this->mimetype = (string)$item->mimetype;
		$this->response = (string)$item->response;
		$this->response_base64 = (bool)$item->response->attributes()[0];
		$this->comment = (string)$item->comment;
	}
	
	
	public function parseParameters()
	{
		$this->parseParametersGET();
		
		if( $this->method == 'POST' ) {
			$this->parseParametersPOST();
		} else {
			$this->_post = [];
		}
	}
	

	public function parseParametersPOST()
	{
		$r = $this->request;
		
		if( $this->request_base64 )  {
			$r = trim( base64_decode($r) );
		}
		
		$r = str_replace( "\r", '', $r );
		$tmp = explode( "\n\n", $r );
		
		if( count($tmp) ==2 ) {
			$this->_post = $this->explodeParameters( $tmp[1] );
		} else {
			$this->_post = [];
		}
		
		//var_dump( $this->_post );
		foreach( $this->_post as $k=>$v ) {
			$this->post .= $k.'='.$v.'&';
		}
		$this->post = trim( $this->post, '&' );
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
		$str[] = $this->method;
		$str[] = $this->_url;
		$str[] = $this->_post;
		//var_dump( $str );
		
		$this->key = base64_encode( serialize($str) );
	}
	
	
	public static function loadDatas( $source )
	{
		$xml = simplexml_load_file( $source );
		if( $xml === false ) {
			Utils::help( 'File source is not XML' );
		}
		echo 'XML loaded: '.$source."\n";
		
		$t_datas = [];
		
		foreach( $xml->item as $item )
		{
			if( (int)$item->status < 200 || (int)$item->status > 299 ) {
				continue;
			}
			
			$br = new BurpRequest( $item );
			
			if( in_array($br->extension,Request::EXCLUDE_EXT) ) {
				continue;
			}
			
			$br->parseParameters();
			$br->generateKey();
			$t_datas[] = $br;
			//var_dump($br);
			//exit();
		}
		
		return $t_datas;
	}
}
