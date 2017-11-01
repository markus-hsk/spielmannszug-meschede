<?php
namespace MBuscher;

/**
 * Simple benchmarking 
 * 
 * @example		$benchmark = new Benchmark();
 * 				// do some stuff
 * 				echo $benchmark;
 * @author		Markus buscher <blackpanther.online@gmx.de>
 */
class Benchmark
{
	protected $start = 0;
	protected $end	 = null;

	public static $default_media_type = 'html';
	public static $default_decimals   = 3;
	public static $default_dec_sep    = ',';
	public static $default_tho_sep    = '.';
	public static $default_warn_lvl   = null;
	public static $default_alarm_lvl  = null;

	
	function __construct()
	{
		$this->start();
	}

	
	function __toString()
	{
		return $this->out();
	}

	
	function start()
	{
		$this->start = static::microtime_float();
	}

	
	function stop()
	{
		$this->end = static::microtime_float();
	}

	
	function getTime()
	{
		if($this->end === null)
			$this->stop();

		return $this->end - $this->start;
	}

	
	function out(array $format = [])
	{
		$media_type = isset($format['MEDIA_TYPE'])  ? $format['MEDIA_TYPE']  : static::$default_decimals;
		$decimals  	= isset($format['DECIMALS'])    ? $format['DECIMALS']    : static::$default_decimals;
		$dec_sep   	= isset($format['DEC_SEP'])     ? $format['DEC_SEP']     : static::$default_dec_sep;
		$tho_sep   	= isset($format['THO_SEP'])     ? $format['THO_SEP']     : static::$default_tho_sep;
		$warn_lvl  	= isset($format['WARN_LVL'])    ? $format['WARN_LVL']    : static::$default_warn_lvl;
		$alarm_lvl 	= isset($format['ALARM_LVL'])   ? $format['ALARM_LVL']   : static::$default_alarm_lvl;

		$time = $this->getTime();
		
		switch($media_type)
		{
			default:
				echo number_format($time, $decimals, $dec_sep, $tho_sep).' Sec';
				
			case 'html':
				break;
		}
		
		if($alarm_lvl !== null && $time >= $alarm_lvl)
		{
			echo '<span style="color:red;">'.number_format($time, $decimals, $dec_sep, $tho_sep).' Sec</span>';
		
		}
		else if($warn_lvl !== null && $time >= $warn_lvl)
		{
			echo '<span style="color:orange;">'.number_format($time, $decimals, $dec_sep, $tho_sep).' Sec</span>';
		}
		else
		{
			echo '<span style="">'.number_format($time, $decimals, $dec_sep, $tho_sep).' Sec</span>';
		}
	}


	static function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}
}