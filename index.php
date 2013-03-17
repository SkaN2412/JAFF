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

    // Know, which command to execute. If nothing is given, execute default command
    if ( isset( $_GET['query'] ) )
    {
        $query = $_GET['query'];
        // Let's execute command!
        inviAPI::execute( $query );
        exit;
    }

    if( isset( $_GET['page'] ) && in_array( $_GET['page'], $accepted_list ) ) {
        $page = $_GET['page'];
    } else {
        $page = "main"; // TODO: main из конфига
    }
    // Renderer::render($page);

}catch(Exception $e){
    print( "Unknown error." );
}