<?php

// http://stackoverflow.com/questions/16238510/pcntl-fork-results-in-defunct-parent-process
// Thousand Thanks!
function signal_handler( $signal, $pid=null, $status=null )
{
	global $f_t_process, $f_t_signal_queue, $f_n_child;
	
	// If no pid is provided, Let's wait to figure out which child process ended
	if( !$pid ){
		$pid = pcntl_waitpid( -1, $status, WNOHANG );
	}
	
	// Get all exited children
	while( $pid > 0 )
	{
		if( $pid && isset($f_t_process[$pid]) ) {
			// I don't care about exit status right now.
			//  $exitCode = pcntl_wexitstatus($status);
			//  if($exitCode != 0){
			//      echo "$pid exited with status ".$exitCode."\n";
			//  }
			// Process is finished, so remove it from the list.
			
			$f_n_child--;
			unset( $f_t_process[$pid] );
		}
		elseif( $pid ) {
			// Job finished before the parent process could record it as launched.
			// Store it to handle when the parent process is ready
			$f_t_signal_queue[$pid] = $status;
		}
		
		$pid = pcntl_waitpid( -1, $status, WNOHANG );
	}
	
	return true;
}


function extractDomain( $host )
{
	$tmp = explode( '.', $host );
	$cnt = count( $tmp );

	$domain = $tmp[$cnt-1];

	for( $i=$cnt-2 ; $i>=0 ; $i-- ) {
		$domain = $tmp[$i].'.'.$domain;
		if( strlen($tmp[$i]) > 3 ) {
			break;
		}
	}

	return $domain;
}


function background( $cmd )
{
	if( substr(php_uname(), 0, 7) == 'Windows' ) {
		pclose( popen("start /B ". $cmd, 'r') );
	}
	else {
		exec( $cmd . " > /dev/null &" );
	}
} 


function usage( $err=null ) {
  echo 'Usage: '.$_SERVER['argv'][0]." <url> <nonce>\n";
  if( $err ) {
    echo 'Error: '.$err."\n";
  }
  exit();
}

if( $_SERVER['argc'] != 3 ) {
  usage();
}


require( dirname(__FILE__).'/Utils.php' );


$f_n_child = 0;
$f_max_child = 5;
$f_sleep = 50000;
$f_t_process = [];
$f_t_signal_queue = [];

$url = trim( $_SERVER['argv'][1] );
$nonce = trim( $_SERVER['argv'][2] );

$depth = 4;
$max_try = 100;
$sleep = 1000000;
$user_agent = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0';
$cookies = 'sucuri_cloudproxy_uuid_0e44946ca=8df185e993128359375b85885d60e83a; sucuricp_tfca_6e453141ae697f9f78b18427b4c54df1=1; wordpress_test_cookie=WP+Cookie+check; wordpress_logged_in_93e97594f67a8a0ba4e55501e74ea8a6=gwen%7C1491366375%7CBE9qDxctDan32mhOPuCdPwVEsfBGrZzH3tc6nU1PcjF%7C12556167bbdc99a6f5bc4531d50bdc7c664f1ccd1d2bec4f64729343f90e672d';

$parse_url = parse_url( $url );
//var_dump( $parse_url );
$domain = extractDomain( $parse_url['host'] );
//var_dump( $domain );

$wget_output = tempnam( '/tmp', 'xss_' );
echo "Grabbing page from ".$parse_url['host']." using wget (".$wget_output.")...\n\n";
$wget = 'wget --random-wait -U "'.$user_agent.'" -r -l'.$depth.' --spider -D '.$parse_url['host'].' '.$url.'  -o '.$wget_output;
//echo $wget."\n";
background( $wget );
// killall wget

$extract = 'cat '.$wget_output.' | grep http | grep 2017 | awk \'{print $3}\' | egrep -iv "\.ico|\.gif|\.jpg|\.jpeg|\.png|\.woff2|\.woff|\.ttf|\.js|\.css|\.mp3|\.mp4|\.mpeg|\.pdf|\.doc|\.txt|\.tar|\.tgz|\.tar\.gz|\.gz|\.zip|\.rar" | egrep "http[s]?://"';
//$wget_output = 'urls.txt';
//$extract = 'cat '.$wget_output;

posix_setsid();
declare( ticks=1 );
pcntl_signal( SIGCHLD, 'signal_handler' );

echo "Waiting for new urls...\n\n";

for( $pointer=0,$n_try=0 ; 1 ; )
{
	echo "Looping...\n";
	
	// check file for new entries
	$t_urls = [];
	exec( $extract, $t_urls );
	//var_dump( $t_urls );
	
	$n_urls = count( $t_urls );
	$n_new = $n_urls - $pointer;
	echo $n_urls." urls found in total, ".$n_new." new\n";
	
	if( $n_urls == $pointer ) {
		$n_try++;
		if( $n_try >= $max_try ) {
			echo "\nLooks like there is no new url since a while, exiting!\n\n";
			break;
		}
		usleep( $sleep );
		continue;
	}
	
	$n_try = 0;
	echo "Treating the new urls (".$n_new.")\n";

	//for( ; $pointer<$n_urls ; )
	for( ; $pointer<$n_urls && $f_n_child<$f_max_child ; )
	{
		//if( $f_n_child < $f_max_child )
		{
			$pid = pcntl_fork();
	
			if( $pid == -1 ) {
				// fork error
			} elseif( $pid ) {
				// father
				$pointer++;
				$f_n_child++;
				$f_t_process[$pid] = uniqid();
		        if( isset($f_t_signal_queue[$pid]) ){
		        	signal_handler( SIGCHLD, $pid, $f_t_signal_queue[$pid] );
		        	unset( $f_t_signal_queue[$pid] );
		        }
			} else {
				// child process
				// call autoknoxss here
				echo "Call autoknoxss here for url ".$pointer."\n";
				$ak = 'php autoknoxss.php -a "'.$user_agent.'" -c "'.$cookies.'" -n "'.$nonce.'" -s "'.$t_urls[$pointer].'"';
				//echo $ak."\n";
				//sleep(3);
				exec( $ak, $output );
				print_result( $t_urls[$pointer], $output );
				exit( 0 );
			}
		}
		
		usleep( $f_sleep );
	}
	
	usleep( $sleep );
}


function print_result( $url, $output )
{
	$output = implode( "\n", $output );
	//var_dump( $output );
	echo $url."\n";
	
	if( preg_match('#safe#i',$output) ) {
		Utils::_println( "Looks safe!", 'light_green' );
	} elseif( preg_match('#XSS found#i',$output) ) {
		echo $output."\n";
		Utils::_println( "XSS found!", 'red' );
	} elseif( preg_match('#Error#i',$output) ) {
		Utils::_println( "Error!", 'yellow' );
	}
}


exit();
