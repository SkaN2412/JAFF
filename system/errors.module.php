<?php
class inviErrors
{
    // Base errors
    const FILE_NOT_FOUND     = 10001;

    // Database errors
    const DB_CONN_FAIL       = 10002;
    const DB_EXEC_FAIL       = 10003;

    // Users manage errors
    const USR_AUTHD          = 10004;
    const USR_REGISTERED     = 10005;
    const USR_EMAIL_USED     = 10006;
    const USR_NOT_REGISTERED = 10007;
    const USR_WRONG_PASSWD   = 10008;
}
