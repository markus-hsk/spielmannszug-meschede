<?php
/**
 * Created by PhpStorm.
 * User: mbuscher
 * Date: 30.01.2017
 * Time: 09:34
 */

class Cache
{
	const CACHE_DIR      = RelativePath . "/temp/";
	const DEFAULT_EXPIRE = 60 * 60 * 24 * 14;


	public static function set($key, $value)
	{
		$cache_key   = static::buildCacheKey($key);
		$cache_value = addslashes(serialize($value));

    	return file_put_contents(static::CACHE_DIR . "$cache_key.cached", '<?php $cached_value = \'' . $cache_value . '\';');
	}


	public static function get($key)
	{
		$cache_key = static::buildCacheKey($key);

		@include static::CACHE_DIR . "$cache_key.cached";

		return isset($cached_value) ? unserialize(stripslashes($cached_value)) : false;
	}


	public static function delete($key)
	{
		$cache_key = static::buildCacheKey($key);

		if(file_exists(static::CACHE_DIR . "$cache_key.cached"))
			return unlink(static::CACHE_DIR . "$cache_key.cached");
		else
			return false;
	}


	/*public static function cleanUp($expire = -1)
	{
		if($expire == -1)
			$expire = static::DEFAULT_EXPIRE;

		$dir = static::CACHE_DIR;
		$min_date = time() - (int) $expire;

		$folder = dir($dir);

		while($filename = $folder->read())
		{
			if(filetype($dir.$filename) != "dir")
			{
				if($min_date > @filemtime($dir . $filename))
				{
					@unlink($dir . $filename);
				}
			}
		}

		$folder->close();
	}*/


	private static function buildCacheKey($key)
	{
		return md5($key);
	}
}