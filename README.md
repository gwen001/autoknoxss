# AutoKnoxss
PHP tool to test Cross Site Scripting aka XSS through KNOXSS.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php autoknoxss.php [OPTIONS] -a <user-agent> -c <cookies> -b|-s|-u <source>

Options:
	-a	your browser user-agent, yes boring...
	-b	load Burp XML file
	-c	set knoxss.me cookies
	-e	max error before exiting, default=0 (disable)
	-h	print this help
	-mi	min throttle (microseconds), default=0 (disable)
	-ma	max throttle (microseconds), default=0 (disable)
	-n	force none, useless
	-p	max process child, default=3
	-s	test a single url
	-t	request timeout, default=20
	-u	load file containing url list
	-v	verbosity level:
			0=everything (default)
			1=XSS and errors
			2=XSS only

Examples:
	php autoknoxss.php -a "Mozilla..." -c "sucuricp_..." -s http://10degres.net/index.html
	php autoknoxss.php -a "Mozilla..." -c "sucuricp_..." -b 10d.xml -t 10 -p 5
	php autoknoxss.php -a "Mozilla..." -c "sucuricp_..." -u 10d.txt -mi 10000 -ma 100000 -e 10 -v 2
```

## Required
PHP with php-curl enabled  
An account on <https://knoxss.me/> logged in  
The User-Agent of your browser  

## Achtung
Since knoxss.me use Sucuri as a firewall, the script must have the same user-agent and the ip address than the browser you are connected with.
Else the program will not be able to find the WPnonce which is required for the next request.  
<br>

## All-in-one
Automate the automation.
This script will try to get urls from a domain using wget and send them all to KNOXSS.  
Since it has been coded very quickly, there is no option, you have to change user-agent and cookies in the code.  

Usage: `php allinone.php <url> <wpnonce>`  
Example: `php allinone.php http://10degres.net c3uhce78`  
<br>
<br>

[![ScreenShot](https://raw.githubusercontent.com/gwen001/autoknoxss/master/example.jpg)](https://github.com/gwen001/autoknoxss)<br>


I don't believe in license.  
You can do want you want with this program.  
