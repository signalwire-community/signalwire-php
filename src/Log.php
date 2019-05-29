<?php
namespace SignalWire;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Log {
  protected static $instance;

	/**
	 * Method to return the Monolog instance
	 *
	 * @return \Monolog\Logger
	 */
	static public function getLogger()
	{
		if (! self::$instance) {
			self::configureInstance();
		}

		return self::$instance;
  }

  /**
	 * Configure Monolog.
	 *
	 * @return Logger
	 */
	protected static function configureInstance()
	{
		$output = "[%datetime%] %channel%.%level_name%: %message% \n";
		$formatter = new LineFormatter($output);

		$level = isset($_ENV['DEBUG']) ? Logger::DEBUG : Logger::INFO;
		$streamHandler = new StreamHandler('php://stdout', $level);
		$streamHandler->setFormatter($formatter);

		$logger = new Logger('SignalWire');
		$logger->pushHandler($streamHandler);
		self::$instance = $logger;
	}

	public static function debug($message, array $context = []){
		self::getLogger()->debug($message, $context);
	}

	public static function info($message, array $context = []){
		self::getLogger()->info($message, $context);
	}

	public static function notice($message, array $context = []){
		self::getLogger()->notice($message, $context);
	}

	public static function warning($message, array $context = []){
		self::getLogger()->warning($message, $context);
	}

	public static function error($message, array $context = []){
		self::getLogger()->error($message, $context);
	}

	// public static function critical($message, array $context = []){
	// 	self::getLogger()->Critical($message, $context);
	// }

	// public static function alert($message, array $context = []){
	// 	self::getLogger()->Alert($message, $context);
	// }

	// public static function emergency($message, array $context = []){
	// 	self::getLogger()->Emergency($message, $context);
	// }
}
