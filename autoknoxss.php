#!/usr/bin/php
<?php

/**
 * I don't believe in license
 * You can do want you want with this program
 * - gwen -
 */

function __autoload( $c ) {
	include( $c.'.php' );
}


// parse command line
{
	$autoknoxss = new AutoKnoxss();

	$argc = $_SERVER['argc'] - 1;

	for ($i = 1; $i <= $argc; $i++) {
		switch ($_SERVER['argv'][$i]) {
			case '-a':
				$autoknoxss->setUserAgent($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-b':
				$autoknoxss->setBurpSource($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-c':
				$autoknoxss->setCookies($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-e':
				$autoknoxss->setMaxError($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-h':
			case '--help':
				Utils::help();
				break;

			case '-mi':
				$autoknoxss->setMinThrottle($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-ma':
				$autoknoxss->setMaxThrottle($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-n':
				$autoknoxss->setWPnonce($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-p':
				$autoknoxss->setMaxChild($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-s':
				$autoknoxss->setSingleSource($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-t':
				$autoknoxss->setTimeout($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-u':
				$autoknoxss->setUrlSource($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			case '-v':
				$autoknoxss->setVerbosity($_SERVER['argv'][$i + 1]);
				$i++;
				break;

			default:
				Utils::help('Unknown option: '.$_SERVER['argv'][$i]);
		}
	}

	if( !$autoknoxss->getBurpSource() && !$autoknoxss->getUrlSource() && !$autoknoxss->getSingleSource() ) {
		Utils::help( 'Source/Url not found, nothing to test' );
	}
	if( !$autoknoxss->getCookies() ) {
		Utils::help( 'Cookies not found' );
	}
	if( !$autoknoxss->getUserAgent() ) {
		Utils::help( 'User-Agent not found' );
	}
}
// ---


// main loop
{
	$autoknoxss->loadDatas();
	$autoknoxss->init();
	$autoknoxss->run();
}
// ---


exit();
