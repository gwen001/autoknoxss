# AutoKnoss
PHP tool to test Cross Site Scripting aka XSS through KNOXSS.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php autoknoxss.php [OPTIONS] -c <cookies>

Options:
	-b	load Burp XML file
	-c	set cookies
	-e	max error before exiting, default=0 (disable)
	-h	print this help
	-me	min throttle (microseconds), default=0 (disable)
	-ma	max throttle (microseconds), default=0 (disable)
	-p	max process child, default=3
	-s	test a single url
	-t	timeout, default=20
	-u	load file containing url list

Examples:
	php autoknoxss.php -c "xxxxx" -s http://10degres.net
	php autoknoxss.php -c "xxxxx" -b 10d.xml -t 10 -p 5
	php autoknoxss.php -c "xxxxx" -u 10d.txt -mi 10000 -ma 100000 -e 10
```

I don't believe in license.  
You can do want you want with this program.  
