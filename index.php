<?php
// Construction is needed here for avoiding uncaught exceptions TO CRASH EVERYTHING
try
{
    define( "DS", DIRECTORY_SEPARATOR );
    include_once( "system".DS."invicms.module.php" );

    // Include required modules
    System::required( "errorhandler", "errors", "exceptions", "templater", "config", "api" );

    // If admin panel is asked, work in it's directory. Else - root directory
    if ( isset( $_GET['admin'] ) ) // TODO: adminka
    {
        $dir = "admin" . DS;
    } else {
        $dir = "";
    }

    // If there's any command given, execute it
    if ( isset( $_GET['query'] ) )
    {
        $query = $_GET['query'];
        inviAPI::execute( $query );
        exit;
    }

    if( isset( $_GET['page'] ) ) {
        $page = $_GET['page'];
    } else {
        $page = "main";
    }

    $acceptedPages = explode( "\n", file_get_contents( "system".DS."acceptedpages" ) );
    if ( file_exists( $dir."pages".DS.$page.".php" ) && in_array( $page, $acceptedPages ) )
    {
        // If page exists and it's in the accepted list, execute it

        include_once( $dir."pages".DS.$page.".php" );
        exit;
    } else {
        // If not, generate 404

        header( "HTTP/1.1 404 Not Found" );
        exit;
    }

}catch(Exception $e){
    print( "Unknown error." );
}