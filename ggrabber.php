<?php

function __autoload( $c ) {
	require_once( getcwd().'/'.$c.'.php' );
}


function usage( $err=null ) {
	echo 'Usage: '.$_SERVER['argv'][0]." [<query>]\n";
	if( $err ) {
		echo 'Error: '.$err."!\n";
	}
	exit();
}


define( 'ERR_PARAM_ACTION', 1 );
define( 'ERR_PARAM_SITE', 2 );
define( 'ERR_PARAM_QUERY', 4 );
define( 'ERR_REQUEST', 6 );
define( 'SE_DEFAULT_PER_PAGE', 10 );


if( $_SERVER['argc'] != 2 ) {
	usage();
}

$q = $_SERVER['argv'][1];
echo "Performing search: ".$q."\n\n";

$separse = new SeParse();
$separse->setQuery( $q );
$separse->setPerPage( 100 );
$separse->setMaxResult( 1000 );
if( $separse->run() < 0 ) {
	$error |= 6;
	exit('error !');
}
$t_result = $separse->getResult();
//var_dump( $t_result );
