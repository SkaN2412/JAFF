<?php
include_once( "system" . DS . "crypt.php" );

/*
 * Module for registering user and authorizing him
 */
class User
{
	/**
	 * Adds user to database
	 *
	 * @param string $nickname nickname wanted
	 * @param string $password Password
	 * @param string $email    Email
	 *
	 * @throws inviException
	 */
	public static function register( $email, $password, $nickname )
	{
		// Check, is user authorized. If authorized, deny registration
		@session_start();
		if ( isset( $_SESSION['authorized'] ) )
		{
			throw new inviException( inviErrors::USR_AUTHD );
		}

		// Connect to DB
		$DBH = DB::$conn;

		// Check email given for existing
		if ( self::isRegistered( $email ) )
		{
			throw new inviException( inviErrors::USR_REGISTERED );
		}

		// Check nickname given for existing
		$DBH->selectEntry( "users", array( 'nickname' => $nickname ) );
		if ( $DBH->stmt->rowCount() > 0 )
		{
			throw new inviException( inviErrors::USR_NICKNAME_USED );
		}

		// All is right, user with data given does not exist. Now generate password hash with Bcrypt class
		$crypt = new Bcrypt( 15 );
		$hash = $crypt->hash( $password );

		// And now insert data into DB
		$DBH->insertData( "users", array( 'nickname' => $nickname, 'password' => $hash, 'email' => $email ) );

		// Now authorize user
		self::authorize( $email, $password );
	}

	/**
	 * Authorize user
	 *
	 * @return void
	 * @throws inviException
	 */
	public static function authorize()
	{
		// Check for user data in session. If there's one, auth user.
		@session_start();
		if ( isset( $_SESSION['authorized'] ) )
		{
			return TRUE;
		} else
		{
			// If there's nothing in session, get nickname and password from post variables
			$email = $_POST['email'];
			$password = $_POST['password'];
		}

		// Connect to DB
		$DBH = DB::$conn;

		// Get data from DB
		$userData = $DBH->selectEntry( "users", array( 'email' => $email ) );
		// If nothing is returned, throw exception
		if ( $DBH->stmt->rowCount() < 1 )
		{
			throw new inviException( inviErrors::USR_NOT_REGISTERED );
		}

		// Check password correctness
		$crypt = new Bcrypt( 15 );
		if ( ! $crypt->verify( $password, $userData['password'] ) )
		{
			throw new inviException( inviErrors::USR_WRONG_PASSWD );
		}

		// Insert data into session variables
		unset( $userData['password'] );
		$_SESSION['authorized'] = TRUE;
		$_SESSION = array_merge( $_SESSION, $userData );
	}

	/**
	 * Returns user's data
	 *
	 * @param string $email [optional] Email of user. If not given, will return current user's data
	 *
	 * @return array Array with data
	 * @throws inviException
	 */
	public static function get( $email = NULL )
	{
		// If $nickname isn't given, return data of current user
		if ( $email == NULL )
		{
			$return = array( 'nickname' => $_SESSION['nickname'], 'email' => $_SESSION['email'], 'group' => $_SESSION['group'] );
			return $return;
		}

		// Connect to DB
		$DBH = DB::$conn;

		// Select data
		$result = $DBH->selectEntry( "users", array( 'email' => $email ), "nickname, email, group" );

		// If nothing is returned, throw exception
		if ( $DBH->stmt->rowCount() < 1 )
		{
			throw new inviException( inviErrors::USR_NOT_REGISTERED );
		}

		return $result;
	}

	/**
	 * Changes password
	 *
	 * @param string $password    Current password
	 * @param string $newPassword New password
	 *
	 * @return void
	 * @throws inviException
	 */
	public static function changePassword( $password, $newPassword )
	{
		// Connect to DB
		$DBH = DB::$conn;

		// Take nickname from class property
		$email = self::get()['email'];

		// Check, is the old password correct
		$checkPassword = $DBH->selectEntry( "users", array( 'email' => $email ), "password" );

		// Check password correctness
		$crypt = new Bcrypt( 15 );
		if ( $crypt->hash( $password ) != $checkPassword['password'] )
		{
			throw new inviException( inviErrors::USR_WRONG_PASSWD );
		}

		// Update password in DB
		$DBH->updateData( "users", array( 'password' => $crypt->hash( $newPassword ) ), array( 'email' => $email ) );
	}

	/*
	 * generateRecoveryKey() returns key, that must be given for change password
	 */
	public static function generateRecoveryKey( $nickname )
	{

	}

	/*
	 * function user_changeLostPassword() changes password of user if key given is similar with generated.
	 */
	public static function changeLostPassword( $key, $newPassword )
	{

	}

	/**
	 * Check, is user already registered or not
	 *
	 * @param string $nickname nickname to check
	 *
	 * @return bool
	 */
	private static function isRegistered( $email )
	{
		// Connect to DB
		$DBH = DB::$conn;

		// Select entry with this nickname
		$DBH->selectEntry( "users", array( 'email' => $email ), "email" );

		if ( $DBH->stmt->rowCount() < 1 )
		{
			return FALSE;
		} else
		{
			return TRUE;
		}
	}

	/*
	 * Method is required for registering user and changing user's email
	 */
	private static function verifyEmail( $email )
	{
		// Пока обойдемся без этого, сделаю подтверждение почты в бета-версии
	}
}