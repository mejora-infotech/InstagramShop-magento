<?php
class Mejora_Instashop_Block_Adminhtml_Widget_Grid_Column_Renderer_Inline
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $html .= '<button onclick="updateNote(this, '. $row->getId() .'); return false">' . Mage::helper('instashop')->__('Update') . '</button>';
        return $html;
    }
}