<?php
namespace Betopan\Utils;

trait Singleton
{
	private static $singleton = null;

	public static function getInstance()
	{
		$class = get_called_class();

	    if (self::$singleton === null) {
            self::$singleton = new $class();
	    }

	    return self::$singleton;
	}
}