<?php
namespace BulkGate\Magesms\Bulkgate;

use BulkGate\Extensions\Database\IDatabase;

class Customers extends \BulkGate\Extensions\Customers
{
    /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $cache */
    private $cache;
    private $customers = [];

    public function __construct(IDatabase $db)
    {
        parent::__construct($db);
        $this->table_user_key = 'entity_id';
    }

    protected function loadCustomers(array $customers, $limit = null)
    {
        $collection = $this->getCustomerCollection($customers);
        $collection->getSelect()->limit($limit);
        return $collection->getData();
    }

    private function getCondition($filter)
    {
        $cond = [];
        foreach ($filter['values'] as $value) {
            if (in_array($filter['type'], ['enum', 'string', 'float'], true)) {
                if ($value[0] === 'prefix') {
                    $cond[] = ['like' => '%' . $value[1] . '%'];
                } elseif ($value[0] === 'sufix') {
                    $cond[] = ['like' => '%' . $value[1]];
                } elseif ($value[0] === 'substring') {
                    $cond[] = ['like' => '%' . $value[1] . '%'];
                } elseif ($value[0] === 'empty') {
                    $cond[] = ['eq' => $value[1]];
                } elseif ($value[0] === 'filled') {
                    $cond[] = ['neq' => $value[1]];
                } elseif ($value[0] === 'is') {
                    $cond[] = ['eq' => $value[1]];
                } elseif ($value[0] === 'not') {
                    $cond[] = ['neq' => $value[1]];
                } elseif ($value[0] === 'gt') {
                    $cond[] = ['gt' => $value[1]];
                } elseif ($value[0] === 'lt') {
                    $cond[] = ['lt' => $value[1]];
                }
            } elseif ($filter['type'] === "date-range") {
                $cond[] = [
                    'from' => date('Y-m-d H:i:s', strtotime($value[1])),
                    'to' => date('Y-m-d H:i:s', strtotime($value[2]))
                ];
            }
        }
        return $cond;
    }
    
