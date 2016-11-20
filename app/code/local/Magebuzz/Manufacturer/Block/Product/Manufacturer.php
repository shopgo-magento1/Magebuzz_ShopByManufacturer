<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Product_Manufacturer extends Mage_Catalog_Block_Product_View
{
  protected function _prepareLayout()
  {
    return parent::_prepareLayout();
  }

  public function showManufacturerInProduct()
  {
    return Mage::helper('manufacturer')->showManufacturerInProduct();
  }

  public function getManufacturerByProductId($optionId)
  {
    $manufacturer = Mage::getModel('manufacturer/manufacturer')->loadByOptionId($optionId);
    return $manufacturer;
  }

  public function getBrandViewProduct()
  {
    return Mage::helper('manufacturer')->getBrandViewProduct();
  }
}