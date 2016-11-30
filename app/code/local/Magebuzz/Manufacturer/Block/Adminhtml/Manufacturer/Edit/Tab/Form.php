<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Adminhtml_Manufacturer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
    $form = new Varien_Data_Form();
    $this->setForm($form);
    $fieldset = $form->addFieldset('manufacturer_form', array('legend' => Mage::helper('manufacturer')->__('Item information')));
    $manufacturer_data = array();
    $model = Mage::registry('manufacturer_data');
    if (Mage::getSingleton('adminhtml/session')->getManufacturerData()) {
      $manufacturer_data = Mage::getSingleton('adminhtml/session')->getManufacturerData();
      Mage::getSingleton('adminhtml/session')->setManufacturerData(null);
    } elseif (Mage::registry('manufacturer_data')) {
      $manufacturer_data = Mage::registry('manufacturer_data')->getData();
    }

    if (isset($manufacturer_data['image']) && $manufacturer_data['image'] != '') {
      $manufacturer_data['image'] = 'manufacturer/' . $manufacturer_data['image'];
    }
    if (!isset($manufacturer_data['manufacturer_id']) || ($manufacturer_data['manufacturer_id'] == null)) {
      $fieldset->addField('name', 'text', array(
        'label'    => Mage::helper('manufacturer')->__('Name'),
        'class'    => 'required-entry',
        'required' => TRUE,
        'name'     => 'name',
      ));
    } else {
      $fieldset->addField('name', 'text', array(
        'label'    => Mage::helper('manufacturer')->__('Name'),
        'class'    => 'required-entry',
        'required' => TRUE,
        'name'     => 'name',
//        'readonly' => 'readonly',
      ));
    }
    $fieldset->addField('identifier', 'text', array(
      'label'    => Mage::helper('manufacturer')->__('URL Identifier'),
      'class'    => 'required-entry',
      'required' => TRUE,
      'name'     => 'identifier',
      /* 'readonly'	=> 'readonly', */
    ));
    $fieldset->addField('image', 'image', array(
      'label'    => Mage::helper('manufacturer')->__('Image'),
      'required' => FALSE,
      'name'     => 'image',
    ));
	  /*if (!Mage::app()->isSingleStoreMode()) {
		  $fieldset->addField('store_id', 'multiselect', array(
			  'name' => 'stores[]',
			  'label' => Mage::helper('manufacturer')->__('Store View'),
			  'title' => Mage::helper('manufacturer')->__('Store View'),
			  'required' => true,
			  'values' => Mage::getSingleton('adminhtml/system_store')
				  ->getStoreValuesForForm(false, true),
		  ));
	  }
	  else {
		  $fieldset->addField('store_id', 'hidden', array(
			  'name' => 'stores[]',
			  'value' => Mage::app()->getStore(true)->getId()
		  ));
	  }*/
    $fieldset->addField('website', 'text', array(
      'label'    => Mage::helper('manufacturer')->__('Website'),
      'required' => FALSE,
      'name'     => 'website',
    ));
    $fieldset->addField('is_featured', 'select', array(
      'label'  => Mage::helper('manufacturer')->__('Featured Manufacturer'),
      'name'   => 'is_featured',
      'values' => array(
        array(
          'value' => 1,
          'label' => Mage::helper('manufacturer')->__('Yes'),
        ),
        array(
          'value' => 0,
          'label' => Mage::helper('manufacturer')->__('No'),
        ),
      ),
    ));

    $fieldset->addField('status', 'select', array(
      'label'  => Mage::helper('manufacturer')->__('Status'),
      'name'   => 'status',
      'class'   => 'required-entry validate-select',
      'values' => array(
        array(
          'value' => 1,
          'label' => Mage::helper('manufacturer')->__('Enabled'),
        ),
        array(
          'value' => 2,
          'label' => Mage::helper('manufacturer')->__('Disabled'),
        ),
      ),
    ));

    $fieldset->addField('description', 'editor', array(
      'name'     => 'description',
      'label'    => Mage::helper('manufacturer')->__('Description'),
      'title'    => Mage::helper('manufacturer')->__('Description'),
      'style'    => 'width:700px; height:150px;',
      'wysiwyg'  => FALSE,
      'required' => FALSE,
    ));

    $form->setValues($manufacturer_data);
    return parent::_prepareForm();
  }
}