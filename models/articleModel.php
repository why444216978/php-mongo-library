<?php

class Article
{
    private $table = 'article';
    private $_mongo;

    public function __construct($mongo)
    {
        $this->_mongo = $mongo;
    }
    public function getAll($filter, $options)
    {
        $data = $this->_mongo->query($this->table, $filter, $options);
        return $data;
    }

    public function update($where, $data)
    {
        return $this->_mongo->update($this->table, $where, $data);
    }

    public function getCount($filter)
    {
        return $this->_mongo->count($this->table, $filter);
    }
}
