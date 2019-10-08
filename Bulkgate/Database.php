<?php
namespace BulkGate\Magesms\Bulkgate;

use BulkGate\Magesms\Extensions;
use PDO;

/**
 * Class DatabaseInterface
 * @package BulkGate\Magesms\Bulkgate
 */
class Database extends Extensions\Strict implements Extensions\Database\DatabaseInterface
{
    private $db;
    private $sql = [];
    private $_objectManager;

    public function __construct()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $db */
        $db = $resource->getConnection();
        $this->db = $db;
    }

    public function execute($sql)
    {
        $output = [];
        $this->sql[] = $sql;
        $result = $this->db->query($sql);
        if ($result && $result->rowCount()) {
            try {
                $output = $result->fetchAll(PDO::FETCH_OBJ);
            } catch (\Exception $e) {
                $output = [];
            }
        }
        return new Extensions\Database\Result($output);
    }

    public function prepare($sql, array $params = [])
    {
        $params = array_map([$this->db, 'quote'], $params);
        return vsprintf($sql, $params);
    }

    public function lastId()
    {
        return $this->db->lastInsertId();
    }

    public function escape($string)
    {
        return str_replace(["'", '"'], ["\'", '\"'], $string);
    }

    public function prefix()
    {
        return '';
    }

    public function table($table)
    {
        return $this->db->getTableName($table);
    }

    public function getSqlList()
    {
        return $this->sql;
    }
}
