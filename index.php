<?php
// Construction is needed here for avoiding uncaught exceptions TO CRASH EVERYTHING
try
{
    // Required modules
    $modules = array(
        'errorhandler',
        'api',
        'config',
        'acceptedpages'
    );

    // Include required modules
    foreach ( $modules as $m )
    {
        $m .= ".module.php";

        if ( ! file_exists( $m ) )
        {
            inviErrorHandler::handle( inviErrors::FILE_NOT_FOUND, __FILE__ . ":" . __LINE__, "...", "Module {$m} not found" );
        }

        include_once( $m );
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
        $page = Config::get( "system/mainPage" );
    }

    if ( file_exists( "pages".DS.$page.".php" ) && in_array( $_GET['page'], $accepted_list ) )
    {
        // If page exists and it's in the accepted list, execute it

        include_once( "pages".DS.$page.".php" );
        exit;
    } else {
        // If not, generate 404

        header( "HTTP/1.1 404 Not Found" );
        exit;
    }

}catch(Exception $e){
    print( "Unknown error." );
}