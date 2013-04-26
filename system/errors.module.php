<?php
class JFError
{
    // Base errors
    const FILE_NOT_FOUND         = 10001;
    const PLUGIN_NOT_INSTALLED   = 10014;

    // Database errors
    const DB_CONN_FAIL           = 10002;
    const DB_EXEC_FAIL           = 10003;

    // Users manage errors
    const USR_AUTHD              = 10004;
    const USR_REGISTERED         = 10005;
    const USR_NICKNAME_USED      = 10006;
    const USR_NOT_REGISTERED     = 10007;
    const USR_WRONG_PASSWD       = 10008;

    // Templater errors
    const TMPLTR_NOT_ARRAY_GIVEN = 10009;
    const TMPLTR_VAR_NOT_FOUND   = 10010;
    const TMPLTR_VAR_NOT_ARRAY   = 10011;
    const TMPLTR_ARRAY_1DIM      = 10012;
    const TMPLTR_NOT_VALID_CASE  = 10013;

    // Pages management errors
    const PGS_ALREADY_EXISTS     = 10015;
}