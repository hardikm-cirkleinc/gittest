<?php

class Ct_SalesRepCoupon_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action {

    /**
     * Admin controller index action
     *
     * @access public
     * @return void
     */
    public function indexAction() {

        /* Get Data Custom Code Start Here */

        $role_id = Mage::getStoreConfig('amperm/general/role');

        $roles_users = Mage::getResourceModel('admin/roles_user_collection');

        foreach ($roles_users as $roleuser) {
            $role_data = Mage::getModel('admin/user')->load($roleuser->getUserId())->getRole()->getData();

            if ($role_data['role_id'] == $role_id) {
                $user = Mage::getModel('admin/user')->load($roleuser->getUserId());
            }
        }

        /* Get Data Custom Code End Here */

        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('SalesRep'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_salesrep'));
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/salesrepcoupon');
    }

    public function exportCsvAction() {
        $content = $this->getLayout()->createBlock('salesrepcoupon/adminhtml_salesrep')
                ->getCsvFile();

        $this->_prepareDownloadResponse('reports.csv', $content);
    }

    public function SalesrepAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('salesrepcoupon/adminhtml_salesrep')->toHtml()
        );
    }

}
