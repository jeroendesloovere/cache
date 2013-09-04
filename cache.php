<?php

/**
 * Cache
 *
 * @author Jeroen Desloovere <jeroen@siesqo.be>
 * @version 0.0.1
 */
class Cache
{
	// version
	const VERSION = '0.0.1';

	// types
	const IS_DATA = 'data';
	const IS_OUTPUT = 'view';

	/**
	 * Cache path
	 *
	 * @var string
	 */
	protected static $cachePath;

	/**
	 * Is cache enabled
	 *
	 * @var bool
	 */
	public static $enabled = true;

	// Compress: false or 0->9 value
	public static $compress = false;
	public static $compress_level = 9;

	// File extension
	public static $file_extension = '.php';

	// Time to last of currently being recorded data
	public static $lifetime = 60;
	
	// Cache output
	private static $cache_output; // Boolean
	private static $group;
	private static $id;
	private static $lifetime_output;

	/**
	 * Cancel saving output to cache
	 */
	public static function cancel()
	{
		self::$cache_output = false;
	}

	/**
	 * Clear all saved caches
	 * @todo
	 */
	private static function clear()
	{
	}

	/**
	 * Convert to object
	 */
	public static function convertToObject($data)
	{
		$obj = new stdclass();
		
		if(is_object($data))
		{
			foreach($data as $key => $value)
			{
				$obj->$key = self::convertToObject($value);
			}
		}
		elseif(is_array($data))
		{
			$keys = array_keys($data);
			if(count($keys)>0)
			{
				foreach($keys as $key)
				{
					$obj->$key = self::convertToObject($data[$key]);
				}
			}
		}
		else $obj = $data;
		return $obj;
	}

	/**
	 * Delete cache
	 *
	 * @param string $filePath
	 */
	private static function delete($filePath)
	{
		if(file_exists($filePath)) @unlink($filePath);
	}

	/**
	 * End reached for saving output to cache
	 */
	public static function end()
	{
		if(self::$enabled)
		{
			if(self::$cache_output)
			{
				$data = ob_get_contents();
				self::write(IS_OUTPUT, self::$group, self::$id, $data, self::$lifetime_output);
			}
			ob_end_flush();
			flush();
		}
	}

	/**
	 * Does cache exists?
	 *
	 * @param string $type
	 * @param string $group
	 * @param int $id
	 */
	public static function exists($type, $group, $id)
	{
		if(self::$enabled)
		{
			$filePath = self::getFilePath($type, $group, $id);
			if(file_exists($filePath) && filemtime($filePath) > time()) return true;
		}
		return false;
	}

	/**
	 * Get cache path
	 */
	public static function getCachePath()
	{
		// cache path not defined
		if(self::$cachePath == null)
		{
			// redefine cache path
			self::setCachePath($_SERVER['DOCUMENT_ROOT'] . '/cache/');	
		}

		return self::$cachePath;
	}
	
	/**
	 * Get data from cache
	 *
	 * @param string $group
	 * @param int $id
	 * @param bool[optional] $overwrite
	 */
	public static function getData($group, $id, $overwrite = false)
	{
		if(self::$enabled && !$overwrite && self::exists(IS_DATA, $group, $id))
		{
			return Data::unserialize(self::read(IS_DATA, $group, $id));
		}

		return false;
	}

	/**
	 * Get filePath
	 *
	 * @param string $type
	 * @param string $group
	 * @param int $id
	 */
	private static function getFilePath($type, $group, $id)
	{
		// Get encrypted filename
		$id = (is_array($id)) ? implode('_',$id) : $id;	
		$enc = md5(SECURITY_KEY.$id);

		// return filePath
		return self::$cachePath.$group.'/'."{$enc}_{$type}".self::$file_extension;
	}

	/**
	 * Read cache
	 *
	 * @param string $type
	 * @param string $group
	 * @param int $id
	 */
	private static function read($type, $group, $id)
	{
		$filePath = self::getFilePath($type, $group, $id);
		if(self::exists($type, $group, $id))
		{
			$data = file_get_contents($filePath);
			if(self::$compress&&function_exists('gzuncompress')) $data = gzuncompress($data);
			return $data;
		}
	
		self::delete($filePath);

		return false;
	}

	/**
	 * Set cache path
	 *
	 * @param string $path
	 */
	public static function setCachePath($path)
	{
		// redefine cache path
		self::$cachePath = (string) $path;

		// create folder if not exists
		// @todo
	}

	/**
	 * Set data in cache
	 *
	 * @param string $group
	 * @param int $id
	 * @param mixed $data
	 * @param bool[optional] $lifetime
	 */
	public static function setData($group, $id, $data, $lifetime = false)
	{
		if(self::$enabled)
		{
			self::write(IS_DATA, $group, $id, Data::serialize($data), $lifetime);
		}
	}

	/**
	 * Start saving output to cache
	 *
	 * @param string $group
	 * @param int $id
	 * @param bool[optional] $lifetime
	 * @param bool[optional] $overwrite
	 */
	public static function start($group, $id, $lifetime = false, $overwrite = false)
	{
		self::$cache_output = false;
		if((bool)DEBUG) $overwrite = true;
		if(self::$enabled)
		{
			if(!$overwrite && self::exists(IS_OUTPUT, $group, $id))
			{
				echo self::read(IS_OUTPUT, $group, $id);
				return false;
			}
			else
			{
				ob_start();					
				self::$group = $group;
				self::$id = $id;
				self::$lifetime_output = ($lifetime) ? $lifetime : self::$lifetime;
				self::$cache_output = (bool)!DEBUG;
			}
		}
		return true;
	}

	/**
	 * Serialize
	 *
	 * @param mixed $data
	 */
	public static function serialize($data)
	{
		if(is_object($data))
		{
			$data = self::convertToObject($data);
		}
		elseif(is_array($data))
		{
			$keys = array_keys($data);
			if(count($keys)>0)
			{
				foreach($keys as $key)
				{
					$data[$key] = self::serialize($data[$key]);
				}
			}
		}
		return serialize($data);
	}
	
	/**
	 * Unserialize
	 *
	 * @param mixed $data
	 */
	public static function unserialize($data)
	{
		$data = unserialize($data);
		
		if(is_array($data))
		{
			$keys = array_keys($data);
			if(count($keys)>0)
			{
				foreach($keys as $key)
				{
					$data[$key] = self::unserialize($data[$key]);
				}
			}
		}
		return $data;
	}

	/**
	 * Write
	 *
	 * @param string $type
	 * @param string $group
	 * @param int $id
	 * @param mixed $data
	 * @param mixed[optional] $lifetime
	 */
	private static function write($type, $group, $id, $data, $lifetime = false)
	{
		// directory not exists
		if(!is_dir(self::$cachePath.$group.'/'))
		{
			// create directory
			mkdir(self::$cachePath.$group.'/',0777,true);
		}

		// define filePath
		$filePath = self::getFilePath($type, $group, $id);

		// define file stream
		$fh = fopen($filePath,'w');

		// set data to file
		if(self::$compress&&function_exists('gzcompress')) $data = gzcompress($data, self::$compress_level);
		fwrite($fh,$data);

		// close file stream
		fclose($fh);

		// set filemtime
		$lifetime = ($lifetime) ? $lifetime : self::$lifetime;
		touch($filePath, time() + $lifetime);
	}
}

/**
 * Cache
 *
 * @author Jeroen Desloovere <jeroen@siesqo.be>
 */
class CacheException extends Exception
{
}
