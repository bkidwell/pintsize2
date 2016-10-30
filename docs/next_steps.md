# Next Steps

Updated 2016-10-30

Plan

	* Feature parity with old app
		* record play events in database
		* voting via IRC
		* view votes, plays in web UI
	* Enhanceent
		* display track metadata (URL, license, etc.)
		* login to web UI via IRC or Twitter
		* vote via web UI

Next steps

	* Import ReactPHP Promises
	* Rewrite the WHOIS stuff to use Promises
	* Import ReactPHP MySQL library
	* Implement simple database query and display result in IRC
	* Observe the server's ping/pong stuff; implement timeout and restart if connection fails
	* Restart on connection close
	* Exponential fall-off on restarting logic
	* Implement settings screen using FatFree PHP and Bootstrap
	* Implement sending command from web server to bot to reload config or restart the bot
	* Sign in via Twitter and Facebook?
