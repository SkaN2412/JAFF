<?php
class ajaxUser
{
    public static function register()
    {
        JFSystem::required( "users" );

        try{
            $return = array();
            JFUser::register( $_POST['email'], $_POST['password'], $_POST['nickname'], "admin" );
            $return['resultText'] = "Пользователь зарегистрирован!";
        } catch ( JFException $e ) {
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

    public static function auth()
    {
        JFSystem::required( "users" );

        try{
            JFUser::authorize();
            $return['result'] = "OK";

        } catch ( JFException $e ) {
            $return['result'] = $e->getCode() . " -> " . $e->getMessage();
        }
        return $return;
    }
}