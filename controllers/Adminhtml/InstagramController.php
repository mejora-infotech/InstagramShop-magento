<?php

class Mejora_Instashop_Adminhtml_InstagramController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("instashop/instagram")->_addBreadcrumb(Mage::helper("adminhtml")->__("Instagram  Manager"),Mage::helper("adminhtml")->__("Instagram Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Instashop"));
			    $this->_title($this->__("Manager Instagram"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Instashop"));
				$this->_title($this->__("Instagram"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("instashop/instagram")->load($id);
				if ($model->getId()) {
					Mage::register("instagram_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("instashop/instagram");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Instagram Manager"), Mage::helper("adminhtml")->__("Instagram Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Instagram Description"), Mage::helper("adminhtml")->__("Instagram Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("instashop/adminhtml_instagram_edit"))->_addLeft($this->getLayout()->createBlock("instashop/adminhtml_instagram_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("instashop")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Instashop"));
		$this->_title($this->__("Instagram"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("instashop/instagram")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("instagram_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("instashop/instagram");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Instagram Manager"), Mage::helper("adminhtml")->__("Instagram Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Instagram Description"), Mage::helper("adminhtml")->__("Instagram Description"));


		$this->_addContent($this->getLayout()->createBlock("instashop/adminhtml_instagram_edit"))->_addLeft($this->getLayout()->createBlock("instashop/adminhtml_instagram_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						
				 //save image
		try{

if((bool)$post_data['post_id']['delete']==1) {

	        $post_data['post_id']='';

}
else {

	unset($post_data['post_id']);

	if (isset($_FILES)) {

		if ($_FILES['post_id']['name']) {

			if($this->getRequest()->getParam("id")){
				$model = Mage::getModel("instashop/instagram")->load($this->getRequest()->getParam("id"));
				if($model->getData('post_id')){
						$io = new Varien_Io_File();
						$io->rm(Mage::getBaseDir('media').DS.implode(DS,explode('/',$model->getData('post_id'))));	
				}
			}
						$path = Mage::getBaseDir('media') . DS . 'instashop' . DS .'instagram'.DS;
						$uploader = new Varien_File_Uploader('post_id');
						$uploader->setAllowedExtensions(array('jpg','png','gif'));
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						$destFile = $path.$_FILES['post_id']['name'];
						$filename = $uploader->getNewFileName($destFile);
						$uploader->save($path, $filename);

						$post_data['post_id']='instashop/instagram/'.$filename;
		}
    }
}

        } catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
        }
//save image


						$model = Mage::getModel("instashop/instagram")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Instagram was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setInstagramData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setInstagramData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("instashop/instagram");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("instashop/instagram");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
		
		public function updateTitleAction() {
			$fieldId = (int) $this->getRequest()->getParam('id');
			$product_id = $this->getRequest()->getParam('product_id');
			if ($fieldId) {
				$model = Mage::getModel('instashop/instagram')->load($fieldId);
				$model->setProductId($product_id);
				$model->save();
				}
	}
			
}
