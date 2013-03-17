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

        for ( $index=1; $index<count($tree); $index++ )
        {
            if ( ! isset( $value[$tree[$index]] ) )
            {
                return FALSE;
            } else {
                $value = $value[$tree[$index]];
            }
        }
        return $value;
    }

    public static function edit( $what, $nValue )
    {
        // TODO: Write config
    }
}