<?php
class JFPages {
    public static function add( $name, $content )
    {
        $file = "pages" . DS . $name . ".php";

        if ( file_exists( $file ) )
        {
            throw new JFException( JFError::PGS_ALREADY_EXISTS );
        }

        touch( $file );

        file_put_contents( $file, $content );

        $accepted = file_get_contents( "system" . DS . "acceptedpages" );
        $accepted .= "\n{$name}";

        file_put_contents( "system" . DS . "acceptedpages", $accepted );
    }

    public static function getContents( $name )
    {
        $file = "pages" . DS . $name . ".php";

        if ( ! file_exists( $file ) )
        {
            throw new JFException( JFError::FILE_NOT_FOUND, "Page not found" );
        }

        return file_get_contents( $file );
    }

    public static function edit( $name, $content )
    {
        $file = "pages" . DS . $name . ".php";

        if ( ! file_exists( $file ) )
        {
            throw new JFException( JFError::FILE_NOT_FOUND, "Page to edit not found" );
        }

        file_put_contents( $file, $content );
    }

    public static function remove( $name )
    {
        $file = "pages" . DS . $name . ".php";

        if ( ! file_exists( $file ) )
        {
            throw new JFException( JFError::FILE_NOT_FOUND, "Page to remove not found" );
        }

        unlink( $file );

        $accepted = file( "system" . DS . "acceptedpages", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

        unset( $accepted[ array_search( $name, $accepted ) ] );

        $content = "";
        foreach( $accepted as $page )
        {
            $content .= "{$page}\n";
        }

        file_put_contents( $file, $content );
    }
}