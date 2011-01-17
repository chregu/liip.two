<?php

namespace Bundle\Liip\ShortenerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('LiipShortenerBundle:Default:index.php');
    }
}
