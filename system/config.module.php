<?php
class Config
{
	/**
	 * get returns value for link given. If value doesn't exist, will be returned FALSE
	 *
	 * @param string $what What to return. This parameter requires path to value needed like a path to file with unix-like separator. For example: database/server
	 *
	 * @return string Value
	 */
	public static function get( $what )
	{
		// Get data from config (with categories)
		$config = self::loadConfig();

		$tree = explode( "/", $what );

		if ( count( $tree ) === 1 )
		{
			$link = & $config[$tree[0]];
		} else
		{
			$link = & $config[$tree[0]][$tree[1]];
		}

		if ( $link === FALSE )
		{
			$value = FALSE;
		} else
		{
			$value = $link;
		}

		return $value;
	}

	public static function edit( $what, $nValue )
	{
		$config = self::loadConfig();

		$tree = explode( "/", $what );

		if ( count( $tree ) === 1 )
		{
			$link = & $config[$tree[0]];
		} else
		{
			$link = & $config[$tree[0]][$tree[1]];
		}

		if ( $link === FALSE )
		{
			return FALSE;
		} else
		{
			$link = $nValue;
		}

		self::writeIntoFile( $config );
	}

	public static function remove( $what )
	{
		$config = self::loadConfig();

		$tree = explode( "/", $what );

		if ( count( $tree ) === 1 )
		{
			$link = & $config[$tree[0]];
		} else
		{
			$link = & $config[$tree[0]][$tree[1]];
		}

		if ( $link === FALSE )
		{
			return FALSE;
		} else
		{
			unset( $link );
		}

		self::writeIntoFile( $config );
	}

	private static function loadConfig()
	{
		return parse_ini_file( "system" . DS . "config.ini", TRUE );
	}

	private static function writeIntoFile( $config )
	{
		$content = "";

		foreach ( $config as $k => $v )
		{
			$content .= "[{$k}]\n";
			foreach ( $k as $name => $val )
			{
				$content .= "{$name} = ";
				if ( is_numeric( $val ) )
				{
					$content .= "{$val}\n";
				} elseif ( is_string( $val ) )
				{
					$content .= "\"{$val}\"\n";
				}
			}
		}

		file_put_contents( "system/config.ini", $config );
	}
}