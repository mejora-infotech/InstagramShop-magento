<?php
class Mejora_Instashop_Adminhtml_InstashopbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Instashop"));
	   $this->renderLayout();
    }
	
	
}