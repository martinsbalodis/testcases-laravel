<?php
return [
	'selenium' => [
		'run_locally' => false, // start a selenium process while testing
		'host' => 'localhost',
		'port' => '4444',
	],
	'webserver' => [
		'host' => 'localhost',
		'port' => '4443',
		'env' => [
			'QUEUE_DRIVER' => 'database',
			'CACHE_DRIVER' => 'file',
			'SESSION_DRIVER' => 'file',
		]
	],
	/**
	 * creates an ssh connection to selenium server for port forwarding.
	 * Selenium server will be able to access the local web server via the
	 * forwarded port
	 */
	'port_forward' => [
		'enable' => true,
		'host' => 'localhost',
		'port' => '2222',
		'username' => 'root',
		'password' => 'password',
		'webserver_host' => 'localhost',
		'webserver_port' => '4443',
	],
];