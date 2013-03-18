<?php
class Config {
    /**
    * get returns value for link given. If value doesn't exist, will be returned FALSE
    * 
    * @param string $what What to return. This parameter requires path to value needed like a path to file with unix-like separator. For example: database/server
    * @return string Value
    */
    public static function get($what)
    {
        // Get data from config (with categories)
        $config = parse_ini_file("system".DS."config.ini", TRUE);

        $tree = explode("/", $what);

        $value = $config[$tree[0]];

        for ( $i=1; $i<count($tree); $i++ )
        {
            if ( ! isset( $value[$tree[$i]] ) )
            {
                $value =  FALSE;
            } else {
                $value = $value[$tree[$i]];
            }
        }
        return $value;
    }

    public static function edit( $what, $nValue )
    {
        $config = parse_ini_file("system".DS."config.ini", TRUE);

        $tree = explode("/", $what);

        $value = $config[$tree[0]];

        for ( $i=1; $i<count($tree); $i++ )
        {
            if ( ! isset( $value[$tree[$i]] ) )
            {
                return  FALSE;
            } else {
                $value[$tree[$i]] = $nValue;
            }
        }

        self::writeIntoFile($value);
    }

    public static function remove( $what )
    {
        $config = parse_ini_file("system".DS."config.ini", TRUE);

        $tree = explode("/", $what);

        $value = $config[$tree[0]];

        for ( $i=1; $i<count($tree); $i++ )
        {
            if ( ! isset( $value[$tree[$i]] ) )
            {
                return FALSE;
            } else {
                unset( $value[$tree[$i]] );
            }
        }

        self::writeIntoFile($value);
    }

    private static function writeIntoFile($config)
    {
        $content = "";

        foreach ($config as $k => $v)
        {
            $content .= "[{$k}]\n";
            foreach ($k as $name => $val)
            {
                $content .= "{$name} = ";
                if ( is_numeric($val) )
                {
                    $content .= "{$val}\n";
                } elseif ( is_string($val) ) {
                    $content .= "\"{$val}\"\n";
                }
            }
        }

        file_put_contents( "system/config.ini", $config );
    }
}