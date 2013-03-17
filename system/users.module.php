<?php

/*
 * Module for registering user and authorizing him
 */
class User
{
    /*
     * function register() adds user to DB and euthorizes him
     */
    public static function register($login, $password, $email)
    {
        // Check, is user authorized. If authorized, he's trying to hack system
        @session_start();
        if ( isset($_SESSION['authorized']) )
        {
            throw new inviException(7, "Can't register while authorized.");
        }
        
        // Connect to DB
        $DBH = new inviPDO();
        
        // Check login given for existing
        if ( self::isRegistered($login) )
        {
            throw new inviException(1, "This login is already registered");
        }
        
        // Check email given for existing
        $DBH->query( "SELECT `email` FROM `users` WHERE `email` = :email", array( 'email' => $email ) );
        if ( $DBH->stmt->rowCount() > 0 )
        {
            throw new inviException(2, "This email is already used");
        }
        
        // All is right, user with data given does not exist. Now generate password hash with Bcrypt class
        $crypt = new Bcrypt(15);
        $hash = $crypt->hash($password);
        
        // And now insert data into DB
        $stmtParams = array(
            'login' => $login,
            'password' => $hash,
            'email' => $email
        );
        $result = $DBH->query("INSERT INTO `users` (`login`, `password`, `email`) VALUES (:login, :password, :email)", $stmtParams );
        if ( ! $result )
        {
            throw new inviException(3, "MySQL error: {$DBH->stmt->errorInfo()}");
        }
        
        // Now authorize user
        self::authorize($login, $password);
    }
   /*
    * authorize() checks password correctness and authorize user. Information about user you can get with get($what) method of this class
    */
    public static function authorize()
    {
        // Check for user data in session. If there's one, auth user.
        @session_start();
        if ( isset($_SESSION['authorized']) )
        {
            return TRUE;
        } else {
            // If there's nothing in session, get login and password from post variables
            $login = $_POST['login'];
            $password = $_POST['password'];
        }
        
        // Connect to DB
        $DBH = new inviPDO();
        
        // Generate hash of password
        $crypt = new Bcrypt(15);
        $hash = $crypt->hash($password);
        
        // Get data from DB
        $DBH->query( "SELECT * FROM `users` WHERE `login` = :login", array( 'login' => $login ) );
        // If nothing is returned, throw exception
        if ( $DBH->stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $userData = $DBH->fetch();
        $userData = $userData[0];
        
        // Check password correctness
        if ( ! $crypt->verify( $password, $userData['password'] ) )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Insert data into session variables
        unset($userData['password']);
        $_SESSION['authorized'] = TRUE;
        $_SESSION = array_merge($_SESSION, $userData);
        return TRUE;
    }
    /*
     * Method get() returns data of user. It requires login and returns array with data.
     */
    public static function get($login = NULL)
    {
        // If $login isn't given, return data of current user
        if ( $login == NULL )
        {
            $return = array(
                'login' => $_SESSION['login'],
                'email' => $_SESSION['email'],
                'group' => $_SESSION['group'],
                'blocked_until' => $_SESSION['blocked_until']
            );
            return $return;
        }
        
        // Connect to DB
        $DBH = new inviPDO();
        
        // Select data
        $DBH->query( "SELECT `login`, `email`, `group`, `blocked_until` FROM `users` WHERE `login` = :login", array( 'login' => $login ) );
        
        // If nothing is returned, throw exception
        if ( $DBH->stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $result = $DBH->fetch();
        return $result[0];
    }
    /*
     * changePassword() requires old password and new password. User must be authorized - login will be taken from auth-data. 
     */
    public static function chandePassword($password, $newPassword)
    {
        // Connect to DB
        $DBH = new inviPDO();
        
        // Take login from class property
        $login = self::$login;
        
        // Check, is the old password correct
        $DBH->query( "SELECT `password` FROM `users` WHERE `login` = :login", array( 'login' => $login ) );
        $checkPassword = $DBH->fetch();
        $checkPassword = $checkPassword[0];
        
        // Check password correctness
        $crypt = new Bcrypt(15);
        if ( $crypt->hash($password) != $checkPassword['password'] )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Update password in DB
        $stmtParams = array(
            'password' => $crypt->hash($newPassword),
            'login' => $login
        );
        $result = $DBH->query( "UPDATE `users` SET `password` = :password WHERE `login` = :login", $stmtParams );
        if ( $stmt->rowCount() < 1 || ! $result )
        {
            throw new inviException(6, "Unknown error, nothing is changed");
        }
        return TRUE;
    }
    /*
     * generateRecoveryKey() returns key, that must be given for change password
     */
    public static function generateRecoveryKey($login)
    {

    }
    /*
     * function user_changeLostPassword() changes password of user if key given is similar with generated.
     */
    public static function changeLostPassword($key, $newPassword)
    {

    }
    /*
     * This private method is needed for checking user registered
     */
    private static function isRegistered($login)
    {
        // Connect to DB
        $DBH = new inviPDO();
        
        // Select entry with this login
        $DBH->query( "SELECT `login` FROM `users` WHERE `login` = :login", array( 'login' => $login ) );
        if ( $DBH->stmt->rowCount() < 1 )
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    /*
     * Method is required for registering user and changing user's email
     */
    private static function verifyEmail($email)
    {
        // Пока обойдемся без этого, сделаю подтверждение почты в бета-версии
    }
}
?>
