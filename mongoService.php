​<?php
    require_once('./models/articleModel.php');

    class MongoService
    {
        private $_manager;
        private $_db;

        public function connect($config)
        {
            if (empty($config)) {
                throw new Exception('config is null', 500);
            }
            if ($config == 'why') {
                $this->_host = '127.0.0.1';
                $this->_username = 'why';
                $this->_password = 'why123';
                $this->_db = 'why_db';
                $mongo = "mongodb://why:why123@127.0.0.1/";
            } else {
                throw new Exception('config is error', 500);
            }

            return $this->_manager = new \MongoDB\Driver\Manager($mongo);
        }

        public function getDB()
        {
            return $this->_db;
        }

        public function setDB($db)
        {
            $this->_db = $db;
            return $this->_db;
        }

        public function getBulk()
        {
            return new \MongoDB\Driver\BulkWrite;
        }

        public function getWriteConcern()
        {
            return new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        }

        /**
         * 插入数据
         * @param $db 数据库名
         * @param $collection 集合名
         * @param $document 数据 array格式
         * @return
         */
        public function insert($collection, $document)
        {
            $bulk = $this->getBulk();
            if (count($document) == 1) {
                $document['_id'] = new \MongoDB\BSON\ObjectID;
                $bulk->insert($document);
            } else {
                foreach ($document as $val) {
                    $val['_id'] = new \MongoDB\BSON\ObjectID;
                    $bulk->insert($val);
                }
            }
            $res = $this->_manager->executeBulkWrite($this->_db . '.' . $collection, $bulk);
            if (empty($res->getWriteErrors())) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * 删除数据
         * @param array $where
         * @param array $option
         * @param string $db
         * @param string $collection
         * @return mixed
         */
        public function delete($collection, $where = array(), $option = array())
        {
            $bulk = $this->getBulk();
            $bulk->delete($where, $option);
            return $this->_manager->executeBulkWrite($this->_db . $collection, $bulk);
        }

        /**
         * 更新数据
         * @param array $where 类似where条件
         * @param array $field  要更新的字段
         * @param bool $upsert 如果不存在是否插入，默认为false不插入
         * @param bool $multi 是否更新全量，默认为false
         * @param string $db   数据库
         * @param string $collection 集合
         * @return mixed
         */
        public function update($collection, $where = array(), $field = array(), $upsert = false, $multi = false)
        {
            if (empty($where)) {
                return 'filter is null';
            }
            $bulk = $this->getBulk();
            $write_concern = $this->getWriteConcern();
            if (isset($where['_id'])) {
                $where['_id'] = new \MongoDB\BSON\ObjectId($where['_id']);
            }
            $bulk->update($where, array('$set' => $field), array('multi' => $multi, 'upsert' => $upsert));
            $res = $this->_manager->executeBulkWrite($this->_db . '.' . $collection, $bulk, $write_concern);
            if (empty($res->getWriteErrors())) {
                return true;
            } else {
                return false;
            }
        }

        public function selectById($collection, $id, $options = array())
        {
            //$filter = ['_id' => new \MongoDB\BSON\ObjectID($id)];
            //return $this->query($collection, $filter, $options);
            $filter = ['_id' => new \MongoDB\BSON\ObjectID($id)];
            $res = $this->query($collection, $filter, $options);
            foreach ($res as $item) {
                $data = $this->objToArray($item);
            }
            return $data;
        }

        public function query($collection, $filter, $options)
        {
            $query = new \MongoDB\Driver\Query($filter, $options);
            $res = $this->_manager->executeQuery($this->_db . '.' . $collection, $query);
            $data = array();
            foreach ($res as $item) {
                $tmp = $this->objToArray($item);
                $tmp['id'] = $tmp['_id']['$oid'];
                unset($tmp['_id']);
                $data[] = $tmp;
            }
            return $data;
        }

        /**
         * 执行MongoDB命令
         * @param array $param
         * @return \MongoDB\Driver\Cursor
         */
        public function command($param)
        {
            $cmd = new \MongoDB\Driver\Command($param);
            return $this->_manager->executeCommand($this->_db, $cmd);
        }

        /**
         * 按条件计算个数
         *
         * @param string $collName 集合名
         * @param array $where 条件
         * @return int
         */
        function count($collName, array $where)
        {
            $result = 0;
            $cmd = [
                'count' => $collName,
                'query' => $where
            ];
            $arr = $this->command($cmd)->toArray();
            if (!empty($arr)) {
                $result = $arr[0]->n;
            }
            return $result;
        }

        public function objToArray($obj)
        {
            return json_decode(json_encode($data), true);
        }

        public function getCol($col)
        {
            if (in_array($col, ['article'])) {
                $this->connect('why');
            } else {
                return 'collection error';
            }
            $col = ucwords($col);

            return new $col($this);
        }
    }

    ?>