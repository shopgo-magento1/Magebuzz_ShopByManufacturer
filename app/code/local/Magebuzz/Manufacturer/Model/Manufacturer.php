<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Model_Manufacturer extends Mage_Core_Model_Abstract
{
  public function _construct()
  {
    parent::_construct();
    $this->_init('manufacturer/manufacturer');
  }

  protected function _afterLoad()
  {
    parent::_afterLoad();
    $this->setData('store_id', $this->getResource()->getStoreId($this->getId()));
  }

  public function getSelectedProductIds()
  {
    $produtIds = array();
    $collection = Mage::getModel('catalog/product')->getCollection()
      ->addFieldToFilter(Mage::helper('manufacturer')->getConfigAttributrCode(), $this->getOptionId());
    if (count($collection)) {
      foreach ($collection as $item) {
        $produtIds[] = $item->getEntityId();
      }
    }
    return $produtIds;
  }

  public function loadByOptionId($optionId)
  {
    $manufacturer = Mage::getModel('manufacturer/manufacturer')->getCollection()
      ->addFieldToFilter('option_id', $optionId)
      ->getFirstItem();
    return $manufacturer;
  }

  public function compareProductList($newarray, $oldarray, $manufacturerOption)
  {
    $insert = array_diff($newarray, $oldarray);
    $delete = array_diff($oldarray, $newarray);

    if (isset($newarray)) {
      if (count($delete)) {
        $this->updateManufacturerProducts($delete, null);
      }
      if (count($insert)) {
        $this->updateManufacturerProducts($insert, $manufacturerOption);
      }
    }
  }

  public function updateManufacturerProducts($products, $manufacturerOption)
  {
    $products = Mage::getModel('catalog/product')->getCollection()
      ->addAttributeToSelect('entity_id', 'type_id')
      ->addAttributeToFilter('entity_id', array('in', $products));

    if (!$products->getSize()) {
      return false;
    }

    $data = array();
    $columns = array(
      'entity_type_id',
      'attribute_id',
      'store_id',
      'entity_id',
      'value'
    );

    $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
    $manufacturerAttributeCode = $this->_hepper()->getConfigAttributrCode();
    $manufacturerAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode(
      'catalog_product',
      $manufacturerAttributeCode
    );

    foreach ($products as $product) {
      $data[] = array(
        $columns[0] => $entityTypeId,
        $columns[1] => $manufacturerAttributeId,
        $columns[2] => Mage_Core_Model_App::ADMIN_STORE_ID,
        $columns[3] => $product->getId(),
        $columns[4] => $manufacturerOption
      );
    }

    if ($data) {
      $resource = Mage::getSingleton('core/resource');
      $writeConnection = $resource->getConnection('core_write');
      $writeConnection->insertOnDuplicate('catalog_product_entity_int', $data, $columns);
    }
  }

  public function addManufacturerOption($opdata)
  {

    $attribute = Mage::getModel('eav/entity_attribute')
      ->loadByCode('catalog_product', $this->_hepper()->getConfigAttributrCode())->getAttributeId();
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    $attributeOption = array(
      'attribute_id' => $attribute,
      'sort_order'   => 0,
    );
    $writeConnection->insert('eav_attribute_option', $attributeOption);
    $lastInsertId = $writeConnection->lastInsertId();
    foreach ($opdata['store_id'] as $storeId) {
      $attOptionValue[] = array(
        'option_id' => $lastInsertId,
        'value'     => $opdata['manufacturerName'],
        'store_id'  => $storeId,
      );
    }
    if (isset($attOptionValue)) {
      $writeConnection->insertMultiple('eav_attribute_option_value', $attOptionValue);
    }
    return $lastInsertId;
  }

  public function deleteManufacturerOption($manufacturer)
  {
    $manufacturerModel = $this->load($manufacturer);
    $optionId = $manufacturerModel->getOptionId();
    $manufacturerAttributeCode = $this->_hepper()->getConfigAttributrCode();
    $manufacturerAttributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode(
      'catalog_product',
      $manufacturerAttributeCode
    );
    $resource = Mage::getSingleton('core/resource');
    $writeConnection = $resource->getConnection('core_write');
    $whereValue = 'eav_attribute_option_value.option_id = ' . $optionId;
    $writeConnection->delete('eav_attribute_option_value', $whereValue);
    $whereOption = 'eav_attribute_option.option_id = ' . $optionId;
    $writeConnection->delete('eav_attribute_option', $whereOption);
    $condition = array(
        'catalog_product_entity_int.value = ?' => (int) $optionId,
        'catalog_product_entity_int.attribute_id = ?' => (int) $manufacturerAttributeId
    );
    $writeConnection->delete('catalog_product_entity_int', $condition);
  }

  public function getAllManufacturer($listOption)
  {
    $collection = $this->getCollection()->getData();
    if (count($collection) > 0) {
      $listManufacturer = array();
      foreach ($collection as $manufacturer) {
        $listManufacturer[] = $manufacturer['option_id'];
      }
      $options = array();
      foreach ($listOption as $option) {
        $options[] = $option['value'];
      }
      $delete = array_diff($listManufacturer, $options);
      foreach ($delete as $del) {
        $this->load($del, 'option_id')->delete();
      }
    }
    return TRUE;
  }

  protected function _hepper()
  {
    return Mage::helper('manufacturer');
  }
}