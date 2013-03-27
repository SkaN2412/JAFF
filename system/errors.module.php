<?php
class inviErrors
{
    protected static $errors = array(
        'DB_AUTH_FAIL' => array(
            'num' => 10001,
            'msg' => "DB authorize failed"
        )
    );

    public static function get( $error )
    {
        return self::$errors[$error];
    }
}
