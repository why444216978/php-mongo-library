<?php
require_once("./mongoService.php");
$mongo = new mongoService();

$article  = $mongo->getCol('article');

//设置过滤条件和选项
$filter = array();
if (!empty($keywords)) {
    $res = $this->getMediaFilter($keywords);
    $filter['media'] = ['$in' => array_values($res)];
}
if (!empty($source)) {
    $filter['source'] = $source;
}

$limit = 30;
$page  = 1;

$filter['created_time'] = [
    '$gte' => 1593532800,
    '$lt' => 1596211200
];

$options['sort']       =  ['pub_time' => -1]; //1-升序，2-降序
$options['limit']      = $limit;
$options['skip'] = ($page - 1) * $limit;
$options['projection'] = ['content' => 0];
//查询所有数据并处理
$data = $article->getAll($filter, $options);

//返回count
$count = $article->getCount($filter);
