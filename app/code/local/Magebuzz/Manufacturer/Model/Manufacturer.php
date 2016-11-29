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

  public function compareProductList($newarray, $oldarray, $manufaturer_option)
  {
    $insert = array_diff($newarray, $oldarray);
		$delete = array_diff($oldarray, $newarray);
    $resource = Mage::getSingleton('core/resource');
    if (isset($newarray)) {
      if (count($delete)) {
        $manufac_attribute_code_del = $this->_hepper()->getConfigAttributrCode();
        foreach ($delete as $del) {
          $product = Mage::getModel('catalog/product')->load($del);
          $product->setData($manufac_attribute_code_del,null)->save();
        }
      }
      if (count($insert)) {
        $manufac_attribute_code = $this->_hepper()->getConfigAttributrCode();
        foreach ($insert as $pid) {
          $product = Mage::getModel('catalog/product')->load($pid);
          $product->getResource()->getAttribute($manufac_attribute_code);
          $product->setData($manufac_attribute_code,$manufaturer_option)->save();
        }
      }
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