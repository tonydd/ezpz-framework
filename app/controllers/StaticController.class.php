<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 10/10/17
 * Time: 18:57
 */

class StaticController extends Controller
{
    /**
     * Action to fetch Core JavaScript
     */
    public function coreJsAction()
    {
        $this->setheader('Content-Type', 'application/javascript');
        echo $this->getRenderer()->getCoreJS();
    }

    /**
     * Action to fetch Custom Javascript
     */
    public function jsAction()
    {
        $this->setheader('Content-Type', 'application/javascript');
        echo $this->getRenderer()->getJS();
    }

    /**
     * Action to fetch CSS code
     */
    public function cssAction()
    {
        $this->setheader('Content-Type', 'text/css');
        echo $this->getRenderer()->getCSS();
    }

    /**
     * Action to display 404 page
     */
    public function notFoundAction()
    {
        $this->getRenderer()
            ->setTitle('Introuvable')
            ->setTemplate('404')
            ->render();
    }

    /**
     *
     */
    public function registerWorkerAction()
    {
        $this->setheader('Content-Type', 'application/json');

        Factory::getCache()->setValue('worker_register', 1);

        echo json_encode('OK');
    }

    /**
     * An action for testing purpose
     */
    public function testAction()
    {
        $this->getRenderer()->setTemplate('recettes/index');
    }
}