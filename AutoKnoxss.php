<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

class AutoKnoxss
{
	private $user_agent;
	private $burp_source;
	private $url_source;
	private $max_error = 0;
	private $max_throttle = 0;
	private $min_throttle = 0;
	private $timeout = 20;
	private $verbosity = 0;
	
	private $knoxss = null;
	
	private $n_child = 0;
	private $max_child = 3;
	private $sleep = 50000;
	private $t_process = [];
	private $t_signal_queue = [];

	private $t_requests = [];

	
	public function getUserAgent() {
		return $this->user_agent;
	}
	public function setUserAgent( $v ) {
		$this->user_agent = trim( $v );
	}

	
	public function getBurpSource() {
		return $this->burp_source;
	}
	public function setBurpSource( $v ) {
		$this->burp_source = $v;
	}

	
	public function getCookies() {
		return $this->cookies;
	}
	public function setCookies( $v ) {
		$this->cookies = trim( $v );
		return true;
	}
	
	
	public function getMaxError() {
		return $this->max_error;
	}
	public function setMaxError( $v ) {
		$this->max_error = (int)$v;
		return true;
	}
	

	public function getMinThrottle() {
		return $this->min_throttle;
	}
	public function setMinThrottle( $v ) {
		$this->min_throttle = (int)$v;
		return true;
	}

	
	public function getMaxThrottle() {
		return $this->max_throttle;
	}
	public function setMaxThrottle( $v ) {
		$this->max_throttle = (int)$v;
		return true;
	}

	
	public function getMaxChild() {
		return $this->max_child;
	}
	public function setMaxChild( $v ) {
		$this->max_child = (int)$v;
		return true;
	}

	
	public function getSingleSource() {
		return $this->single_source;
	}
	public function setSingleSource( $v ) {
		$this->single_source = trim( $v );
		return true;
	}
	
	
	public function getTimeout() {
		return $this->timeout;
	}
	public function setTimeout( $v ) {
		$this->timeout = (int)$v;
		return true;
	}

	
	public function getUrlSource() {
		return $this->url_source;
	}
	public function setUrlSource( $v ) {
		$f = trim( $v );
		if( is_file($f) ) {
			$this->url_source = $f;
			return true;
		} else {
			return false;
		}
		return true;
	}

	
	public function getVerbosity() {
		return $this->verbosity;
	}
	public function setVerbosity( $v ) {
		$this->verbosity = (int)$v;
		return true;
	}

	
	// http://stackoverflow.com/questions/16238510/pcntl-fork-results-in-defunct-parent-process
	// Thousand Thanks!
	private function signal_handler( $signal, $pid=null, $status=null )
	{
		global $t_process, $t_signal_queue, $n_child;
				
		// If no pid is provided, Let's wait to figure out which child process ended
		if( !$pid ){
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		// Get all exited children
		while( $pid > 0 )
		{
			if( $pid && isset($this->t_process[$pid]) ) {
				// I don't care about exit status right now.
				//  $exitCode = pcntl_wexitstatus($status);
				//  if($exitCode != 0){
				//      echo "$pid exited with status ".$exitCode."\n";
				//  }
				// Process is finished, so remove it from the list.
				$this->n_child--;
				unset( $this->t_process[$pid] );
			}
			elseif( $pid ) {
				// Job finished before the parent process could record it as launched.
				// Store it to handle when the parent process is ready
				$this->t_signal_queue[$pid] = $status;
			}
			
			$pid = pcntl_waitpid( -1, $status, WNOHANG );
		}
		
		return true;
	}
	
	
	private function loadBurp()
	{
		$this->t_requests = BurpRequest::loadDatas( $this->burp_source );
		return count( $this->t_requests );
	}
	
	
	private function loadUrls()
	{
		$this->t_requests = UrlRequest::loadDatas( $this->url_source );
		return count( $this->t_requests );
	}

	
	private function loadSingle()
	{
		$this->t_requests = SingleRequest::loadDatas( $this->single_source );
		return count( $this->t_requests );
	}
	
	
	public function loadDatas()
	{
		if( $this->burp_source ) {
			return $this->loadBurp();
		} elseif( $this->url_source ) {
			return $this->loadUrls();
		}  elseif( $this->single_source ) {
			return $this->loadSingle();
		} else {
			return false;
		}

		echo "\nLoading ".count($this->t_requests)." requests...\n";
		
		$t_keys = [];
		foreach( $this->t_requests as $k=>$br ) {
			if( !in_array($br->key,$t_keys) ) {
				$t_keys[] = $br->key;
			} else {
				unset( $this->t_requests[$k] );
			}
		}
		sort( $this->t_requests );
	}
	
	
	public function init()
	{
		$this->knoxss = new KnoxssRequest();
		$this->knoxss->setCookies( $this->cookies );
		$this->knoxss->setUserAgent( $this->user_agent );
		//$knoxss->getCookies();
		//exit();
		$this->knoxss->getNonce();
		if( !$this->knoxss->wpnonce ) {
			Utils::help( 'WPNonce not found' );
		}
		echo "WPnonce extracted: ".$this->knoxss->getWPnonce()."\n";

		$n_request = count( $this->t_requests );
		echo 'Testing '.$n_request." request...\n\n";

		$t_splitted = [];
		for( $i=0,$d=0 ; $i<$n_request ; $i++,$d++ ) {
			$t_splitted[$d%$this->max_child][] = $this->t_requests[$i];
		}
		$this->t_requests = $t_splitted;
		//var_dump($this->t_requests);
		
		foreach( $t_splitted as $k=>$tbr ) {
			echo 'Process '.($k+1).' will treat '.count($tbr)." requests\n";
		}
		echo "\n";
	}
	
	
	public function run()
	{
		$max_child = count( $this->t_requests );
		
		for( $current_pointer=0 ; $current_pointer<$max_child ; )
		{
			if( $this->n_child < $this->max_child )
			{
				$pid = pcntl_fork();
		
				if( $pid == -1 ) {
					// fork error
				} elseif( $pid ) {
					// father
					$this->n_child++;
					$current_pointer++;
					$this->t_process[$pid] = uniqid();
			        if( isset($this->t_signal_queue[$pid]) ){
			        	$this->signal_handler( SIGCHLD, $pid, $this->t_signal_queue[$pid] );
			        	unset( $this->t_signal_queue[$pid] );
			        }
				} else {
					// child process
					$i = $n_error = 0;
					$child_id = $current_pointer + 1;
	
					foreach( $this->t_requests[$current_pointer] as $r )
					{
						$i++;
						$this->knoxss->target = $r->url;
						$this->knoxss->post = $r->post;
						
						ob_start();
						echo $child_id.'.'.$i.'. ';
						$this->knoxss->go();
						$error = $this->knoxss->result();
						$buffer = ob_get_contents();
						ob_end_clean();
						echo $buffer;
						
						if( !$error ) {
							$n_error = 0;
						} else {
							$n_error++;
						}
						if( $this->max_error > 0 && $n_error >= $this->max_error ) {
							echo "\n";
							Utils::_println( "Too many errors, contact the vendor or try later! (".$child_id.")", 'light_cyan' );
							break;
						}
						
						usleep( rand($this->min_throttle,$this->max_throttle) );
					}
					exit( 0 );
				}
			}
			
			usleep( $this->sleep );
		}
		
		echo "\n";
	}
}
