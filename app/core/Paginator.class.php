<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 21/10/17
 * Time: 20:35
 */

class Paginator
{

    protected $class;
    protected $pageSize;

    private $sessKey;

    const DEFAULT_PAGESIZE = 10;

    const NEXT      = 'next';
    const PREV      = 'prev';
    const PARAM     = 'pdirection';
    

    public function __construct(string $class)
    {
        $this->setClass($class);
        $this->setPagesize(self::DEFAULT_PAGESIZE);
        $this->sessKey = "paginator-$class";
        $this->getCount();
    }

    protected function setClass($class)
    {
        $this->class = $class;
    }

    public function setPagesize($ps)
    {
        $this->pageSize = $ps;
    }

    public function setPage($page)
    {
        $this->setOffset($this->pageSize * $page);
    }

    private function getOffset()
    {
        return SessionHelper::getValue($this->sessKey) ?? 0;
    }

    private function setOffset($offset)
    {
        SessionHelper::setValue($this->sessKey, $offset);
    }

    public function resetOffset()
    {
        $this->setOffset(0);
    }

    public function fetch()
    {
        $db = PDOHelper::getInstance();
        $offset = $this->getOffset();
        $req = $db->createSelect()
            ->from(Helper::fromCamelCase($this->class))
            ->setLimit($this->pageSize)
            ->setOffset($offset);

        if ($data = $req->find()) {
            return $data;
        }

        return false;
    }

    public function fetchNext()
    {
        $db = PDOHelper::getInstance();
        $offset = $this->getOffset();
        $req = $db->createSelect()
            ->from(Helper::fromCamelCase($this->class))
            ->setLimit($this->pageSize)
            ->setOffset($offset);

        if ($data = $req->find()) {
            $this->setOffset($offset + $this->pageSize);
            return $data;
        }
        else {
            $this->resetOffset();
            return false;
        }
    }

    public function fetchPrevious()
    {
        $db = PDOHelper::getInstance();
        $offset = $this->getOffset();

        if ($offset > 0) {
            $offset = $offset - $this->pageSize;
        }

        $req = $db->createSelect()
            ->from(Helper::fromCamelCase($this->class))
            ->setLimit($this->pageSize)
            ->setOffset($offset);

        if ($data = $req->find()) {
            $this->setOffset($offset);
            return $data;
        }
        else {
            $this->resetOffset();
            return false;
        }
    }

    public function getControls()
    {
        $html = '<div class="row">';

        // Aller au début
        $html .= '<div class="col-lg-2">DEB</div>';
        // Prev
        $html .= '<div class="col-lg-2">PREV</div>';

        // Millieu
        $html .= '<div class="col-lg-4"></div>';

        // Next
        $html .= '<div class="col-lg-2">NEXT</div>';

        // LAST
        $html .= '<div class="col-lg-2">END</div>';

        $html .= '</div>'; // End row

        return $html;
    }

    private $count;
    public function getCount()
    {
        if ($this->count === null) {
            $this->count = PDOHelper::getInstance()
                ->createSelect()
                ->from(Helper::fromCamelCase($this->class))
                ->count();
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