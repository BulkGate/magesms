<?php
namespace Topefekt\Magesms\Bulkgate;


use BulkGate\Extensions\Database\IDatabase;

class Customers extends \BulkGate\Extensions\Customers
{
	private $cache;

	public function __construct(IDatabase $db)
	{
		parent::__construct($db);
		$this->table_user_key = 'entity_id';
	}

	protected function loadCustomers(array $customers, $limit = null)
	{
		$collection = $this->getCustomerCollection();
		if (!empty($customers)) {
			$collection->addFilter('entity_id', $customers);
		}
		$collection->getSelect()->limit($limit);
		return $collection->getData();
	}

	protected function filter(array $filters)
	{
		return '';
	}

	protected function getTotal()
	{
		return $this->getCustomerCollection()->count();
	}

	protected function getFilteredTotal(array $customers)
	{
		return '';
	}

	public function getCustomerCollection() {
		if ($this->cache) {
			return $this->cache;
		}
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

		/** @var Smsprofile $profile */
/*		$profile = $objectManager->get(Smsprofile::class);
		if ($profile->user->getPrefbilling()) {
			if ($attr->getId())
				$collection->getSelect()
					->columns('IF(`at_shipping_telephone`.`telephone`, `at_shipping_telephone`.`telephone`, IF(`at_billing_telephone`.`telephone`, `at_billing_telephone`.`telephone`, `at_mobile`.`value`)) AS telephone');
			else
				$collection->getSelect()
					->columns('IF(`at_shipping_telephone`.`telephone`, `at_shipping_telephone`.`telephone`, `at_billing_telephone`.`telephone`) AS telephone');
			$collection->getSelect()
				->columns('IF(`at_billing_country_id`.`country_id`, `at_billing_country_id`.`country_id`, `at_shipping_country_id`.`country_id`) AS country_id');
		} else {*/
			if ($attr->getId())
				$collection->getSelect()
					->columns('IF(`at_billing_telephone`.`telephone`, `at_billing_telephone`.`telephone`, IF(`at_shipping_telephone`.`telephone`, `at_shipping_telephone`.`telephone`, `at_mobile`.`value` )) AS telephone');
			else
				$collection->getSelect()
					->columns('IF(`at_billing_telephone`.`telephone`, `at_billing_telephone`.`telephone`, `at_shipping_telephone`.`telephone`) AS telephone');
			$collection->getSelect()
				->columns('IF(`at_shipping_country_id`.`country_id`, `at_shipping_country_id`.`country_id`, `at_billing_country_id`.`country_id`) AS country_id');
//		}

		return $this->cache = $collection;
		return $this->db->execute("
            SELECT      `user_id` AS `order`,
                        MAX(CASE WHEN meta_key = 'billing_first_name' AND meta_value IS NOT NULL THEN meta_value ELSE (CASE WHEN meta_key = 'first_name' THEN  meta_value ELSE (CASE WHEN meta_key = 'shipping_first_name' THEN  meta_value END) END) END) first_name,
                        MAX(CASE WHEN meta_key = 'billing_last_name' AND meta_value IS NOT NULL THEN meta_value ELSE (CASE WHEN meta_key = 'last_name' THEN  meta_value ELSE (CASE WHEN meta_key = 'shipping_last_name' THEN  meta_value END)  END) END) last_name,
                        MAX(CASE WHEN meta_key = 'billing_phone' THEN meta_value END) phone_mobile,
                        MAX(CASE WHEN meta_key = 'billing_company' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_company' THEN  meta_value END) END) company_name,
                        MAX(CASE WHEN meta_key = 'billing_country' THEN LOWER(meta_value) ELSE (CASE WHEN meta_key = 'shipping_country' THEN  LOWER(meta_value) END)  END) country,
                        MAX(CASE WHEN meta_key = 'billing_address_1' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_address_1' THEN  meta_value END) END) street1,
                        MAX(CASE WHEN meta_key = 'billing_address_2' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_address_2' THEN  meta_value END) END) street2,
                        MAX(CASE WHEN meta_key = 'billing_postcode' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_postcode' THEN  meta_value END) END) zip,
                        MAX(CASE WHEN meta_key = 'billing_city' THEN meta_value ELSE (CASE WHEN meta_key = 'shipping_city' THEN  meta_value END) END) city,
                        MAX(CASE WHEN meta_key = 'billing_email' THEN meta_value END) email
            FROM `{$this->db->table('usermeta')}`
            ". (count($customers) > 0 ? "WHERE `user_id` IN ('".implode("','", $customers)."') " : "") . "
            GROUP BY `user_id`
            HAVING `phone_mobile` NOT LIKE '' 	
            ". ($limit !== null ? "LIMIT $limit" : ""))->getRows();
	}


}