<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 21/10/17
 * Time: 20:35
 */

class Paginator
{
    /** @var  string */
    protected $class;
    /** @var  int */
    protected $pageSize;
    /** @var  int */
    protected $offset;
    /** @var  PDORequest */
    protected $request;

    /** @var string */
    private $sessKey;

    const DEFAULT_PAGESIZE = 10;

    const PARAM     = 'offset';
    

    public function __construct(string $class)
    {
        $this->setClass($class);
        $this->setPagesize(self::DEFAULT_PAGESIZE);
        $this->sessKey = "paginator-$class";
    }

    protected function setClass($class)
    {
        $this->class = $class;
    }

    public function setPagesize($ps)
    {
        $this->pageSize = $ps;
    }

    public function getPagesize()
    {
        return $this->pageSize;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setRequest(PDORequest $request)
    {
        $this->request = $request;
    }

    public function fetch()
    {
        $ctl = Controller::getCurrentController();
        $offset = (int)$ctl->getParameter(self::PARAM) ?? 0;
        $this->offset = $offset;
        return $this->fetchSql();
    }

    public function fetchSql()
    {
        if ($this->request === null) {
            $db = PDOHelper::getInstance();
            $req = $db->createSelect()
                ->from(Helper::fromCamelCase($this->class));
        }
        else {
            $req = $this->request;
        }

        $req->setLimit($this->pageSize)
            ->setOffset($this->offset);

        // -- Paginator cache
//        $hash = md5($req->toQueryString());
//        $cache = Factory::getCache();
//        if ($cache->hasValue($hash)) {
//            return $cache->getValue($hash);
//        }

        if ($data = $req->find()) {
            //$cache->setValue($hash, $data);
            return $data;
        }
        else {
            return false;
        }
    }

    public function getInfos()
    {
        $cnt = $this->getCount();
        $res = $this->pageSize;
        $page = ($this->getOffset() / $res)+1;

        $html = "<div class='well'>
                Page $page
                <br/>
                Affichage des résultats $this->offset à ".($this->offset + $this->pageSize)." sur $cnt au total.
                </div>";
        return $html;
    }
    
    public function getControls()
    {
        $html = '<div class="row">';
        $html .= '<form method="POST" action="' . Controller::getCurrentController()->getRequestedUrl() .'">';

        $btnName = self::PARAM;
        $currentOffset = $this->offset;
        $count = $this->getCount();

        // Aller au début

        $html .= "<div class='col-lg-2'>";
        if ($currentOffset > 0) {
            $html .= "<button class='btn btn-danger' name='$btnName' type='submit' value='0'><<</button>";
        }
        $html .= "</div>";

        // Prev
        $prevOffset = $currentOffset - $this->pageSize;
        $html .= "<div class='col-lg-2'>";
        if ($currentOffset > 0) {
            $html .= "<button class='btn btn-default' name='$btnName' type='submit' value='$prevOffset'><</button>";
        }
        $html .= "</div>";

        // Millieu
        $html .= '<div class="col-lg-4"></div>';
        // TODO générer les pages (content)

        // Next
        $nextOffset = $currentOffset + $this->pageSize;
        $html .= "<div class='col-lg-2'>";
        if ($nextOffset < $count) {
            $html .= "<button class='btn btn-default' name='$btnName' type='submit' value='$nextOffset'>></button>";
        }
        $html .= "</div>";

        // LAST
        $lastOffset = $count - $this->pageSize;
        $html .= "<div class='col-lg-2'>";
        if ($nextOffset < $count) {
            $html .= "<button class='btn btn-primary' name='$btnName' type='submit' value='$lastOffset'>>></button>";
        }
        $html .= "</div>";

        $html .= '</div>'; // End row

        return $html;
    }

    private $count;
    public function getCount()
    {
        if ($this->count === null) {
            if ($this->request === null) {
                $req = PDOHelper::getInstance()
                    ->createSelect()
                    ->from(Helper::fromCamelCase($this->class));
            }
            else {
                $req = $this->request;
            }

            $this->count = $req->count();
        }

        return $this->count;
    }

    private static $_instances = [];
    /**
     * @param $class
     * @return Paginator
     */
    public static function getPaginator($class)
    {
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new Paginator($class);
        }

        return self::$_instances[$class];
    }
}