<?php
/**
 * Class for handling errors
 */
class JFErrorHandler
{
    protected static $printing = true;
    protected static $pf = "html";
    protected static $logging = true;
    protected static $lf = "html";
    protected static $logfile = "log.html";

    protected static $errno;
    protected static $error;
    protected static $file;
    protected static $date;
    protected static $trace;

    /**
     * @param int    $mode Handling mode. 0 - no print, no log; 1 - print, no log; 2 - no print, log; 3 - print, log
     * @param string $logfile
     * @param string $lf
     * @param string $pf
     *
     * @return bool
     */
    public static function __init( $mode, $logfile = "log.html", $lf = "html", $pf = "html" )
    {
        // Define handling mode
        switch ( $mode )
        {
            case 0:
                self::$printing = false;
                self::$logging = false;
                break;
            case 1:
                self::$printing = true;
                self::$logging = false;
                break;
            case 2:
                self::$printing = false;
                self::$logging = true;
                break;
            case 3:
                self::$printing = true;
                self::$logging = true;
                break;
        }

        // Define and create logfile, if it does not exist
        if ( ! file_exists( $logfile ) && self::$logging == true )
        {
            touch( $logfile );
            self::$logfile = $logfile;
        }

        // Define logging and printing format
        if ( $lf == "html" || $lf = "plain" )
        {
            self::$lf = $lf;
        }

        if ( $pf == "html" || $pf == "plain" )
        {
            self::$pf = $pf;
        }
    }

    /**
     * @param int    $errno Code of error
     * @param string $file  File, that executed error
     * @param string $trace Trace to file
     * @param string $error Error message
     */
    public static function handle( $errno, $file, $trace, $error = NULL )
    {
        self::$errno = $errno;
        self::$date = date( "r" );
        self::$file = $file;
        self::$trace = $trace;

        if ( $error == NULL )
        {
            self::$error = "-";
        } else
        {
            self::$error = $error;
        }

        if ( self::$printing )
        {
            self::printError();
        }
        if ( self::$logging )
        {
            self::logError();
        }
    }

    protected static function printError()
    {
        print( self::prepareData( "p" ) );
    }

    protected static function logError()
    {
        $content = file_get_contents( self::$logfile );
        file_put_contents( self::$logfile, $content . self::prepareData( "l" ) );
    }

    protected static function prepareData( $for )
    {
        $templater = new JFTemplater();

        switch ( $for )
        {
            case "p":
                switch ( self::$pf )
                {
                    case "html":
                        $load = "htmlError";
                        break;
                    case "plain":
                        $load = "plainError";
                        break;
                }
                break;
            case "l":
                switch ( self::$lf )
                {
                    case "html":
                        $load = "htmlError";
                        break;
                    case "plain":
                        $load = "plainError";
                        break;
                }
                break;
        }

        $templater->load( $load );
        return $templater->parse( array( 'errno' => self::$errno, 'error' => self::$error, 'file' => self::$file, 'date' => self::$date, 'trace' => self::$trace ) );
    }
}

JFErrorHandler::__init( 0, "errors.log", "plain", "html" );