<?php
/**
 * Created by PhpStorm.
 * User: tony
 * Date: 14/10/17
 * Time: 18:23
 */

class AjaxController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->setheader('Content-Type', 'application/json');
    }

    public function autocompleteAction()
    {
        $params = $this->getParameters();
        $autocompleteConf = $params['autocomplete'];
        $out = [];

        if (!isset($autocompleteConf['model'])) {
            // Error
        }

        if (!isset($autocompleteConf['term'])) {
            // Error
        }

        if (!isset($autocompleteConf['queryField'])) {
            // Error
        }

        if (!isset($autocompleteConf['fields'])) {
            // Error
        }

        /** @var Model $model */
        $model = $autocompleteConf['model'];
        $term = $autocompleteConf['term'];
        $comp = $autocompleteConf['comp'] ?? '=';
        $queryField = $autocompleteConf['queryField'];
        $fields = $autocompleteConf['fields'];

        $queryConditions = [
            [
                'field'     =>  $queryField,
                'value'     => $term,
                'compare'   => $comp,
                'case'      => false
            ]
        ];

        /** @var Model[] $founds */
        $founds = $model::loadByFields($queryConditions);

        foreach ($founds as $found) {
            $sub = [];
            foreach ($fields as $field) {
                $meth = "get" . ucfirst($field);
                $sub[$field] = $found->$meth();
            }
            $out[] = $sub;
        }

        echo json_encode($out);
    }
}