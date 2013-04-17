<?php
class Users
{
    public static function register()
    {
        System::required( "users" );

        try{
            $return = array();
            User::register( $_POST['email'], $_POST['password'], $_POST['nickname'] );
            $return['resultText'] = "Пользователь зарегистрирован!";
        } catch ( inviException $e ) {
            switch ($e->getCode())
            {
                case 10005:
                    $return['resultText'] = "Пользователь уже зарегистрирован";
                    break;
                case 10006:
                    $return['resultText'] = "Такой ник уже зарегистрирован";
                    break;
                default:
                    $return['resultText'] = "Ошибка #{$e->getCode()}";
            }
        }

    }
}