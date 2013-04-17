<?php

/*
 * inviException
 * Extended exceptions tool for php
 */

final class JFException extends Exception
{
    //Metadata
    const NAME = "inviException";
    const VER = "0.2beta";

    /**
     * Constructor throws exception
     *
     * @param int    $errno Code of error
     * @param string $error Error message
     */
    public function __construct( $errno, $error = NULL )
    {
        parent::__construct( $error, (int)$errno );

        JFErrorHandler::handle( parent::getCode(), parent::getFile() . ":" . parent::getLine(), parent::getTraceAsString(), parent::getMessage() );
    }
}