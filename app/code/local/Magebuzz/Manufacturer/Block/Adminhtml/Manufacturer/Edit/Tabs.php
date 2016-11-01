<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Adminhtml_Manufacturer_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
  public function __construct()
  {
    parent::__construct();
    $this->setId('manufacturer_tabs');
    $this->setDestElementId('edit_form');
    $this->setTitle(Mage::helper('manufacturer')->__('Manufacturer Information'));
  }

  protected function _beforeToHtml()
  {
    $this->addTab('form_section', array(
      'label'   => Mage::helper('manufacturer')->__('Manufacturer Information'),
      'title'   => Mage::helper('manufacturer')->__('Manufacturer Information'),
      'content' => $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit_tab_form')->toHtml(),
    ));

    $this->addTab('manufacturer_products', array(
      'label' => Mage::helper('manufacturer')->__('Manufacturer Products'),
      'title' => Mage::helper('manufacturer')->__('Manufacturer Products'),
      'class' => 'ajax',
      'url'   => $this->getUrl('adminhtml/magebuzz_manufacturer/productlist', array('_current' => TRUE, 'id' => $this->getRequest()->getParam('id'))),
    ));
    $this->addTab('manufacturer_meta_data', array(
      'label'   => Mage::helper('manufacturer')->__('Meta Data'),
      'title'   => Mage::helper('manufacturer')->__('Meta Data'),
      'content' => $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit_tab_meta')->toHtml(),
    ));
    return parent::_beforeToHtml();
  }
}
