<?php

namespace Application\LiipToBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{

    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function indexAction()
    {

        $this->view->setTemplate('LiipToBundle:Default:index.twig');
        return $this->view->handle();

//        return $this->render('LiipToBundle:Default:index.twig.html');
    }


}
