<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Ct_SalesRepCoupon_Adminhtml_ApplycouponController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'))
                ->_addBreadcrumb($this->__('Sales'), $this->__('CT'))
                ->_addBreadcrumb($this->__('Slides'), $this->__('SalesRule'));

        return $this;
    }

    public function indexAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_view'));
        $this->renderLayout();
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/applycoupon');
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_grid')->toHtml()
        );
    }

    public function generatereportAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_monthly'));
        $this->renderLayout();
    }

    /* generate report button code start here */

    public function generatemonthreportAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_monthwise'));
        $this->renderLayout();
    }

    /* generate report button code end here */

    /* Monthly Date Filter Click Event Code Start Here */

    public function generatemonthwisereportAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_monthreport'));
        $this->renderLayout();
    }

    /* Monthly Date Filter Click Event Code End Here */

    public function applycouponAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_addcoupon'));
        $this->renderLayout();
    }

    public function editAction() {
        $this->loadLayout()
                ->_setActiveMenu('sales')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_coupon_edit'));
        $this->renderLayout();
    }

    public function saveAction() {
        if ($postData = $this->getRequest()->getPost()) {
            $couponcode = $postData['coupon_code'];
            $collection = Mage::getModel('salesrepcoupon/ctcoupon')->load($couponcode, 'code');
            $sent_via_mail = $collection->getSentViaMail();
            $from_date = $collection->getFromDate();
            $to_date = $collection->getToDate();
            $current_date = date('Y-m-d');

            $usage_limit = $collection->getUsageLimit();
            $usage_per_customer = $collection->getUsagePerCustomer();
            $times_used = $collection->getTimesUsed();

            if ($sent_via_mail) {
                if ($from_date >= $current_date && $to_date <= $current_date) {

                    if ($usage_limit >= $times_used) {
                        $times_used = $collection->getTimesUsed();
                        if ($usage_limit == $times_used) {
                            Mage::getSingleton('adminhtml/session')->addError('The Coupon Already Used');
                        } else {
                            $collection->setTimesUsed($times_used + 1);
                            $collection->save();
                            Mage::getSingleton('adminhtml/session')->addSuccess('Successfully Add Coupon that Deduct in your monthly statement');
                        }
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError('The Coupon Usage Limit is Over');
                    }
                } else {
                    Mage::getSingleton('adminhtml/session')->addError('The Date Validity is Incorrect');
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Coupon code has not been sent to anyone So You do not use Directly');
            }
            $this->_redirect('*/*/index');
        }
    }

}
