<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 08/10/17
 * Time: 19:47
 */

class LoginController extends Controller
{
    private $currentUser;

    public function __construct()
    {
        if (SessionHelper::valueExists('currentUser')) {
            $this->setCurrentUser(SessionHelper::getValue('currentUser'));
        }

        parent::__construct();
    }

    public function setCurrentUser($user)
    {
        $this->currentUser = $user;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function unsetCurrentUser()
    {
        $this->currentUser = null;
        SessionHelper::unsetValue('currentUser');
    }

    public function isLogged()
    {
        if (SessionHelper::valueExists('currentUser')) {
            return true;
        }

        return false;
    }

    public function getNom()
    {
        return $this->currentUser->getNom() ?? 'UNKNOWN';
    }

    public function loginFormAction()
    {
        $this->getRenderer()
            ->setTitle("Se connecter")
            ->setTemplate('form_login')
            ->render();
    }

    public function loginAction()
    {
        $parameters = $this->getParameters();
        $mail  = htmlentities($parameters['mail']);
        $pwd    = htmlentities($parameters['pwd']);

        if (($user = User::getLoginUser($mail, $pwd)) === null) {
            $this->getRenderer()->addMessage("Vos informations de connexion sont incorrectes !", Renderer::ERR_MESSAGE);
            $this->redirectHome();
            return;
        }

        SessionHelper::setValue('currentUser', $user);
        $this->setCurrentUser($user);
        $this->getRenderer()->addMessage("Vous êtes maintenant connecté(e) !", Renderer::SUCCESS_MESSAGE);
        $this->redirectHome();
    }

    public function logoutAction()
    {
        $this->unsetCurrentUser();
        $this->getRenderer()->addMessage("Vous avez été déconnecté(e)", Renderer::INFO_MESSAGE);
        $this->redirect('index');
    }
}