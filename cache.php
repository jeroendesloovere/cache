<?php

/**
 * Cache
 *
 * @author Jeroen Desloovere <jeroen@siesqo.be>
 */
class Cache
{
	// cache compression
	const CACHE_COMPRESSION = false;

	// cache compression level
	const CACHE_COMPRESSION_LEVEL = 9;

	// cache debug
	const CACHE_DEBUG = false;

	// cache file extension
	const CACHE_EXTENSION = '.php';

	// cache security key
	const CACHE_SECURITY = 'D15sdf8szefs698df15sd7';

	// cache will be kept so long (in seconds)
	const CACHE_TIME = 60;

	// types
	const IS_DATA = 'data';
	const IS_OUTPUT = 'view';

	/**
	 * All settings for cache will be saved here
	 *
	 * @var array
	 */
	protected static $cache;

	/**
	 * Cancel saving output to cache
	 */
	public static function cancel()
	{
		self::$cache['output'] = false;
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
	 *
	 * @param mixed $data
	 */
	private static function convertToObject($data)
	{
		// define new class
		$obj = new stdclass();

		// data is an object
		if(is_object($data))
		{
			// loop data
			foreach($data as $key => $value)
			{
				// add key to object
				$obj->$key = self::convertToObject($value);
			}
		}

		// data is an array
		elseif(is_array($data))
		{
			// define keys
			$keys = array_keys($data);

			// we have keys
			if(count($keys)>0)
			{
				// loop keys
				foreach($keys as $key)
				{
					// add key to object
					$obj->$key = self::convertToObject($data[$key]);
				}
			}
		}

		// else set data as object
		else $obj = $data;

		// return object
		return $obj;
	}

	/**
	 * Delete cache
	 *
	 * @param string $filePath
	 */
	private static function delete($filePath)
	{
		// delete file if exists
		if(file_exists($filePath)) @unlink($filePath);
	}

	/**
	 * End reached for saving output to cache
	 */
	public static function end()
	{
		// cache is enabled
		if(self::$cache['enabled'])
		{
			// we should save output
			if(self::$cache['output'])
			{
				// get page content from memory
				$content = ob_get_contents();

				// save content to a cache file
				self::write(IS_OUTPUT, self::$cache['group'], self::$cache['id'], $content, self::$cache['time']);
			}

			// show output
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
		// cache is enabled
		if(self::$cache['enabled'])
		{
			// define cache file path
			$cacheFilePath = self::getCachePathToFile($type, $group, $id);

			// it exists and is not yet over-time
			if(file_exists($cacheFilePath) && filemtime($cacheFilePath) > time()) return true;
		}

		return false;
	}

	/**
	 * Get cache extension
	 *
	 * @return string
	 */
	private static function getCacheExtension()
	{
		// if no cache extension set
		if(!isset(self::$cache['extension']))
		{
			// define to the default cache extension
			self::setCacheExtension(CACHE_EXTENSION);	
		}

		return self::$cache['extension'];
	}

	/**
	 * Get cache path where the caches will be saved to
	 *
	 * @return string
	 */
	public static function getCachePath()
	{
		// cache path not defined
		if(self::$cache['path'] == null)
		{
			// redefine cache path to default path
			self::setCachePath($_SERVER['DOCUMENT_ROOT'] . '/cache/');	
		}

		return self::$cache['path'];
	}

	/**
	 * Get path to the cached file
	 *
	 * @param string $type
	 * @param string $group
	 * @param int $id
	 */
	private static function getCachePathToFile($type, $group, $id)
	{
		// get encrypted filename
		$id = (is_array($id)) ? implode('_', $id) : $id;
		$enc = md5(CACHE_SECURITY . $id);

		// return path to the cached file
		return self::getCachePath() . $group . '/' . "{$enc}_{$type}" . self::$cacheExtension;
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
		// cache is enabled, data-file exists and it should not be overridden
		if(self::$cache['enabled'] && !$overwrite && self::exists(IS_DATA, $group, $id))
		{
			// we return the cached data-file
			return self::unserialize(self::read(IS_DATA, $group, $id));
		}

		// otherwise return false
		return false;
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
		// define cache file path
		$cacheFilePath = self::getCachePathToFile($type, $group, $id);

		// cache already exists
		if(self::exists($type, $group, $id))
		{
			// get content from existing cache
			$content = file_get_contents($cacheFilePath);

			// uncompress if necessairy
			if(CACHE_COMPRESSION && function_exists('gzuncompress')) $content = gzuncompress($content);

			// return content
			return $content;
		}

		// delete cache
		self::delete($cacheFilePath);

		// return false
		return false;
	}

	/**
	 * Set cache extension
	 *
	 * @param string $extension
	 */
	public static function setCacheExtension($extension)
	{
		// redefine
		$extension = (string) $extension;

		// throw error when '.' not found
		if(strpos($extension, '.') === false)
		{
			throw new CacheException('The extension should contain a point.');	
		}

		// redefine cache extension
		self::$cache['extension'] = $extension;
	}

	/**
	 * Set cache path
	 *
	 * @param string $path
	 */
	public static function setCachePath($path)
	{
		// redefine cache path
		self::$cache['path'] = (string) $path;
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
		// cache is enabled
		if(self::$cache['enabled'])
		{
			// we should write data to a cache file
			self::write(IS_DATA, $group, $id, self::serialize($data), $lifetime);
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
		// define output per default as false
		self::$cache['output'] = false;

		// always override if debug is true
		if((bool) CACHE_DEBUG) $overwrite = true;

		// cache is enabled
		if(self::$cache['enabled'])
		{
			// cache exists and we should not override
			if(self::exists(IS_OUTPUT, $group, $id) && !$overwrite)
			{
				// read in cache and output it
				echo self::read(IS_OUTPUT, $group, $id);
				return false;
			}

			// cache doesn't exists or we should override it
			else
			{
				// start fetching output
				ob_start();	

				// redefine variables				
				self::$cache['group'] = $group;
				self::$cache['id'] = $id;
				self::$cache['time'] = ($lifetime) ? $lifetime : CACHE_TIME;
				self::$cache['output'] = (bool) !CACHE_DEBUG;
			}
		}

		// return true
		return true;
	}

	/**
	 * Serialize
	 *
	 * @param mixed $data
	 */
	public static function serialize($data)
	{
		// is object
		if(is_object($data))
		{
			$data = self::convertToObject($data);
		}

		// is array
		elseif(is_array($data))
		{
			// define keys from array
			$keys = array_keys($data);

			// we have keys
			if(count($keys) > 0)
			{
				// loop keys
				foreach($keys as $key)
				{
					// add key and its serialized data
					$data[$key] = self::serialize($data[$key]);
				}
			}
		}

		// return serialized data
		return serialize($data);
	}
	
	/**
	 * Unserialize
	 *
	 * @param mixed $data
	 */
	public static function unserialize($data)
	{
		// unserialize data
		$data = unserialize($data);

		// data is array
		if(is_array($data))
		{
			// define keys
			$keys = array_keys($data);

			// we have keys
			if(count($keys)>0)
			{
				// loop keys
				foreach($keys as $key)
				{
					// add key and its unserialized data
					$data[$key] = self::unserialize($data[$key]);
				}
			}
		}

		// return unserialized data
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
		if(!is_dir(self::getCachePath() . $group . '/'))
		{
			// create directory
			mkdir(self::getCachePath() . $group . '/', 0777, true);
		}

		// define filePath
		$filePath = self::getCachePathToFile($type, $group, $id);

		// define file stream
		$fh = fopen($filePath,'w');

		// set data to file
		if(CACHE_COMPRESSION && function_exists('gzcompress')) $data = gzcompress($data, CACHE_COMPRESSION_LEVEL);
		fwrite($fh, $data);

		// close file stream
		fclose($fh);

		// set filemtime
		$lifetime = ($lifetime) ? $lifetime : CACHE_TIME;
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
