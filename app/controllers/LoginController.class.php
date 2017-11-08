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
        return $this->currentUser ?? new User();
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

    public function isAdmin()
    {
        $usr = $this->getCurrentUser();
        return (int)$usr->getAdmin() === 1;
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
            ->assign('url', $this->getParameter('url'))
            ->render();
    }

    public function loginAction()
    {
        $mail   = $this->getParameter('mail');
        $pwd    = $this->getParameter('pwd');

        if (($user = User::getLoginUser($mail, $pwd)) === null) {
            $this->getRenderer()->addMessage("Vos informations de connexion sont incorrectes !", Renderer::ERR_MESSAGE);
            $this->redirectHome();
            return;
        }

        SessionHelper::setValue('currentUser', $user);
        $this->setCurrentUser($user);
        $this->getRenderer()->addMessage("Vous êtes maintenant connecté(e) !", Renderer::SUCCESS_MESSAGE);

        if (($url = $this->getParameter('url')) !== null) {
            $this->redirectUrl($url);
        }
        else {
            $this->redirectHome();
        }
    }

    public function logoutAction()
    {
        $this->unsetCurrentUser();
        $this->getRenderer()->addMessage("Vous avez été déconnecté(e)", Renderer::INFO_MESSAGE);
        $this->redirect('index');
    }

    public function registerAction()
    {
        $user = new User();
        $user->setData($this->getParameters());
        $user->save();

        $this->getRenderer()->addMessage("Votre compte à bien été crée !", Renderer::INFO_MESSAGE);
        $this->redirect('index');
    }

    public function accountAction()
    {
        if (!$this->isLogged()) {
            $this->getRenderer()
                ->addMessage("Vous devez être connecté pour voir cette page.<br/>Connectez-vous pour être redirigé sur la page souhaitée.",
                    Renderer::WARN_MESSAGE);

            $this->redirect('login', 'loginForm', [
                'url'   => $this->getRequestedUrl()
            ]);

            return;
        }

        $this->getRenderer()
            ->setTitle("Mes infos")
            ->setTemplate('account')
            ->assign('user', $this->getCurrentUser())
            ->render();
    }
}