<?php
/**
 * Main class of inviCMS.
 *
 * @author Andrey "SkaN" Kamozin <andreykamozin@gmail.com>
 */
class System
{
    /**
     * Method inserts your content into main template and prints it
     *
     * @param string $title   Title of current page
     * @param string $content Content, which should insert into template
     *
     * @return void
     */
    public static function out( $title, $content )
    {
        // Init templater
        $templater = new inviTemplater();

        // Load page template
        $templater->load( "main" );

        // Prepare params
        $params = array( 'title' => $title, 'content' => $content );

        // Parse & print
        print( $templater->parse( $params ) );
    }

    public static function required()
    {
        $modules = func_get_args();

        foreach ( $modules as $m )
        {
            $m = "system" . DS . $m . ".module.php";

            if ( ! file_exists( $m ) )
            {
                inviErrorHandler::handle( inviErrors::FILE_NOT_FOUND, __FILE__ . ":" . __LINE__, "...", "Module {$m} not found" );
            }

            include_once( $m );
        }
    }
}