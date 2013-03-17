<?php
/**
 * Class for handling errors
 */
class inviErrorHandler
{
    protected static $errors = "inviErrors";

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
     * Method defines class properties
     *
     * @param int $mode Defines logging and printing. 0 - no log, no print; 1 - print, no log; 2 - log, no print; 3 - log, print
     * @param string $logfile [optional] Defines file for logs. Default is "log.html"
     * @param string $lf [optional] Logging format: "html" or "plain". Default is "html"
     * @param string $pf [optional] Printing format. Similar as logging
     */
    public static function __init($mode, $logfile = "log.html", $lf = "html", $pf = "html")
    {
        // Define hadling mode
        switch ($mode)
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
        if ( ! file_exists($logfile) && self::$logging == true )
        {
            touch($logfile);
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
     * Function handles error
     *
     * @param $errno Code of error
     * @param $file File, that generated error
     * @param $trace Stack of function behind the error
     * @param string $error [optional] Explaining of the error
     */
    public static function handle($errno, $file, $trace, $error = NULL)
    {
        self::$errno = $errno;
        self::$date = date("c");
        self::$file = $file;
        self::$trace = $trace;

        if ( $error == NULL )
        {
            self::$error = "-";
        } else {
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
        file_put_contents( self::$logfile, $content . self::prepareData("l") );
    }

    /**
     * Method prepares data for logging or printing
     *
     * @param string $for Sould be "l" or "p" - log or print coordination
     * @return string Filled template
     */
    protected static function prepareData( $for )
    {
        $templater = new inviTemplater();

        // Var $for contains "l" or "f" in case of log or print data needed coordination. self::$lf and self::$pf contains log format and print format coordination. So $load will match this template: (plain|html)Error
        $load = self::${$for."f"} . "Error";

        /*switch ($for)
        {
            case "p":
                switch (self::$pf)
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
                switch (self::$lf)
                {
                    case "html":
                        $load = "htmlError";
                        break;
                    case "plain":
                        $load = "plainError";
                        break;
                }
                break;
        }*/

        $templater->load($load);
        return $templater->parse( array(
                   'errno' => self::$errno,
                   'error' => self::$error,
                   'file' => self::$file,
                   'date' => self::$date,
                   'trace' => self::$trace
               ) );
    }
}
