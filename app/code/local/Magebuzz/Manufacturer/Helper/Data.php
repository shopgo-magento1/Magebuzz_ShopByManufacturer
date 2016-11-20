<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/
class Magebuzz_Manufacturer_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getManufacturerOptions()
  {
    $attributeId = Mage::getResourceModel('eav/entity_attribute')
      ->getIdByCode('catalog_product', $this->getConfigAttributrCode());
    $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
    $attributeOptions = $attribute->getSource()->getAllOptions();
    return $attributeOptions;
  }

  public function generateIdentifier($string)
  {
    $identifier = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($string));
    $identifier = strtolower($identifier);
    $identifier = trim($identifier, '-');
    return $identifier;
  }

  public function getOptionById($option_id)
  {
    $option = Mage::getModel('manufacturer/manufacturer')->getCollection()
      ->addFieldToFilter('option_id', $option_id)
      ->getFirstItem();
    if ($option) return $option->getId();
    return FALSE;
  }

  public function getManufacturerOptionById($option_id)
  {
    $manufacturer_options = $this->getManufacturerOptions();
    if (is_array($manufacturer_options)) {
      foreach ($manufacturer_options as $option) {
        if ($option_id == $option['value']) {
          return TRUE;
        }
      }
      return FALSE;
    }
  }

  public function renameImage($image_name)
  {
    $string = str_replace("  ", " ", $image_name);
    $new_image_name = str_replace(" ", "-", $string);
    $new_image_name = strtolower($new_image_name);
    return $new_image_name;
  }

  public function isAlphabetOrder()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/alphabet_sorting');
  }

  public function displayImage()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/show_image');
  }

  public function groupAlphabet()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/group_alphabet');
  }

  public function getFirstLetter($name)
  {
    $letter = '';
    $name = trim($name);
    $letter = strtoupper(substr($name, 0, 1));
    return $letter;
  }

  public function showFeaturedManufacturers()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/show_featured_manufacturer');
  }

  public function showTopNavigation()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/show_top_nav');
  }

  public function getBrandView()
  {
    return Mage::getStoreConfig('manufacturer/general/brand_view');
  }

  public function getBrandViewProduct()
  {
    return Mage::getStoreConfig('manufacturer/general/brand_view_product');
  }

  public function getManufacturerImageListingUrl($image)
  {
    if (empty($image) || $image == '') return '';
    $image_url = Mage::getBaseDir('media') . DS . 'manufacturer' . DS . $image;
    $imageResized = Mage::getBaseDir('media') . DS . 'manufacturer' . DS . 'resized' . DS . $image;
    if (!file_exists($imageResized) && file_exists($image_url)) {
      $imageObj = new Varien_Image($image_url);
      $imageObj->constrainOnly(TRUE);
      $imageObj->keepAspectRatio(TRUE);
      $imageObj->keepFrame(FALSE);
      $imageObj->resize(80, 80);
      $imageObj->save($imageResized);
    }
    return Mage::getBaseUrl('media') . 'manufacturer' . '/resized/' . $image;
  }

  public function resizeManufacturerImage($image, $width = null, $height = null)
  {
    if (empty($image) || $image == '') return '';
    $image_url = Mage::getBaseDir('media') . DS . 'manufacturer' . DS . $image;
    $imageResized = Mage::getBaseDir('media') . DS . 'manufacturer' . DS . 'resized' . DS . $image;
    if (!file_exists($imageResized) && file_exists($image_url)) {
        $imageObj = new Varien_Image($image_url);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(FALSE);
        $imageObj->resize($width, $height);
        $imageObj->save($imageResized);
    }
    return Mage::getBaseUrl('media') . 'manufacturer' . '/resized/' . $image;
  }

  public function getManufacturerImage($image)
  {
    if (empty($image) || $image == '') {
      $default = $this->getDefaultManufacturerImage();
      return $default;
    } else {
      return Mage::getBaseUrl('media') . 'manufacturer' . '/' . $image;
    }
  }

  public function getDefaultManufacturerImage()
  {
    $image = Mage::getStoreConfig('manufacturer/general/default_manufacturer_image');
    if ($image != "") {
      return Mage::getBaseUrl('media') . 'manufacturer/default' . '/' . $image;
    }
    return '';
  }

  public function showManufacturerInProduct()
  {
    return (bool)Mage::getStoreConfig('manufacturer/general/show_manufacturer_in_product_page');
  }

  public function getSelectedProducts($manufacturer_id)
  {
    $products = array();
    $productIds = Mage::getModel('manufacturer/manufacturer')->load($manufacturer_id)->getSelectedProductIds();
    foreach ($productIds as $productId) {
      $products[$productId] = array('position' => 0);
    }
    return $products;
  }

  public function getConfigAttributrCode()
  {
    return Mage::getStoreConfig('manufacturer/configfield/text_attribute_code');
  }

  public function getConfigTextLabe()
  {
    return Mage::getStoreConfig('manufacturer/configfield/text_manufacturer_lable');
  }

  public function getConfigTextFeatured()
  {
    return Mage::getStoreConfig('manufacturer/configfield/text_featured_manufacturer_lable');
  }

  public function getConfigTextAll()
  {
    return Mage::getStoreConfig('manufacturer/configfield/text_all_manufacturer_lable');
  }

  public function getConfigTextRouter()
  {
    return strtolower(Mage::getStoreConfig('manufacturer/configfield/text_router'));
  }

  public function getManufacturerByIdentifier($idenfier)
  {
    $manufacturer = Mage::getModel('manufacturer/manufacturer')->getCollection()->AddFieldToFilter('identifier', $idenfier);
    foreach ($manufacturer as $facturer) {
      $idIdenfier = $facturer->getManufacturerId();
    }
    return $idIdenfier;
  }

  public function getLinkExtenal($string){

    $link = preg_replace('/((http|ftp|https):\/\/)/', '', Mage::helper('catalog/product_url')->format($string));
    return $link;
  }
}
