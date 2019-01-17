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


	private static $active  = true;
	private static $entries = null;


	public static function set($key, $value, $expire = -1)
	{
		if(!static::$active)
			return 0;

		$cache_key   = static::buildCacheKey($key);
		$cache_value = addslashes(serialize($value));

    	$result = file_put_contents($cache_key, '<?php $cached_value = \'' . $cache_value . '\';');
    	
    	static::storeKeyInfo($cache_key, $expire);
    	
    	return $result;
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
	
	
	private static function storeKeyInfo($key, $expire)
	{
		static::getAllEntries(); // Sorgt dafür das initial die Information über alle Einträge geladen wird
		
		if(isset(static::$entries[$key]))
		{
			static::$entries[$key]['expire']  = $expire;
			static::$entries[$key]['touched'] = time();
				
		}
		else
		{
			static::$entries[$key] = ['expire' => $expire, 'touched' => time()];
		}
		
		file_put_contents(static::CACHE_DIR.'/cachestats.cached', '<?php $entries = \'' . addslashes(serialize(static::$entries)) . '\';');
	}
	
	private static function getAllEntries()
	{
		if(static::$entries === null)
		{
			@include static::CACHE_DIR.'/cachestats.cached';
				
			static::$entries = isset($entries) ? unserialize(stripslashes($entries)) : [];
		}
		
		return static::$entries;
	}
	
	public static function getStats()
	{
		if(!static::$active)
			return false;
		
		$entries = static::getAllEntries();
		
		$now     = time();
		$total   = count($entries);
		$actual  = 0;
		$expired = 0;
		foreach($entries as &$entry)
		{
			if($entry['touched'] + $entry['expire'] < $now)
				$expired++;
			else
				$actual++;
		}
		
		return ['total' => $total, 'actual' => $actual, 'expired' => $expired];
	}
}