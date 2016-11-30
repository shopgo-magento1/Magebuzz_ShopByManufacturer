<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

	class Magebuzz_Manufacturer_Block_Adminhtml_Manufacturer_Grid extends Mage_Adminhtml_Block_Widget_Grid
	{
		const STATUS_ENABLED = Magebuzz_Manufacturer_Model_Status::STATUS_ENABLED;
		const STATUS_DISABLED = Magebuzz_Manufacturer_Model_Status::STATUS_DISABLED;

		public function __construct()
		{
			parent::__construct();
			$this->setId('manufacturerGrid');
			$this->setDefaultSort('manufacturer_id');
			$this->setDefaultDir('ASC');
			$this->setSaveParametersInSession(TRUE);
		}

		protected function _prepareCollection()
		{
			$collection = Mage::getModel('manufacturer/manufacturer')->getCollection();
			$this->setCollection($collection);
			return parent::_prepareCollection();
		}

		protected function _filterStoreCondition($collection, $column)
		{
			if (!$value = $column->getFilter()->getValue()) {
				return;
			}
			$this->getCollection()->addStoreFilter($value);
		}

		protected function _afterLoadCollection()
		{
			$this->getCollection()->walk('afterLoad');
			parent::_afterLoadCollection();
		}

		protected function _prepareColumns()
		{
			$this->addColumn('manufacturer_id', array(
				'header' => Mage::helper('manufacturer')->__('ID'),
				'align' => 'right',
				'width' => '50px',
				'index' => 'manufacturer_id',
			));

			$this->addColumn('name', array(
				'header' => Mage::helper('manufacturer')->__('Name'),
				'align' => 'left',
				'index' => 'name',
			));
			/*if (!Mage::app()->isSingleStoreMode()) {
				$this->addColumn('store_id', array(
					'header' => Mage::helper('manufacturer')->__('Store View'),
					'index' => 'store_id',
					'type' => 'store',
					'store_all' => true,
					'store_view' => true,
					'sortable' => true,
					'filter_condition_callback' => array($this,
						'_filterStoreCondition'),
				));
			}*/

			$this->addColumn('website', array(
				'header' => Mage::helper('manufacturer')->__('Website'),
				'align' => 'left',
				'width' => '200px',
				'index' => 'website',
			));

			$this->addColumn('is_featured', array(
				'header' => Mage::helper('manufacturer')->__('Is Featured'),
				'align' => 'left',
				'width' => '80px',
				'index' => 'is_featured',
				'type' => 'options',
				'options' => array(
					1 => 'Yes',
					0 => 'No',
				),
			));

			$this->addColumn('status', array(
				'header' => Mage::helper('manufacturer')->__('Status'),
				'align' => 'left',
				'width' => '80px',
				'index' => 'status',
				'type' => 'options',
				'options' => array(
					self::STATUS_ENABLED => 'Enabled',
					self::STATUS_DISABLED => 'Disabled',
				),
			));

			$this->addColumn('action',
				array(
					'header' => Mage::helper('manufacturer')->__('Action'),
					'width' => '100',
					'type' => 'action',
					'getter' => 'getId',
					'actions' => array(
						array(
							'caption' => Mage::helper('manufacturer')->__('Edit'),
							'url' => array('base' => '*/*/edit'),
							'field' => 'id'
						)
					),
					'filter' => FALSE,
					'sortable' => FALSE,
					'index' => 'stores',
					'is_system' => TRUE,
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('manufacturer')->__('CSV'));
			$this->addExportType('*/*/exportXml', Mage::helper('manufacturer')->__('XML'));
			return parent::_prepareColumns();
		}

		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('manufacturer_id');
			$this->getMassactionBlock()->setFormFieldName('manufacturer');
			$this->getMassactionBlock()->addItem('delete', array(
				'label' => Mage::helper('manufacturer')->__('Delete'),
				'url' => $this->getUrl('*/*/massDelete'),
				'confirm' => Mage::helper('manufacturer')->__('Are you sure?')
			));
			$statuses = Mage::getSingleton('manufacturer/status')->getOptionArray();
			array_unshift($statuses, array('label' => '', 'value' => ''));
			$this->getMassactionBlock()->addItem('status', array(
				'label' => Mage::helper('manufacturer')->__('Change status'),
				'url' => $this->getUrl('*/*/massStatus', array('_current' => TRUE)),
				'additional' => array(
					'visibility' => array(
						'name' => 'status',
						'type' => 'select',
						'class' => 'required-entry',
						'label' => Mage::helper('manufacturer')->__('Status'),
						'values' => $statuses
					)
				)
			));
			return $this;
		}

		public function getRowUrl($row)
		{
			return $this->getUrl('*/*/edit', array('id' => $row->getId()));
		}

	}