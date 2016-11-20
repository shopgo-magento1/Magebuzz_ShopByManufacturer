<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Manufacturer extends Mage_Core_Block_Template
{
  public function _prepareLayout()
  {
    return parent::_prepareLayout();
  }

  public function getManufacturers()
  {
    $store_id = Mage::app()->getStore(TRUE)->getId();
    $manufacturerIds = Mage::getModel('manufacturer/manufacturer')->getResource()->getManufacturerId($store_id);
    if (count($manufacturerIds)) {
      $collection = Mage::getModel('manufacturer/manufacturer')->getCollection()
        ->addFieldToFilter('manufacturer_id', $manufacturerIds)
        ->addFieldToFilter('status', 1);
      if (Mage::helper('manufacturer')->isAlphabetOrder()) {
        $collection->setOrder('name', 'ASC');
      }
      return $collection;
    }
    return FALSE;
  }

  public function displayImage()
  {
    return Mage::helper('manufacturer')->displayImage();
  }

  public function groupAlphabet()
  {
    return Mage::helper('manufacturer')->groupAlphabet();
  }

  public function getFirstLetter($name)
  {
    return Mage::helper('manufacturer')->getFirstLetter($name);
  }

  public function showTopNavigation()
  {
    return Mage::helper('manufacturer')->showTopNavigation();
  }

  public function getBrandView()
  {
    return Mage::helper('manufacturer')->getBrandView();
  }

  public function getAvailableLetter($collection)
  {
    $prev = '';
    $letters = array();
    foreach ($collection as $manufacturer) {
      $current = $this->getFirstLetter($manufacturer->getName());
      if ($current != $prev) {
        $letters[] = $current;
        $prev = $current;
      }
    }
    return $letters;
  }

  public function getManufacturersByFirstLetter($letter)
  {
    $collection = Mage::getModel('manufacturer/manufacturer')->getCollection()
      ->addFieldToFilter('status', 1)
      ->addFieldToFilter('name', array('like' => $letter . '%'));
    return $collection;
  }

  public function showFeaturedManufacturers()
  {
    return Mage::helper('manufacturer')->showFeaturedManufacturers();
  }

  public function getFeaturedImage($image)
  {
    if ($image == '') {
      return Mage::helper('manufacturer')->getDefaultManufacturerImage();
    } else {
      return Mage::helper('manufacturer')->getManufacturerImage($image);
    }
  }

  public function getManufacturerImageListingUrl($image)
  {
    return Mage::helper('manufacturer')->getManufacturerImageListingUrl($image);
  }

  public function getManufacturerUrl($id)
  {
    $manufacturer = Mage::getModel('manufacturer/manufacturer')->load($id);
    return $this->getUrl() . $this->_helperMenufacturer()->getConfigTextRouter() . '/' . $manufacturer->getIdentifier();
  }

  public function getFeaturedManufacturers()
  {
    $collection = $this->getManufacturers();
    if ($collection) {
      $collection->addFieldToFilter('is_featured', 1);
    }
    return $collection;
  }

  protected function _helperMenufacturer()
  {
    return Mage::helper('manufacturer');
  }
}
