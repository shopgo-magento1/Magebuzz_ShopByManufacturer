<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Adminhtml_Manufacturer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_manufacturer';
    $this->_blockGroup = 'manufacturer';
    $this->_headerText = Mage::helper('manufacturer')->__('Manufacturer Manager');
    $this->_addButtonLabel = Mage::helper('manufacturer')->__('Add Manufacturer');
    /*$this->_addButton('import_from_magento', array(
      'label'   => Mage::helper('manufacturer')->__('Update Manufacturer from Magento'),
      'onclick' => 'setLocation(\'' . $this->_getImportUrl() . '\')',
      'class'   => 'add',
    ));*/

    $this->_addButton('reindex_url', array(
      'label'   => Mage::helper('manufacturer')->__('Reindex Manufacturer URL'),
      'onclick' => 'setLocation(\'' . $this->_getReindexUrl() . '\')',
      'class'   => ''
    ));

	  $this->_addButton('reindex_name',array(
		  'label' => Mage::helper('manufacturer')->__('Reindex Manufacturer Name'),
		  'onclick' => 'setLocation(\'' . $this->_getReindexName() . '\')',
		  ));

    parent::__construct();
  }

  protected function _getImportUrl()
  {
    return $this->getUrl('*/*/import', array('_secure' => TRUE));
  }

  protected function _getReindexUrl()
  {
    return $this->getUrl('*/*/reindex', array('_secure' => TRUE));
  }

	protected function _getReindexName(){
		return $this->getUrl('*/*/reindexname', array('_secure' => TRUE));
	}

  public function getNotification()
  {
    $helper = Mage::helper('manufacturer');
    $urls = Mage::getModel('core/url_rewrite')->getCollection()
      ->addFieldToFilter('request_path', array('like' => $helper->getConfigTextRouter() . '%'));
    if (!count($urls)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}