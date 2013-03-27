<?php
/**
 * Created by Andrey Kamozin
 * User: andrey
 * Date: 11.02.13
 * Time: 12:49
 */
class inviAPI
{
    public static function execute( $query )
    {
        // TODO: create function

        $stack = explode( ".", $query ); // "blog.article.125"

        // If system is called, execute system command
        if ( $stack[0] == "system" )
        {

        }
    }
}
?>