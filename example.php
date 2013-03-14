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
