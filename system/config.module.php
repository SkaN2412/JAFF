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
        $config = self::loadConfig();

        if ( ${self::getVarName( $what )} === FALSE )
        {
            $value = FALSE;
        } else {
            $value = ${self::getVarName( $what )};
        }

        return $value;
    }

    public static function edit( $what, $nValue )
    {
        $config = self::loadConfig();

        if ( ${self::getVarName( $what )} === FALSE )
        {
            return FALSE;
        } else {
            ${self::getVarName( $what )} = $nValue;
        }

        self::writeIntoFile($value);
    }

    public static function remove( $what )
    {
        $config = self::loadConfig();

        if ( ${self::getVarName( $what )} === FALSE )
        {
            return FALSE;
        } else {
            unset( ${self::getVarName( $what )} );
        }

        self::writeIntoFile($value);
    }

    private static function loadConfig()
    {
        return parse_ini_file("system".DS."config.ini", TRUE);
    }

    private static function getVarName( $what )
    {
        $tree = explode("/", $what);

        return "\$config[{$tree[0]}][{$tree[1]}]";
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