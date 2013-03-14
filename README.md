serial
======

PHP class utilizing Direct IO to interact with a RS232 serial port.

Installation
========

To install using Composer, add `"mdlayher/serial": "dev-master"` to the `require` section of your `composer.json`.


Usage
====

Point the class to your serial RS232 device file location.  Options may be set once a connection is established.

```php
<?php
	// serial demo
	require_once __DIR__ . "/vendor/autoload.php";
	use \serial\serial as serial;

	// Open serial connection
	$serial = new serial("/dev/pts/1");

	// Set connection options
	$options = array(
		"baud" => 38400,
		"bits" => 8,
		"stop" => 1,
		"parity" => 0,
	);
	$serial->set_options($options);

	// Write data, read response (in this case, OBD-II)
	$serial->write("AT RV\r");
	printf("res: %s\n", $serial->read());
```
