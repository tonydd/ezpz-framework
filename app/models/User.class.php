<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 09/10/17
 * Time: 22:58
 */

class User extends Model
{
    protected $id;
    protected $nom;
    protected $mail;
    protected $pwd;
    protected $token;
    protected $tokenExpiration;
    protected $admin;

    /**
     * @param $pwd
     */
    public function setPwd($pwd)
    {
        $this->pwd = $pwd ? password_hash($pwd, PASSWORD_DEFAULT) : '';
    }

    /**
     * @param $mail
     * @param $pwd
     * @return Model|null
     */
    public static function getLoginUser($mail, $pwd)
    {
        $usersWithMail = User::loadByFields([
            [
                'field' => 'mail',
                'value' => $mail
            ]
        ]);

        if (count($usersWithMail) === 1) {
            $potentialUser = $usersWithMail[0];

            $hashedPwd = $potentialUser->getPwd();

            return password_verify($pwd, $hashedPwd) ? $potentialUser : null;
        }

        return null;
    }
}