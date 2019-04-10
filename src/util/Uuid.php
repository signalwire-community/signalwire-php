<?php
namespace SignalWire\Util;

/**
 * UUID class
 *
 * The following class generates VALID RFC 4122 COMPLIANT
 * Universally Unique IDentifiers (UUID) version 3, 4 and 5.
 *
 * UUIDs generated validates using OSSP UUID Tool, and output
 * for named-based UUIDs are exactly the same. This is a pure
 * PHP implementation.
 *
 * @author Andrew Moore
 * @link http://www.php.net/manual/en/function.uniqid.php#94959
 */
class UUID
{
	/**
	 * Generate v1 UUID
	 *
	 * Version 1 UUIDs are time-based based. It can take an optional
	 * node identifier based on mac address or a unique string id.
	 *
	 * @param	string	$node
	 */
	public static function v1($node)
	{
		// nano second time (only micro second precision) since start of UTC
		$time = microtime(true) * 10000000 + 0x01b21dd213814000;
		$time = pack("H*", sprintf('%016x', $time));

		$sequence = random_bytes(2);
		$sequence[0] = chr(ord($sequence[0]) & 0x3f | 0x80);   // variant bits 10x
		$time[0] = chr(ord($time[0]) & 0x0f | 0x10);           // version bits 0001

		if (!empty($node)) {
			// non hex string identifier
			if (is_string($node) && preg_match('/[^a-f0-9]/is', $node)) {
				// base node off md5 hash for sequence
				$node = md5($node);
				// set multicast bit not IEEE 802 MAC
				$node = (hexdec(substr($node, 0, 2)) | 1) . substr($node, 2, 10);
			}
			if (is_numeric($node))
				$node = sprintf('%012x', $node);
			if (strlen($node) > 12)
				$node = substr($node, 0, 12);
		} else {
			// base node off random sequence
			$node = random_bytes(6);
			// set multicast bit not IEEE 802 MAC
			$node[0] = chr(ord($node[0]) | 1);
			$node = bin2hex($node);
		}

		return bin2hex($time[4] . $time[5] . $time[6] . $time[7]) // time low
		      . '-' . bin2hex($time[2] . $time[3])                // time med
		      . '-' . bin2hex($time[0] . $time[1])                // time hi
		      . '-' . bin2hex($sequence)                          // seq
                      . '-' . $node;                                      // node

	}

	/**
	 * Generate v3 UUID
	 *
	 * Version 3 UUIDs are named based. They require a namespace (another
	 * valid UUID) and a value (the name). Given the same namespace and
	 * name, the output is always the same.
	 *
	 * @param	uuid	$namespace
	 * @param	string	$name
	 */
	public static function v3($namespace, $name)
	{
		if(!self::is_valid($namespace)) return false;

		// Get hexadecimal components of namespace
		$nhex = str_replace(array('-','{','}'), '', $namespace);

		// Binary Value
		$nstr = '';

		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i+=2)
		{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}

		// Calculate hash value
		$hash = md5($nstr . $name);

		return sprintf('%08s-%04s-%04x-%04x-%12s',

		// 32 bits for "time_low"
		substr($hash, 0, 8),

		// 16 bits for "time_mid"
		substr($hash, 8, 4),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 3
		(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

		// 48 bits for "node"
		substr($hash, 20, 12)
		);
	}

	/**
	 *
	 * Generate v4 UUID
	 *
	 * Version 4 UUIDs are pseudo-random.
	 */
	public static function v4()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

		// 32 bits for "time_low"
		random_int(0, 0xffff), random_int(0, 0xffff),

		// 16 bits for "time_mid"
		random_int(0, 0xffff),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 4
		random_int(0, 0x0fff) | 0x4000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		random_int(0, 0x3fff) | 0x8000,

		// 48 bits for "node"
		random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff)
		);
	}

	/**
	 * Generate v5 UUID
	 *
	 * Version 5 UUIDs are named based. They require a namespace (another
	 * valid UUID) and a value (the name). Given the same namespace and
	 * name, the output is always the same.
	 *
	 * @param	uuid	$namespace
	 * @param	string	$name
	 */
	public static function v5($namespace, $name)
	{
		if(!self::is_valid($namespace)) return false;

		// Get hexadecimal components of namespace
		$nhex = str_replace(array('-','{','}'), '', $namespace);

		// Binary Value
		$nstr = '';

		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i+=2)
		{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}

		// Calculate hash value
		$hash = sha1($nstr . $name);

		return sprintf('%08s-%04s-%04x-%04x-%12s',

		// 32 bits for "time_low"
		substr($hash, 0, 8),

		// 16 bits for "time_mid"
		substr($hash, 8, 4),

		// 16 bits for "time_hi_and_version",
		// four most significant bits holds version number 5
		(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

		// 16 bits, 8 bits for "clk_seq_hi_res",
		// 8 bits for "clk_seq_low",
		// two most significant bits holds zero and one for variant DCE1.1
		(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

		// 48 bits for "node"
		substr($hash, 20, 12)
		);
	}

	public static function is_valid($uuid) {
		return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
	}
}
?>
