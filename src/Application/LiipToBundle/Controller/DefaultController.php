<?php

namespace Application\LiipToBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LiipToBundle:Default:index.twig.html');
    }
}
