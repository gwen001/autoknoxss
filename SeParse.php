<?php

class SeParse
{
	private $query = '';

	private $start = 0;

	private $per_page = 10;

	private $max_result = 500;

	private $no_limit = false;

	private $t_result = array();

	private $delay = 2;

	private $t_user_agent = array(
		'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0 Iceweasel/31.7.0',
		'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)',
		'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
		'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A',
		'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
		'Opera/9.80 (Windows NT 6.0) Presto/2.12.388 Version/12.14',
		'Mozilla/5.0 (X11; Linux 3.5.4-1-ARCH i686; es) KHTML/4.9.1 (like Gecko) Konqueror/4.9',
		'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0',
	);

	private $t_proxy = array(
		'103.14.8.239:8080',
	);

	
	public function run()
	{
		for( $i=0 ; $i<$this->max_result ; $i+=$this->per_page )
		{
			if( $i > 0 ) {
				sleep( $this->delay );
			}

			$r = $this->seRun( $this->query, $this->start+$i, $this->per_page );

			if( $r < 0 ) {
				// captcha triggered :/
				return -1;
			} elseif( $r < $this->per_page ) {
				// looks like we find the last result page
				break;
			}
		}
	}


	private function seRun( $q, $start, $per_page )
	{
		$se_url = 'http://www.google.com/search?q='.$q.'&start='.$start;
		if( !$this->no_limit ) {
			$se_url .= '&num='.$per_page;
		}

		$se_result = $this->seCall( $se_url );

	    $r = '#<h3 class="r"><a href="([^">]*)"#';
		$m = preg_match_all( $r, $se_result, $matches );
		//var_dump( $matches );
		
		$cnt = ($m ? count($matches[0]) : 0);
		
		if ($cnt ) {
			$t_urls = [];
			foreach( $matches[1] as $u ) {
				$t_urls[] = $this->cleanUrl( $u );
			}
			$this->t_result = array_merge( $this->t_result, $t_urls );
			echo "\n".$cnt." new urls found.\n\n";
			echo implode( "\n", $t_urls )."\n\n";
		}
		
		return $cnt;
	}

	
	private function seCall( $se_url )
	{
		echo 'Calling '.$se_url."...\n";

		$c = curl_init();
		curl_setopt( $c, CURLOPT_URL, $se_url );
		curl_setopt( $c, CURLOPT_HEADER, 0 );
		curl_setopt( $c, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $c, CURLOPT_USERAGENT, $this->t_user_agent[rand(0,count($this->t_user_agent)-1)] );
		curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
		//curl_setopt( $c, CURLOPT_PROXY, $this->t_proxy[rand(0,count($this->t_proxy)-1)] );
		//curl_setopt( $c, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
		$se_result = curl_exec( $c );
		$code = curl_getinfo( $c, CURLINFO_HTTP_CODE );
		//var_dump( $se_result );
		file_put_contents( 'result.html', $se_result );
		//exit();
		curl_close( $c );
	
/*
		if( $code != 200 ) {
			//var_dump($code);
			//var_dump( $se_result );
			if( $code == 302 ) {
				$new_url = Utils::cutter( '<A HREF="', '"', $se_result, 0 );
				//var_dump( $new_url );
				return $this->seCall( $new_url );
			} else {
				//echo "Error cannot retrieve any result !\n";
				echo $se_result;
				return -1;
			}
		}
*/
		//$se_result = file_get_contents( 'result.html' );
		//var_dump( $se_result );
		//exit();

		return $se_result;
	}

	
	private function cleanUrl( $str )
	{
		$str = str_replace( '/url?q=', '', $str );
		$str = urldecode( $str );
		if( ($a=strpos($str,'&amp;')) ) {
			$str = substr( $str, 0, strpos($str,'&amp;') );
		}
		
		return $str;
	}
	
	
	public function setQuery( $v ) {
		$this->query = urlencode( $v );
	}
	public function getQuery() {
		return $this->query;
	}

	public function setStart( $v ) {
		$this->start = (int)$v;
	}
	public function getStart() {
		return $this->start;
	}

	public function setPerPage( $v ) {
		$this->per_page = (int)$v;
	}
	public function getPerPage() {
		return $this->per_page;
	}

	public function setMaxResult( $v ) {
		$this->max_result = (int)$v;
	}
	public function getMaxResult() {
		return $this->max_result;
	}

	public function disableLimit() {
		$this->no_limit = true;
	}

	public function getResult() {
		return $this->t_result;
	}
}
