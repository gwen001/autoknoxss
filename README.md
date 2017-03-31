# AutoKnoss
PHP tool to test Cross Site Scripting aka XSS through KNOXSS.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php autoknoxss.php [OPTIONS] -c <cookies>

Options:
	-b	load Burp XML file
	-c	set cookies
	-e	max error before exiting, default=0
	-h	print this help
	-me	min throttle, default=0
	-ma	max throttle, default=0
	-p	max process child, default=3
	-s	test a single url
	-t	timeout, default=20
	-u	load file containing url list
	-v	verbosity level, default=0

Examples:
	php ultimate-open-redirect.php -t http://www.example.com -z 10degres.net
	php ultimate-open-redirect.php -u -r -t target.txt -z 10degres.net
```

I don't believe in license.  
You can do want you want with this program.  