    protected function filter(array $filters)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $customers = [];
        $filtered = false;
        foreach ($filters as $key => $filter) {
            if (isset($filter['values']) && count($filter['values']) > 0 && !$this->empty) {
                /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
                $collection = $objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
                $collection->addNameToSelect();
                switch ($key) {
                    case 'firstname':
                        $collection->addFieldToFilter('firstname', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'lastname':
                        $collection->addFieldToFilter('lastname', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'country_id':
                        foreach ($filter['values'] as &$value) {
                            $value[1] = strtoupper($value[1]);
                        }
                        $collection->addNameToSelect()
                            ->joinAttribute('billing_country_id', 'customer_address/country_id',
                                'default_billing', null, 'left')
                            ->joinAttribute('shipping_country_id', 'customer_address/country_id',
                                'default_shipping', null, 'left');
                        $collection->getSelect()
                            ->columns('IFNULL(`at_shipping_country_id`.`country_id`, 
                                `at_billing_country_id`.`country_id`) AS country_id');

                        $collection->getSelect()->having($this->getSql($filter, 'country_id'));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'gender':
                        $collection->addFieldToFilter('gender', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'billing_city':
                        $collection
                            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
                            ->addFieldToFilter('billing_city', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'product':
                        if (strpos($collection->getSelect(), $collection->getTable('sales_order_grid')) === false) {
                            $collection->joinTable('sales_order_grid', 'customer_id=entity_id', ['entity_id']);
                        }
                        $sql = $resource->getConnection()
                            ->prepareSqlCondition(
                                'soi.sku', $this->getCondition($filter)
                            );
                        $collection->getSelect()
                            ->join(
                                ['soi' => $collection->getTable('sales_order_item')],
                                'soi.`order_id` = `'.$collection->getTable('sales_order_grid').'`.`entity_id` AND '.$sql
                            );
                        $collection->getSelect()->group('e.entity_id');
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'born_date':
                        $collection->addFieldToFilter('dob', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'newsletter':
                        if ($filter['values'][0][1] === 'no') {
                            $cond = 'ns.`subscriber_status` = 0 OR ns.`subscriber_status` IS NULL';
                        } else {
                            $cond = 'ns.`subscriber_status` = 1';
                        }

                        $collection->getSelect()
                            ->joinLeft(
                                ['ns' => $collection->getTable('newsletter_subscriber')],
                                'ns.`customer_id` = e.`entity_id`'
                            );
                        $collection->getSelect()
                            ->where($cond);
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'all_orders_amount':
                        if (strpos($collection->getSelect(), $collection->getTable('sales_order_grid')) === false)
                            $collection->joinTable('sales_order_grid', 'customer_id=entity_id', ['entity_id']);
                        $sql = $resource->getConnection()
                            ->prepareSqlCondition(
                                'orders_sum', $this->getCondition($filter)
                            );
                        $collection->getSelect()
                            ->columns('SUM('.$collection->getTable('sales_order_grid').'.`grand_total`) AS orders_sum')
                            ->having($sql)
                            ->group('e.entity_id');
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'registration_date':
                        $collection->addFieldToFilter('created_at', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'order_date':
                        if (strpos($collection->getSelect(), $collection->getTable('sales_order_grid')) === false)
                            $collection->joinTable('sales_order_grid', 'customer_id=entity_id', ['entity_id']);
                        $sql = $resource->getConnection()
                            ->prepareSqlCondition(
                                'created_at_sale', $this->getCondition($filter)
                            );
                        $collection->getSelect()
                            ->columns($collection->getTable('sales_order_grid').'.created_at AS created_at_sale')
                            ->group('e.entity_id');
                        $collection->getSelect()->having($sql);
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                    case 'type':
                        $collection->joinAttribute('billing_vat_id', 'customer_address/vat_id',
                            'default_billing', null, 'left')
                            ->addFieldToFilter('billing_vat_id', $this->getCondition($filter));
                        foreach ($collection as $item) {
                            $customers[] = $item->getId();
                        }
                        break;
                }
            }
            $filtered = true;

        }

        if (!$customers) {
            $this->empty = true;
        }
        return array(array_unique($customers), $filtered);
    }

    protected function getTotal()
    {
        return $this->getCustomerCollection()->count();
    }

    protected function getFilteredTotal(array $customers)
    {
//        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
//        $collection = $objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
//        return $collection->addFieldToFilter('entity_id', array('in' => $customers))->count();
        return $this->getCustomerCollection($customers)->count();
    }

    protected function getCustomerCollection(array $customers = []) {
        if ($this->cache && $this->customers === $customers) {
            return $this->cache;
        }
        $this->customers = $customers;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $objectManager->create(\Magento\Customer\Model\ResourceModel\Customer\Collection::class);
        $collection->addNameToSelect()
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('shipping_telephone', 'customer_address/telephone', 'default_shipping', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('shipping_country_id', 'customer_address/country_id', 'default_shipping', null, 'left');
        $filter = [
            [
                'attribute' => 'billing_telephone',
                [['notnull' => true], ['neq' => '']]
            ],
            [
                'attribute' => 'shipping_telephone',
                [['notnull' => true], ['neq' => '']]
            ]
        ];
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attrObj */
        $attrObj = $objectManager->get(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attr = $attrObj->loadByCode('customer', 'mobile');
        if ($attr->getId()) {
            $collection->joinAttribute('customer_mobile', 'customer/mobile', 'mobile', null, 'left');
            $filter[] = [
                'attribute' => 'mobile',
                [['notnull' => true], ['neq' => '']]
            ];
        }
        $collection->addFieldToFilter($filter);

        if ($attr->getId()) {
            $collection->getSelect()
                ->columns('IF(`at_billing_telephone`.`telephone`, `at_billing_telephone`.`telephone`, 
                    IF(`at_shipping_telephone`.`telephone`, `at_shipping_telephone`.`telephone`, 
                    `at_mobile`.`value` )) AS telephone');
        } else {
            $collection->getSelect()
                ->columns('IF(`at_billing_telephone`.`telephone`, `at_billing_telephone`.`telephone`, 
                    `at_shipping_telephone`.`telephone`) AS telephone');
        }
        $collection->getSelect()
            ->columns('IF(`at_shipping_country_id`.`country_id`, `at_shipping_country_id`.`country_id`, 
                `at_billing_country_id`.`country_id`) AS country_id');

        if ($customers) {
            $collection->addFieldToFilter('entity_id', ['in' => $customers]);
        }
        $collection->getSelect()
            ->columns('e.firstname AS first_name');
        $collection->getSelect()
            ->columns('e.lastname AS last_name');

        return $this->cache = $collection;
    }
}
