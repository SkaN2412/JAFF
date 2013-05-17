<?php
class JFSystem
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
        $templater = new JFTemplater();

        // Load page template
        $templater->load( "main" );

        // Prepare params
        $params = array( 'title' => $title, 'content' => $content );

        // Parse & print
        print( $templater->parse( $params ) );
    }

    /**
     * Include any system module
     *
     * Usage: JFSystem::required(string $className1[, string $className2[, string $classNameN]])
     */
    public static function required()
    {
        $modules = func_get_args();

        foreach ( $modules as $m )
        {
            $m = "system" . DS . $m . ".module.php";

            if ( ! file_exists( $m ) )
            {
                JFErrorHandler::handle( JFError::FILE_NOT_FOUND, __FILE__ . ":" . __LINE__, "...", "Module {$m} not found" );
            }

            include_once( $m );
        }
    }

    public static function loadPlugin( $name, $arg )
    {
        if ( ! file_exists( "plugins" . DS . $name . DS . "init.php" ) )
        {
            throw new JFException( JFError::PLUGIN_NOT_INSTALLED, "Plugin {$name} not installed or installation corrupted" );
        }

        include_once( "plugins" . DS . $name . DS . "init.php" );

        return new $name( $arg );
    }
}