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
        $response = null;
        $code = $this->request->get('code', null);
        if ($code) {
            //normalize code
            $code = preg_replace("#[^a-zA-Z0-9_]#", "", $code);
        } else if ($revcan = $this->getRevCanonical($this->url)) {
            return new \Symfony\Component\HttpFoundation\Response($revcan,200,array("Content-Type"=>"text/plain"));
        }
        $data = 'http://example.com/'.$this->getShortCode($this->url, $code);
        return new \Symfony\Component\HttpFoundation\Response($data,200,array("Content-Type"=>"text/plain"));
        
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

    protected function getRevCanonical($url) {

        $ch = curl_init();

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, "http://revcanonical.appspot.com/api?url=" . urlencode($this->url));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // grab URL and pass it to the browser
        $data = curl_exec($ch);

        $respCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // close cURL resource, and free up system resources
        curl_close($ch);

        if ($respCode != 200) {
            return false;
        } else if ($data != $url) {
            return $data;
        } else {
            return false;
        }
    }

    protected function getShortCode($url, $usercode = null, $lconly = false) {
        // if no ., it's not a real URL :)
        if (strpos($url, '.') === false) {
            return $url;
        }
        $url = $this->normalizeUrl($url);
        //check if it's an own URL :)
        $host = 'http://' . $this->request->getHost();

        if (strpos($url, $host) === 0) {
            return substr($url, strlen($host) + 1);
        }


        $urlmd5 = md5($url);

        //check if a code exists
        $code = $this->getCodeFromDBWithMD5($urlmd5);
        //if not create one
        if (!$code) {
            // insert url
            $this->insertUrl($url, $usercode, $lconly, $urlmd5);
            // get code again (if another code with the same url was inserted in the meantime...)
            $code = $this->getCodeFromDBWithMD5($urlmd5);

        }
        return $code;

    }


    protected function normalizeUrl($url) {
        if (strpos($url, 'https:') === 0) {
            $url = preg_replace("#https:/+#", "", $url);
            $url = 'https://' . $url;
        } else {
            $url = preg_replace("#http:/+#", "", $url);
            $url = 'http://' . $url;
        }
        return $url;

    }

        protected function insertUrl($url, $code = null, $lconly = false, $urlmd5 = null) {
        if (!$urlmd5) {
            $urlmd5 = md5($url);
        }

        if ($code && $this->codeExists($code)) {
            $code = $this->getNextCode($lconly);
        }

        if (!$code) {
            $code = $this->getNextCode($lconly);
        }

        $query = 'INSERT INTO urls (code,url,md5) VALUES (:code,:url,:urlmd5)';

        $stm = $this->db->prepare($query);

        if (!$stm->execute(array(
                ':code' => $code,
                ':url' => $url,
                ':urlmd5' => $urlmd5
        ))) {
            die("DB Error");
        }
        return $code;
    }

    protected function getNextCode($lconly) {
        if ($lconly) {
            $tablename = 'lower';
            $id = $this->nextId($tablename);
            $code = $this->id2url($id, $lconly);
        } else {
            $tablename = 'mixed';
            $code = $this->id2url($this->nextId($tablename), $lconly);
        }
        if ($this->codeExists($code)) {
            $code = $this->getNextCode($lconly);
        }

        return $code;
    }

    protected function codeExists($code) {
        $query = "SELECT count(code) from urls where code = " . $this->db->quote($code);
        $res = $this->db->query($query);
        if (!$res) {
            $info =  $this->db->errorInfo();
            throw new api_exception_Db(api_exception::THROW_FATAL,array(),0,$info[2]);
        }
        $r = $res->fetch();
        if ($r && $r[0] > 0) {
            return true;
        }
        return false;
    }
    protected function id2url($val, $lconly = false) {
        if (0 == $val) {
            return 0;
        }
        if ($lconly) {
            $base = 36;
            $symbols = 'adgjmptuwvk0376e9f8b4y2osi5nz1crhxlq';
        } else {
            $base = 63;
            $symbols = 'JVPAGYRKBWLUTHXCDSZNFOQMEIef02nwy1mdtx7p89653cbaoj4igkvrsqz_hul';
        }
        $result = '';
        $exp = $oldpow = 1;
        while ($val > 0 && $exp < 10) {

            $pow = pow($base, $exp++);

            $mod = ($val % $pow);
            // print $mod ."\n";
            $result = substr($symbols, $mod / $oldpow, 1) . $result;
            $val -= $mod;
            $oldpow = $pow;
        }
        return $result;
    }


    protected function nextId($name = 'mixed') {

        $sequence_name = 'ids_' . $name;
        $seqcol_name = 'id';
        $query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (NULL)";
        $res = $this->db->exec($query);
        if (!$res) {
            $info =  $this->db->errorInfo();
            throw new api_exception_Db(api_exception::THROW_FATAL,array(),0,$info[2]);
        }

        $value = $this->db->lastInsertId($seqcol_name);

        if (!$value) {
           throw new api_exception(api_exception::THROW_FATAL,array(),0,"Couldn't get a value for nextId");
        }



        if (is_numeric($value)) {
            $query = "DELETE FROM $sequence_name WHERE $seqcol_name < $value";
            $this->db->query($query);
        }
        return $value;
    }
}
