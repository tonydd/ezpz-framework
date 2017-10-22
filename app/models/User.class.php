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
    protected $login;
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

    public function generateForm()
    {
        // TODO je pense qu'il faudra foutre ça dans une vue (shrug)

        $renderer = Controller::getCurrentController()->getRenderer();

        /** @var Model $class */
        $class = get_class($this);

        $html = '<form method="post" action="' . $renderer->buildUrl('login', 'save') . '">';
        $html .= '<fieldset>';
        $html .= '<legend>Informations du compte</legend>';
        $html .= '<div class="form-container">';

        // -- ID
        if (($pkVal = $this->getId()) !== null) {
            $html .= "<input type='hidden' name='id' id='id' value='$pkVal' />";
        }

        // -- Nom
        $html .= '<div class="form-group">';
        $html .= "<label for='nom'>Nom : </label>";
        $html .= "<input class='form-control' id='nom' name='nom' type='text' value='".$this->getNom()."' />";
        $html .= "</div>";

        // -- Login
        $html .= '<div class="form-group">';
        $html .= "<label for='login'>Login : </label>";
        $html .= "<input class='form-control' id='login' name='login' type='text' value='".$this->getLogin()."' />";
        $html .= "</div>";

        // -- Mail
        $html .= '<div class="form-group">';
        $html .= "<label for='mail'>Mail : </label>";
        $html .= "<input class='form-control' id='mail' name='mail' type='text' value='".$this->getMail()."' />";
        $html .= "</div>";

        $html .='</fieldset><br/>';

        $html .= '<fieldset>';
        $html .= '<legend>Changer de mot de passe</legend>';

        // -- MDP
        $html .= '<div class="form-group">';
        $html .= "<label for='pwd'>Mot de passe : </label>";
        $html .= "<input class='form-control' id='pwd' name='pwd' type='text' value='' />";
        $html .= "</div>";

        $html .= '</div>'; // End div.form-container
        $html .= '</fieldset>';
        $html .= '</form>';

        return $html;
    }
}