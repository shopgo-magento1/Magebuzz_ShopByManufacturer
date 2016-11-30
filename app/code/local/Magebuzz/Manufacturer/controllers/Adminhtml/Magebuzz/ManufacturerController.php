<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

	class Magebuzz_Manufacturer_Adminhtml_Magebuzz_ManufacturerController extends Mage_Adminhtml_Controller_action
	{
		protected function _initAction()
		{
			$this->loadLayout()
				->_setActiveMenu('manufacturer/items')
				->_addBreadcrumb(Mage::helper('adminhtml')->__('Manufacturer Manager'), Mage::helper('adminhtml')->__('Manufacturer Manager'));
			return $this;
		}

		public function indexAction()
		{
			$this->_initAction()
				->renderLayout();
		}

		public function editAction()
		{
			$id = $this->getRequest()->getParam('id');
			$model = Mage::getModel('manufacturer/manufacturer')->load($id);
			if ($model->getId() || $id == 0) {
				$data = Mage::getSingleton('adminhtml/session')->getFormData(TRUE);
				if (!empty($data)) {
					$model->setData($data);
				}
				Mage::register('manufacturer_data', $model);
				$this->loadLayout();
				$this->_setActiveMenu('manufacturer/items');
				$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
				$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
				$this->getLayout()->getBlock('head')->setCanLoadExtJs(TRUE);
				$this->_addContent($this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit'))
					->_addLeft($this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit_tabs'));
				$this->renderLayout();
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturer')->__('Item does not exist'));
				$this->_redirect('*/*/');
			}
		}

		public function newAction()
		{
			$this->_forward('edit');
		}

		public function saveAction()
		{
			$helper = Mage::helper('manufacturer');
			if ($data = $this->getRequest()->getPost()) {
				$helper = Mage::helper('manufacturer');
				$model = Mage::getModel('manufacturer/manufacturer');
				if ($id = $this->getRequest()->getParam('id')) {
					$model->load($id);
					$productlist = $model->getSelectedProductIds();
					$optionid = $model->getOptionId();
				}
				//$storeIds = $this->getRequest()->getParam('stores');
				$storeIds = array(0);
				$data['image'] = '';
				if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != '') {
					try {
						$image_name = $_FILES['image']['name'];
						$new_image_name = $helper->renameImage($image_name);
						$uploader = new Varien_File_Uploader('image');
						$uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
						$uploader->setAllowRenameFiles(TRUE);
						$uploader->setFilesDispersion(FALSE);
						$path = Mage::getBaseDir('media') . DS . 'manufacturer';
						if (!file_exists($path . DS . $new_image_name)) {
							$uploader->save($path, $new_image_name);
						}
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturer')->__("There was problem when saving manufacturer. Please try again."));
						Mage::getSingleton('adminhtml/session')->setFormData($data);
						$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
						return;
					}
					$data['image'] = $new_image_name;
				} elseif ($model->getImage()) {
					$data['image'] = $model->getImage();
				}
				$post = $this->getRequest()->getPost();
				if (isset($post['image']['delete']) && $post['image']['delete'] == 1) {
					$data['image'] = '';
				}
				$model->setData($data);
				//edit Form
				if ($this->getRequest()->getParam('id') > 0) {
					$model->setId($this->getRequest()->getParam('id'));
					if (!$optionid) {
						$manufacturerOption = array('manufacturerName' => $data['name'], 'store_id' => $storeIds);
						$optionid = $model->addManufacturerOption($manufacturerOption);
						$model->setOptionId($optionid);
					}
					if (isset($data['in_manufacturer_product'])) {
						$productIds = array();
						parse_str($data['in_manufacturer_product'], $productIds);
						$productIds = array_keys($productIds);
						$model->compareProductList($productIds, $productlist, $optionid);
					}

				} else {
					//add Form
					$manufacturerOption = array('manufacturerName' => $data['name'], 'store_id' => $storeIds);
					$optionManufacturerId = $model->addManufacturerOption($manufacturerOption);
					if (isset($data['in_manufacturer_product'])) {
						$productIds = array();
						parse_str($data['in_manufacturer_product'], $productIds);
						$productIds = array_keys($productIds);
						$model->compareProductList($productIds, $productlist = array(), $optionManufacturerId);
						$model->setOptionId($optionManufacturerId);

					}
				}
				try {
					$model->save();
					//rewrite URL
					$isChangedIdentifier = FALSE;
					if ($model->getId() && $model->getIdentifier() == $data['identifier']) {
						$isChangedIdentifier = TRUE;
					}
					$store_id = Mage::app()->getStore()->getId();
					$identifier = $helper->generateIdentifier($model->getIdentifier());
					$rewriteModel = Mage::getModel('core/url_rewrite');
					$id_path = 'manufacturer/' . $model->getId();
					$rewriteModel->loadByIdPath($id_path);
					$request_path = $helper->getConfigTextRouter() . '/' . $identifier;
					if ($rewriteModel->getId()) {
						if ($isChangedIdentifier) {
							$rewriteModel->setData('id_path', 'manufacturer/' . $model->getId());
							$rewriteModel->setData('request_path', $request_path);
							$rewriteModel->setData('target_path', 'manufacturer/index/view/id/' . $model->getId());
							$rewriteModel->save();
						}
					} else {
						//create new rewrite
						$rewriteModel->loadByRequestPath($request_path);
						if ($rewriteModel->getId()) {
							$identifier = $identifier . '-' . $model->getId();
							$request_path = $request_path . '-' . $model->getId();
							$urlRewrite = Mage::getModel('core/url_rewrite');
							$urlRewrite->setData('id_path', 'manufacturer/' . $model->getId());
							$urlRewrite->setData('request_path', $request_path);
							$urlRewrite->setData('target_path', 'manufacturer/index/view/id/' . $model->getId());
							$urlRewrite->setStoreId($store_id);
							$urlRewrite->save();
						} else {
							$identifier = $identifier . '-' . $model->getId();
							$urlRewrite = Mage::getModel('core/url_rewrite');
							$urlRewrite->setData('id_path', 'manufacturer/' . $model->getId());
							$urlRewrite->setData('request_path', $request_path);
							$urlRewrite->setData('target_path', 'manufacturer/index/view/id/' . $model->getId());
							$urlRewrite->setStoreId($store_id);
							$urlRewrite->save();
						}
					}
					$model->setIdentifier($data['identifier'])->save();
					Mage::getModel('manufacturer/manufacturer')->getResource()->saveStore($model->getId(), $storeIds);
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('manufacturer')->__('Item was successfully saved'));
					Mage::getSingleton('adminhtml/session')->setFormData(FALSE);
					if ($this->getRequest()->getParam('back')) {
						$this->_redirect('*/*/edit', array('id' => $model->getId()));
						return;
					}
					$this->_redirect('*/*/');
					return;
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					Mage::getSingleton('adminhtml/session')->setFormData($data);
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
					return;
				}
			}
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('manufacturer')->__('Unable to find item to save'));
			$this->_redirect('*/*/');
		}

		public function deleteAction()
		{
			if ($this->getRequest()->getParam('id') > 0) {
				try {
					$model = Mage::getModel('manufacturer/manufacturer');
					//delete URL rewrite
					$id_path = 'manufacturer/' . $model->getId();
					$urlRewrite = Mage::getModel('core/url_rewrite');
					$urlRewrite->loadByIdPath($id_path)->delete();
					Mage::getModel('manufacturer/manufacturer')->deleteManufacturerOption($this->getRequest()->getParam('id'));
					$model->setId($this->getRequest()->getParam('id'))->delete();
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
					$this->_redirect('*/*/');
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				}
			}
			$this->_redirect('*/*/');
		}

		public function productlistAction()
		{
			$this->loadLayout();
			$this->getLayout()->getBlock('manufacturer.edit.tab.products')
				->setProducts($this->getRequest()->getPost('oproduct', null));
			$this->renderLayout();
		}

		public function productlistGridAction()
		{
			$this->loadLayout();
			$this->getLayout()->getBlock('manufacturer.edit.tab.products')
				->setProducts($this->getRequest()->getPost('oproduct', null));
			$this->renderLayout();
		}

		public function gridAction()
		{
			$this->getResponse()->setBody(
				$this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_edit_tab_products')->toHtml()
			);
		}

		public function massDeleteAction()
		{
			$manufacturerIds = $this->getRequest()->getParam('manufacturer');
			if (!is_array($manufacturerIds)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
			} else {
				try {
					foreach ($manufacturerIds as $manufacturerId) {
						$manufacturer = Mage::getModel('manufacturer/manufacturer')->load($manufacturerId);
						//delete URL rewrite
						$id_path = 'manufacturer/' . $manufacturer->getId();
						$urlRewrite = Mage::getModel('core/url_rewrite');
						$urlRewrite->loadByIdPath($id_path)->delete();
						Mage::getModel('manufacturer/manufacturer')->deleteManufacturerOption($manufacturerId);
						$manufacturer->delete();
					}
					Mage::getSingleton('adminhtml/session')->addSuccess(
						Mage::helper('adminhtml')->__(
							'Total of %d record(s) were successfully deleted', count($manufacturerIds)
						)
					);
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}

		public function massStatusAction()
		{
			$manufacturerIds = $this->getRequest()->getParam('manufacturer');
			if (!is_array($manufacturerIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			} else {
				try {
					foreach ($manufacturerIds as $manufacturerId) {
						$manufacturer = Mage::getSingleton('manufacturer/manufacturer')
							->load($manufacturerId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(TRUE)
							->save();
					}
					$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully updated', count($manufacturerIds))
					);
				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}

		public function exportCsvAction()
		{
			$fileName = 'manufacturer.csv';
			$content = $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_grid')
				->getCsv();
			$this->_sendUploadResponse($fileName, $content);
		}

		public function exportXmlAction()
		{
			$fileName = 'manufacturer.xml';
			$content = $this->getLayout()->createBlock('manufacturer/adminhtml_manufacturer_grid')
				->getXml();
			$this->_sendUploadResponse($fileName, $content);
		}

		protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
		{
			$response = $this->getResponse();
			$response->setHeader('HTTP/1.1 200 OK', '');
			$response->setHeader('Pragma', 'public', TRUE);
			$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', TRUE);
			$response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
			$response->setHeader('Last-Modified', date('r'));
			$response->setHeader('Accept-Ranges', 'bytes');
			$response->setHeader('Content-Length', strlen($content));
			$response->setHeader('Content-type', $contentType);
			$response->setBody($content);
			$response->sendResponse();
			die;
		}

		public function importAction()
		{
			$helper = Mage::helper('manufacturer');
			$manufacturer_options = $helper->getManufacturerOptions();
			Mage::getModel('manufacturer/manufacturer')->getAllManufacturer($manufacturer_options);
			$count = 0;
			foreach ($manufacturer_options as $manufacturer) {
				if ($manufacturer['value']) {
					$model = Mage::getModel('manufacturer/manufacturer');
					$model->load($manufacturer['value'], 'option_id');
					if (!$model->getId()) {
						$identifier = $helper->generateIdentifier($manufacturer['label']);
						$store_id = 0;
						$model->setName($manufacturer['label'])
							->setOptionId($manufacturer['value'])
							->setIdentifier($identifier)
							->setStoreId($store_id)
							->setStatus(1)
							->save();
						$request_path = $helper->getConfigTextRouter() . '/' . $identifier;
						$id_path = 'manufacturer/' . $model->getId();
						$rewriteModel = Mage::getModel('core/url_rewrite');
						$rewriteModel->loadByRequestPath($request_path);
						if (!$rewriteModel->getId()) {
							$rewriteModel->setData('id_path', $id_path);
							$rewriteModel->setData('request_path', $request_path);
							$rewriteModel->setData('target_path', 'manufacturer/index/view/id/' . $model->getId());
							$rewriteModel->save();
						} else {
							$identifier = $identifier . '-' . $model->getId();
							$request_path = $request_path . '-' . $model->getId();
							$urlModel = Mage::getModel('core/url_rewrite');
							$urlModel->setData('id_path', $id_path);
							$urlModel->setData('request_path', $request_path);
							$urlModel->setData('target_path', 'manufacturer/index/view/id/' . $model->getId());
							$urlModel->save();
						}
						$model->setIdentifier($identifier)->save();
						$model->getResource()->saveStore($model->getId(), $store_id);
						$count++;
					}
				}
			}
			if ($count != 0) {
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully imported', $count
					)
				);
				$this->_redirect('*/*/index');
			} else {
				Mage::getSingleton('adminhtml/session')->addError($helper->__('There is no item to import'));
				$this->_redirect('*/*/index');
			}
		}

		public function reindexAction()
		{
			$helper = Mage::helper('manufacturer');
			$urls = Mage::getModel('core/url_rewrite')->getCollection()
				->addFieldToFilter('id_path', array('like' => 'manufacturer%'));
			if (count($urls)) {
				foreach ($urls as $item) {
					try {
						$item->delete();
					} catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('index')->__('Cannot initialize the indexer process.'));
					}
				}
			}
			$collection = Mage::getModel('manufacturer/manufacturer')->getCollection();
			if (count($collection)) {
				$helper = Mage::helper('manufacturer');
				$store_id = Mage::app()->getStore()->getId();
				try {
					foreach ($collection as $item) {
						if ($item->getIdentifier()) {
							$rewriteModel = Mage::getModel('core/url_rewrite');
							$identifier = $helper->generateIdentifier($item->getName());
							$id_path = 'manufacturer/' . $item->getId();
							$request_path = $helper->getConfigTextRouter() . '/' . $identifier;
							$rewriteModel->loadByRequestPath($request_path);
							if ($rewriteModel->getId()) {
								$identifier = $identifier . '-' . $item->getId();
								$request_path = $request_path . '-' . $item->getId();
							}
							$urlRewrite = Mage::getModel('core/url_rewrite');
							$urlRewrite->setData('id_path', 'manufacturer/' . $item->getId());
							$urlRewrite->setData('target_path', 'manufacturer/index/view/id/' . $item->getId());
							$urlRewrite->setStoreId($store_id);
							$urlRewrite->setData('request_path', $request_path);
							$urlRewrite->save();

							$item->setIdentifier($identifier)->save();
						}
					}
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('index')->__('Cannot initialize the indexer process.'));
					$this->_redirect('*/*/index');
				}
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('manufacturer')->__('Manufacturer URLs are re-indexed successfully.')
			);
			$this->_redirect('*/*/index');
		}


		public function reindexnameAction()
		{
			$helper = Mage::helper('manufacturer');
			$manufacturer_options = $helper->getManufacturerOptions();
			$model = Mage::getModel('manufacturer/manufacturer');
			foreach ($manufacturer_options as $option) {
				if ($option['value']) {
					$model->load($option['value'], 'option_id');
					$model->setName($option['label'])->save();
				}
			}
			$this->_redirect('*/*/index');
		}

		protected function _isAllowed() {
			return Mage::getSingleton('admin/session')->isAllowed('manufacturer/manage_manufacturer');
		}
	}
