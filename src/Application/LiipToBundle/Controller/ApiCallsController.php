<?php

namespace Application\LiipToBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiCallsController extends Controller
{
    protected $view;
    protected $request;

    public function __construct($view, $request, $db)
    {
        $this->db = $db;
        $this->view = $view;
        $this->request = $request;
        $this->url = $this->request->get("url");
    }

   
    public function checkCodeReverseAction()
    {
        
        $data = $this->getCodeFromDB($this->request->get("url"));
        if (!$data) {
            $data = false;
        }

        return new \Symfony\Component\HttpFoundation\Response(json_encode($data));
        // the LiipViewBundle way, but that returns an json-ified array of $data, not $data itself
        /*
          $this->view->setParameters($data);
          return $this->view->handle();
         */
    }

    public function createAction() {

        if (empty($this->url)) {
            die("empty url");
        }
        $code = $this->request->get('code', null);
        if ($code) {
            //normalize code
            $code = preg_replace("#[^a-zA-Z0-9_]#", "", $code);
        } else if ($revcan = $this->getRevCanonical($this->url)) {
               $this->data = $revcan;
               return;
        }
        $this->data = 'http://' . $this->request->getHost() . '/' . $this->getShortCode($this->url, $code);
    }


    protected function getCodeFromDB($url)
    {
        $urlmd5 = md5($url);
        return $this->getCodeFromDBWithMD5($urlmd5);
    }

    protected function getCodeFromDBWithMD5($urlmd5)
    {
        
        $query = "SELECT code FROM urls where md5 = :urlmd5";
        $stm = $this->db->prepare($query);
        $stm->execute(array(
                ':urlmd5' => $urlmd5
        ));
        return $stm->fetchColumn();
    }


}
