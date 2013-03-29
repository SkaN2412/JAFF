<?php
class inviAPI
{
    public static function execute( $query )
    {
        // Prepare query
        $stack = explode( ".", $query );

        // Get accepted AJAX files
        $acceptedAjax = explode( "\n", file_get_contents( "system".DS."acceptedajax" ) );

        // If file's accepted and exists, include it
        if ( in_array($stack[0], $acceptedAjax) && file_exists( "ajax".DS.$stack[0].".php" ) )
        {
            include( "ajax".DS.$stack[0].".php" );
        }

        // Execute function
        $result = $stack[1]::$stack[2]();

        // Convert returned array to JSON format and return it to script
        print(json_encode($result));
    }
}