<?php
// Construction is needed here for avoiding uncaught exceptions TO CRASH EVERYTHING
try
{
    define( "DS", DIRECTORY_SEPARATOR );
    include_once( "system" . DS . "system.module.php" );

    // Include required modules
    JFSystem::required( "errorhandler", "errors", "exceptions", "templater", "config", "pdo" );

    // If admin panel is asked, work in it's directory. Else - root directory
    if ( isset( $_GET['admin'] ) )
    {
        JFSystem::required( "users" );

        JFUser::authorize();

        $group = JFUser::get()['group'];
        if ( $group !== "admin" )
        {
            header( "HTTP/1.1 404 Not Found" );
            include_once( "pages" . DS . "404.php" );
            exit;
        }

        $dir = "admin" . DS;
    } else {
        $dir = "";
    }

    // If there's any command given, execute it
    if ( isset( $_GET['query'] ) )
    {
        JFSystem::required( "api" );
        $query = $_GET['query'];
        JFAPI::execute( $query );
        exit;
    }

    if ( isset( $_GET['page'] ) )
    {
        $page = $_GET['page'];
    } else {
        $page = "main";
    }

    $acceptedPages = file( "system" . DS . "acceptedpages", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    if ( file_exists( $dir . "pages" . DS . $page . ".php" ) && in_array( $page, $acceptedPages ) )
    {
        // If page exists and it's in the accepted list, execute it

        include_once( $dir . "pages" . DS . $page . ".php" );
        exit;
    } else {
        // If not, generate 404

        header( "HTTP/1.1 404 Not Found" );
        include_once( $dir . "pages" . DS . "404.php" );
        exit;
    }

} catch ( Exception $e ) {
    print( "Unknown error." );
}