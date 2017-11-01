<?php

namespace MBuscher;

/**
 * Allows the application to create cache-entries on the filesystem which can be loaded really fast through the
 * operationg system/ SSD drives.
 * 
 * @see https://blog.graphiq.com/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad#.u0p11a2q3
 * 
 * @author	Markus Buscher <markus@buscher.de>
 */
class Cache
{
	private static $cache_dir = null;
	private static $default_expire = null;
	private static $active = true;


	/**
	 * Creates a entry in the cache for the given key with the given value
	 * 
	 * @param	string	$key
	 * @param	mixed	$value
	 * @param	int		$expire		Expiration time in seconds
	 * @return 	boolean
	 * @static
	 */
	public static function set($key, $value, $expire = null)
	{
		if(!static::$active)
			return 0;
		
		if($expire === null)
		{
			$expire = static::defaultExpire();
		}
		else if(!is_int($expire) || $expire <= 0)
		{
			trigger_error('The expire has to be a positive integer', E_USER_ERROR);
			return false;
		}
			
		$expire_ts = time() + $expire;

		$cache_key   = static::buildCacheKey($key);
		$cache_value = base64_encode(serialize($value));

    	return file_put_contents($cache_key, '<?php if(time()<'.$expire_ts.'){$cached_value=\''.$cache_value.'\';}else{@unlink(__FILE__);}') > 0;
	}


	/**
	 * Reads an existing cache-entry and returns it's value or false if no cache entry was hit
	 * 
	 * @param 	string	$key
	 * @return	mixed|boolean
	 * @static
	 */
	public static function get($key)
	{
		if(!static::$active)
			return false;

		$cache_key = static::buildCacheKey($key);

		@include $cache_key;

		return isset($cached_value) ? unserialize(base64_decode($cached_value)) : false;
	}


	/**
	 * Deletes a cache-entry from the filesystem
	 * 
	 * @param	string	$key
	 * @return	boolean
	 * @static
	 */
	public static function delete($key)
	{
		if(!static::$active)
			return false;

		$cache_key = static::buildCacheKey($key);

		if(file_exists($cache_key))
			return unlink($cache_key);
		else
			return false;
	}

	
	/**
	 * Enables the cache handler
	 * 
	 * @return	void
	 * @static 
	 */
	public static function enable()
	{
		static::$active = true;
	}

	
	/**
	 * Disables the cache handler
	 * 
	 * @return	void
	 * @static
	 */
	public static function disable()
	{
		static::$active = false;
	}
	
	
	/**
	 * Reads and sets the default caching expiration time
	 * 
	 * @param	int		$set_default		[optional]
	 * @return	int
	 * @static
	 */
	public static function defaultExpire($set_default = null)
	{
		if($set_default !== null && (int)$set_default > 0)
		{
			static::$default_expire = (int)$set_default;
		}
		else if($set_default !== null && (int)$set_default <= 0)
		{
			trigger_error('The expire has to be a positive integer', E_USER_ERROR);
			return false;
		}
		
		if(static::$default_expire === null)
		{
			$env = getenv('cache_default_expire');
		
			if(!$env)
			{
				trigger_error('The environment configuration cache_default_expire is not set', E_USER_ERROR);
			}
		
			static::defaultExpire($env);
		}
		
		return static::$default_expire;
	}

	
	/**
	 * Reads and sets the default caching directory
	 * 
	 * @param	string	$dir_path		Path to the directory for the cache files
	 * @return	string
	 * @static
	 */
	public static function cacheDir($dir_path = '')
	{
		if($dir_path != '')
		{
			if(!is_dir($dir_path) || !is_writable($dir_path))
			{
				trigger_error('The given path '.$dir_path.' for the cache is not a writable directory', E_USER_ERROR);
			}
				
			static::$cache_dir = $dir_path;
		}
	
		if(static::$cache_dir === null)
		{
			$env = getenv('cache_dir');
				
			if(!strlen($env))
			{
				trigger_error('The environment configuration cache_dir is not set', E_USER_ERROR);
			}
				
			static::cacheDir($env);
		}
	
		return static::$cache_dir;
	}

	
	/**
	 * Creates the internal key name by hashing the given key
	 * 
	 * @param	string	$key
	 * @return	string
	 * @static
	 */
	protected static function buildCacheKey($key)
	{
		$cache_key = md5($key);
		
		$cache_dir = static::cacheDir();
		$cache_key = $cache_dir . "/$cache_key.cached";

		return $cache_key;
	}
	
	
	/**
	 * Runs a directory clean up to delete all expired cache keys. Should be called after output buffer is flushed or via cronjob
	 * 
	 * @return	void
	 * @static
	 */
	public static function cleanUp()
	{
		$cache_dir = static::cacheDir();
		
		$files = glob($cache_dir.'/*.cached'); // get all file names
		foreach($files as $file)
		{
			@include $file;
		}
	}
}