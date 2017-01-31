<?php
/**
 * @see https://blog.graphiq.com/500x-faster-caching-than-redis-memcache-apc-in-php-hhvm-dcd26e8447ad#.u0p11a2q3
 *
 *
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 30.01.2017
 * Time: 09:34
 */

class Cache
{
	const CACHE_DIR      = RelativePath . "/temp/cached/";
	const DEFAULT_EXPIRE = 60 * 60 * 24 * 14;


	private static $active = true;


	public static function set($key, $value)
	{
		if(!static::$active)
			return 0;

		$cache_key   = static::buildCacheKey($key);
		$cache_value = addslashes(serialize($value));

    	return file_put_contents($cache_key, '<?php $cached_value = \'' . $cache_value . '\';');
	}


	public static function get($key)
	{
		if(!static::$active)
			return false;

		$cache_key = static::buildCacheKey($key);

		@include $cache_key;

		return isset($cached_value) ? unserialize(stripslashes($cached_value)) : false;
	}


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

	public static function enable()
	{
		static::$active = true;
	}

	public static function disable()
	{
		static::$active = false;
	}


	private static function buildCacheKey($key)
	{
		$cache_key = md5($key);

		$cache_dir = static::CACHE_DIR.substr($cache_key, 0, 1);
		makeDirs($cache_dir);

		$cache_key = $cache_dir . "/$cache_key.cached";

		return $cache_key;
	}
}