<?php
	// serial.php - Matt Layher, 3/13/13
	// PHP class utilizing Direct IO to interact with a RS232 serial port
	//
	// changelog
	//
	// 3/14/13 MDL:
	//	- bugfixes and optimizations
	// 3/13/13 MDL:
	//	- initial commit

	namespace serial;

	class serial
	{
		// CONSTANTS - - - - - - - - - - - - - - - - - - - -

		// Default read/write length
		const DEFAULT_LENGTH = 1024;

		// Default wait time after write (in microseconds)
		const DEFAULT_WAIT = 200000;

		// Direct IO attribute defaults
		const DEFAULT_BAUD = 9600;
		const DEFAULT_BITS = 8;
		const DEFAULT_STOP = 1;
		const DEFAULT_PARITY = 0;

		// STATIC VARIABLES - - - - - - - - - - - - - - - -

		// Verbosity
		private static $verbose = 0;

		// Valid Direct IO (DIO) options
		private static $OPTIONS = array(
			"baud" => array(50, 75, 110, 134, 150, 200, 300, 600, 1200, 1800, 2400, 4800, 9600, 19200, 38400),
			"bits" => array(5, 6, 7, 8),
			"stop" => array(1, 2),
			"parity" => array(0, 1, 2),
		);

		// INSTANCE VARIABLES - - - - - - - - - - - - - - -

		// Instance variables
		private $device;
		private $options;

		// Serial connection
		private $serial;

		// PUBLIC PROPERTIES - - - - - - - - - - - - - - - -

		// device:
		//  - get: device
		//	- set: device (validated by file_exists(), created if not present)
		public function get_device()
		{
			return $this->device;
		}
		public function set_device($device)
		{
			if (file_exists($device))
			{
				$this->device = $device;
				return true;
			}

			return false;
		}

		// options:
		//  - get: options array
		//	- set: options array (validated by is_array() and keys checked)
		public function get_options()
		{
			return $this->options;
		}
		public function set_options($options)
		{
			if (is_array($options))
			{
				// Check for valid DIO attribute options
				foreach ($options as $key => $value)
				{
					// Validate option name
					if (!array_key_exists($key, self::$OPTIONS))
					{
						trigger_error("Invalid PHP Direct IO option specified '" . $key . "'", E_USER_WARNING);
						return false;
					}

					// Validate option value
					if (!in_array($value, self::$OPTIONS[$key]))
					{
						trigger_error("Invalid PHP Direct IO value specified for " . $key . " '" . $value . "'", E_USER_WARNING);
						return false;
					}
				}

				// If all checks pass, set options
				$this->options = $options;
				dio_tcsetattr($this->serial, $options);
				return true;
			}

			return false;
		}

		// CONSTRUCTOR/DESTRUCTOR - - - - - - - - - - - - -

		// Construct serial object using specified device with specified flags (02 = O_RDWR)
		public function __construct($device, $flags = 02)
		{
			// Attempt to set device...
			if (!$this->set_device($device))
			{
				throw new \Exception("Unable to set device for serial connection");
			}

			// Check if Direct IO extension installed
			if (!function_exists("dio_open"))
			{
				throw new \Exception("PHP Direct IO is not installed, cannot open serial connection!");
			}

			// Create direct IO file handle with specified flags
			$this->serial = dio_open($device, $flags);

			// Set synchronous IO
			dio_fcntl($this->serial, F_SETFL, O_SYNC);

			// Set options default
			$options = array(
				"baud" => self::DEFAULT_BAUD,
				"bits" => self::DEFAULT_BITS,
				"stop" => self::DEFAULT_STOP,
				"parity" => self::DEFAULT_PARITY,
			);
			$this->set_options($options);

			return;
		}

		// Close connection on destruct
		public function __destruct()
		{
			if (isset($this->serial))
			{
				$this->close();
			}
			return;
		}

		// PUBLIC METHODS - - - - - - - - - - - - - - - - -

		// Close connection to serial port
		public function close()
		{
			if (isset($this->serial))
			{
				dio_close($this->serial);
				unset($this->serial);
			}
			return true;
		}

		// Read data from serial port
		public function read($length = self::DEFAULT_LENGTH)
		{
			$bytes = dio_read($this->serial, $length);
			return $bytes;
		}

		// Write data to serial port
		public function write($data, $length = self::DEFAULT_LENGTH, $wait = self::DEFAULT_WAIT)
		{
			$bytes = dio_write($this->serial, $data);
			usleep($wait);
			return $bytes;
		}
	}
