<?php

/**
 * @category Augustash FreightRandLCarrier
 * @package Augustash_FreightRandLCarrier
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */
namespace Augustash\FreightRandLCarrier\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var string
     */
    private $entityTypeId;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->entityTypeId = 'catalog_product';
    }

    /**
     * Setup
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_hazmat',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Hazmat',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_freezable',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Must ship refrigerated',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_not_freezable',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Must not freeze',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_sort_segregate',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Sort and segregate',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'is_over_dimensions',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Oversized',
                'input' => 'boolean',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        /**
         * Setup shipping group and install attribute
         */
        $setId = $eavSetup->getDefaultAttributeSetId($this->entityTypeId);

        // Create shipping attribute group
        $eavSetup->addAttributeGroup($this->entityTypeId, $setId, 'Shipping');

        // Get shipping attribute group id
        $groupId = $eavSetup->getAttributeGroupId($this->entityTypeId, $setId, 'Shipping');

        // Add attribute to default set, shipping group
        $attributeCodes = [
            'is_hazmat',
            'is_freezable',
            'is_not_freezable',
            'is_sort_segregate',
            'is_over_dimensions'
        ];

        foreach ($attributeCodes as $code) {
            $attributeId = $eavSetup->getAttribute($this->entityTypeId, $code);

            $eavSetup->addAttributeToGroup($this->entityTypeId, $setId, $groupId, $attributeId['attribute_id']);
        }
    }

    /**
     * Uninstall
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     */
    public function uninstall(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $attributeCodes = [
            'is_hazmat',
            'is_freezable',
            'is_not_freezable',
            'is_sort_segregate',
            'is_over_dimensions'
        ];

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach ($attributeCodes as $code) {
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $code
            );
        }
    }
}
