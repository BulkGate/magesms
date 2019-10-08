<?php
namespace BulkGate\Magesms\Extensions;

use DateTime;

/**
 * Class Settings
 * @package BulkGate\Magesms\Extensions
 */
class Settings extends Strict implements SettingsInterface
{
    /** @var array */
    public $data = [];

    /** @var Database\DatabaseInterface */
    private $db;

    public function __construct(Database\DatabaseInterface $database)
    {
        $this->db = $database;
    }

    public function load($settings_key, $default = false)
    {
        list($scope, $key) = Key::decode($settings_key);

        if (isset($this->data[$scope])) {
            if (isset($this->data[$scope][$key])) {
                return $this->data[$scope][$key];
            }
            if (!isset($this->data[$scope][$key]) && $key !== null) {
                return $default;
            }
            return $this->data[$scope];
        }
        $result = $this->db->execute(
            $this->db->prepare(
                'SELECT * FROM `' . $this->db->table('bulkgate_module') .
                '` WHERE `scope` = %s AND `synchronize_flag` != "delete" ORDER BY `order`',
                [
                    $scope
                ]
            )
        );

        if ($result->getNumRows() > 0) {
            foreach ($result as $item) {
                switch ($item->type) {
                    case "text":
                        $this->data[$scope][$item->key] = (string)$item->value;
                        break;
                    case "int":
                        $this->data[$scope][$item->key] = (int)$item->value;
                        break;
                    case "float":
                        $this->data[$scope][$item->key] = (float)$item->value;
                        break;
                    case "bool":
                        $this->data[$scope][$item->key] = (bool)$item->value;
                        break;
                    case "json":
                        try {
                            $this->data[$scope][$item->key] = Json::decode($item->value);
                        } catch (Exceptions\JsonException $e) {
                            $this->data[$scope][$item->key] = null;
                        }

                        break;
                }
            }
        } else {
            $this->data[$scope] = false;
        }
        return $this->load($settings_key);
    }

    public function set($settings_key, $value, array $meta = [])
    {
        if (!isset($meta['datetime'])) {
            $meta['datetime'] = time();
        }

        list($scope, $key) = Key::decode($settings_key);

        $result = $this->db->execute(
            $this->db->prepare(
                'SELECT * FROM `' . $this->db->table('bulkgate_module') . '` WHERE `scope` = %s AND `key` = %s',
                [
                    $scope, $key
                ]
            )
        );

        if ($result->getNumRows() > 0) {
            $this->db->execute(
                $this->db->prepare(
                    'UPDATE `' . $this->db->table('bulkgate_module') . '` SET value = %s, `datetime` = %s ' .
                    $this->parseMeta($meta) . ' WHERE `scope` = %s AND `key` = %s',
                    [
                        $value, $meta['datetime'], $scope, $key
                    ]
                )
            );
        } else {
            $this->db->execute(
                $this->db->prepare(
                    'INSERT INTO `' . $this->db->table('bulkgate_module') . '` SET 
                        `scope`= %s,
                        `key`= %s,
                        `value`= %s' . $this->parseMeta($meta).'
                        ',
                    [
                        $scope, $key, $value
                    ]
                )
            );
        }
    }

    public function delete($settings_key = null)
    {
        if ($settings_key === null) {
            $this->db->execute('
                DELETE FROM `' . $this->db->table('bulkgate_module') . '` WHERE `synchronize_flag` = "delete"
            ');
        } else {
            list($scope, $key) = Key::decode($settings_key);

            $this->db->execute(
                $this->db->prepare('
                    DELETE FROM `' . $this->db->table('bulkgate_module') . '` WHERE `scope` = %s AND `key` = %s
                ', [
                    $scope, $key
                ])
            );
        }
    }

    public function synchronize()
    {
        $output = [];

        $result = $this->db->execute('SELECT * FROM `'.$this->db->table('bulkgate_module').
            '` WHERE `scope` != "static"')->getRows();

        foreach ($result as $row) {
            $output[$row->scope . ':' . $row->key] = $row;
        }

        return $output;
    }

    public function install()
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `".$this->db->table('bulkgate_module')."` (
              `scope` varchar(50) NOT NULL DEFAULT 'main',
              `key` varchar(50) NOT NULL,
              `type` varchar(50) NOT NULL DEFAULT 'text',
              `value` text NOT NULL,
              `datetime` bigint(20) DEFAULT NULL,
              `order` int(11) NOT NULL DEFAULT '0',
              `synchronize_flag` varchar(50) NOT NULL DEFAULT 'none',
              PRIMARY KEY (`scope`,`key`)
            ) DEFAULT CHARSET=utf8;
        ");
        $this->set('static:synchronize', 0, ['type' => 'int']);
    }

    public function uninstall()
    {
        if ($this->load('main:delete_db', false)) {
            $this->db->execute("DROP TABLE IF EXISTS `" . $this->db->table('bulkgate_module') . "`");
        }
    }

    private function parseMeta(array $meta)
    {
        $output = [];

        foreach ($meta as $key => $item) {
            switch ($key) {
                case 'type':
                    $output[] = $this->db->prepare('`type`= %s', [$this->checkType($item)]);
                    break;
                case 'datetime':
                    $output[] = $this->db->prepare('`datetime`= %s', [$this->formatDate($item)]);
                    break;
                case 'order':
                    $output[] = $this->db->prepare('`order`= %s', [(int)$item]);
                    break;
                case 'synchronize_flag':
                    $output[] = $this->db->prepare('`synchronize_flag`= %s', [$this->checkFlag($item)]);
                    break;
            }
        }
        return count($output) > 0 ? ','.implode(',', $output) : '';
    }

    private function formatDate($date)
    {
        if ($date instanceof DateTime) {
            return $date->getTimestamp();
        } elseif (is_string($date)) {
            return strtotime($date);
        } elseif (is_int($date)) {
            return $date;
        }
        return time();
    }

    private $types = ['text', 'int', 'float', 'bool', 'json'];

    private function checkType($type, $default = 'text')
    {
        if (in_array((string)$type, $this->types, true)) {
            return $type;
        }
        return $default;
    }

    private $flags = ['none', 'add', 'change', 'delete'];

    private function checkFlag($flag, $default = 'none')
    {
        if (in_array((string)$flag, $this->flags, true)) {
            return $flag;
        }
        return $default;
    }
}
