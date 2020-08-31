<?php


namespace Magenest\Staff\Setup\Patch\Data;


use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddStaffTypeAttribute implements DataPatchInterface, PatchRevertableInterface
{
    protected $moduleDataSetup;
    protected $eavSetupFactory;
    protected $logger;
    protected $eavConfig;
    protected $attributeResource;

    public function __construct(EavSetupFactory $eavSetupFactory,
                                Config $eavConfig,
                                LoggerInterface $logger,
                                \Magento\Customer\Model\ResourceModel\Attribute $attributeResource,
                                \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
        $this->attributeResource = $attributeResource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->addAvatarAttribute();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function addAvatarAttribute()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'staff_type',
            ['type' => 'int',
            'label' => 'Magenest Staff',
            'input' => 'select',
            "source" => 'Magenest\Staff\Model\Config\Source\ShowStaffType',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'sort_order' => 999,
            'position' => 999,
            'system' => false,
        ]);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId(Customer::ENTITY);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(Customer::ENTITY);

        $attribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'staff_type');
        $attribute->setData('attribute_set_id', $attributeSetId);
        $attribute->setData('attribute_group_id', $attributeGroupId);

        $attribute->setData('used_in_forms', [
            'adminhtml_customer', 'customer_account_create', 'customer_account_edit'
        ]);

        $this->attributeResource->save($attribute);
    }

    public static function getDependencies()
    {
        // TODO: Implement getDependencies() method.
        return [];
    }

    public function getAliases()
    {
        // TODO: Implement getAliases() method.
        return [];
    }

    public function revert()
    {
        // TODO: Implement revert() method.
    }
}
