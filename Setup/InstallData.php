<?php
/**
 * @category Augustash FreightBase
 * @package Augustash_FreightBase
 * @copyright Copyright (c) 2017 Augustash
 * @author Augustash Team <changes@augustash.com>
 */

namespace Augustash\FreightBase\Setup;

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

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->entityTypeId = 'catalog_product';
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'freight_class',
            [
                'group' => 'Shipping',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Freight Class',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => '55',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple'
            ]
        );

        /**
         * Setup additional attributes
         * Add to Shipping group
         */
        
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'must_ship_freight',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Must ship freight',
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
            'declared_value',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Declared Value',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple',
                'note' => 'Estimated value for shipping insurance calculations.'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'freight_length',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Freight length',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'unique' => false,
                'apply_to' => 'simple',
                'note' => 'Length of product in inches, number only. Example: 5\' would be 60, 5\' 2" would be 62'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'freight_width',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Freight width',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
                'note' => 'Width of product in inches, number only. Example: 5\' would be 60, 5\' 2" would be 62'
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'freight_height',
            [
                'group' => 'Shipping',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Freight height',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'is_user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => true,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => 'simple',
                'note' => 'Height of product in inches, number only. Example: 5\' would be 60, 5\' 2" would be 62'
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
            'freight_class',
            'must_ship_freight',
            'declared_value',
            'freight_length',
            'freight_width',
            'freight_height'
        ];

        foreach($attributeCodes as $code) {
            $attributeId = $eavSetup->getAttribute($this->entityTypeId, $code);

            $eavSetup->addAttributeToGroup($this->entityTypeId, $setId, $groupId, $attributeId['attribute_id']);
        }
    }

    public function uninstall(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $attributeCodes = [
            'freight_class',
            'must_ship_freight',
            'declared_value',
            'freight_length',
            'freight_width',
            'freight_height'
        ];

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        foreach($attributeCodes as $code) {
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $code
            );
        }
    }
}