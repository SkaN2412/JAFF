<?php
class inviErrors
{
    // Constants with errors' codes
    const DB_AUTH_FAIL = 10001;
    const FILE_NOT_FOUND = 10002;

    /**
     * @param int $code Code of error
     * @return string Explaining of the error
     */
    public static function getMessage($code) {
        switch($code) {
            case self::DB_AUTH_FAIL:
                $s = "DB authorize failed";
                break;

            default:
                $s = "";
        }
        return $s;
    }
}
