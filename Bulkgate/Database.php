<?php
namespace BulkGate\Magesms\Bulkgate;

use Bulkgate\Extensions\Database\Result;
use BulkGate\Extensions\Exception;
use Bulkgate\Extensions\Strict;
use Bulkgate\Extensions\Database\IDatabase;
use PDO;

class Database extends Strict implements IDatabase
{
    private $db;
    private $sql = [];

    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
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
        return new Result($output);
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
        return addslashes($string);
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
