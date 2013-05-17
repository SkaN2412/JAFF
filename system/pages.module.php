<?php
class JFPages {
    public static function getList()
    {
        $pages = scandir( "pages" );

        // Parse file for JFSystem::required() and JFTemplater::load() usages
        $list = array();
        foreach ( $pages as $name )
        {
            $list[$name]['templates'] = self::templatesUsages( $name );
        }

        return $list;
    }

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

    private static function dependsOn( $page )
    {
        $string = file_get_contents( "pages" . DS . $page . ".php" );

        preg_match_all( '@JFSystem::required\(([^)]*)\);@i', $string, $matches );

        $return = array();
        for ( $i = 0; $i < count( $matches[1] ); $i++ )
        {
            preg_match_all( "@['\"]([A-z0-9_-]*)['\"]@", $matches[1][$i], $temp );
            $return = array_merge( $return, $temp[1] );
        }
        $return = array_unique( $return );
        // TODO: dependencies on plugins
        return $return;
    }

    private static function templatesUsages( $page )
    {
        $string = file_get_contents( "pages" . DS . $page . ".php" );

        preg_match_all( '@\$templater->load\( ?"([^)]*)" ?\);@i', $string, $matches );

        $matches = array_unique( $matches[1] );

        return $matches;
    }
}