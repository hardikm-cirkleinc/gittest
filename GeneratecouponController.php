<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Ct_SalesRepCoupon_Adminhtml_GeneratecouponController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('salesrepcoupon')
                ->_title($this->__('Apply Coupon'))
                ->_addBreadcrumb($this->__('Sales'), $this->__('CT_SalesRepCoupon'))
                ->_addBreadcrumb($this->__('Slides'), $this->__('Manage Coupon'));

        return $this;
    }

    public function indexAction() {
        $this->loadLayout()
                ->_setActiveMenu('salesrepcoupon')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_generatecoupon_view'));
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $taxIds = $this->getRequest()->getParam('id');
        if (!is_array($taxIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('salesrepcoupon')->__('Please select coupon.'));
        } else {
            try {
                $rateModel = Mage::getModel('salesrepcoupon/ctcoupon');
                foreach ($taxIds as $taxId) {
                    $rateModel->load($taxId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('salesrepcoupon')->__(
                                'Total of %d record(s) were deleted.', count($taxIds)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        $content = $this->getLayout()->createBlock('salesrepcoupon/adminhtml_generatecoupon_grid')
                ->getCsvFile();

        $this->_prepareDownloadResponse('coupons.csv', $content);
    }

    public function generatecouponcodeAction() {

        $tbl_dasheveryxcharacter = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/dash_every_x_characters', Mage::app()->getStore());
        $tbl_codelength = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/code_length', Mage::app()->getStore());
        $tbl_codeprefix = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/code_prefix', Mage::app()->getStore());
        $tbl_codesuffix = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/code_suffix', Mage::app()->getStore());
        $tbl_formate = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/codeformat_select', Mage::app()->getStore());
        $tbl_usage_limit = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/uses_per_coupon', Mage::app()->getStore());
        $tbl_usage_per_customer = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/uses_per_customer', Mage::app()->getStore());
        $tbl_from_date = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/from_date', Mage::app()->getStore());
        $tbl_to_date = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/to_date', Mage::app()->getStore());
        $tbl_numbers_of_coupon = Mage::getStoreConfig('salesrepcoupon/salesrepcoupon_group1/numbers_of_coupon', Mage::app()->getStore());

        $rule = Mage::getModel('salesrule/rule')->load(1);

        $generator = Mage::getModel('salesrule/coupon_massgenerator');
        $parameters = array(
            'count' => $tbl_numbers_of_coupon,
            'format' => $tbl_formate,
            'dash_every_x_characters' => $tbl_dasheveryxcharacter,
            'prefix' => $tbl_codeprefix,
            'suffix' => $tbl_codesuffix,
            'length' => $tbl_codelength
        );


        if (!empty($parameters['format'])) {
            switch (strtolower($parameters['format'])) {
                case 'alphanumeric':
                case 'alphanum':
                    $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
                    break;
                case 'alphabetical':
                case 'alpha':
                    $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHABETICAL);
                    break;
                case 'numeric':
                case 'num':
                    $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_NUMERIC);
                    break;
            }
        }

        $generator->setDash(!empty($parameters['dash_every_x_characters']) ? (int) $parameters['dash_every_x_characters'] : 0);
        $generator->setLength(!empty($parameters['length']) ? (int) $parameters['length'] : 6);
        $generator->setPrefix(!empty($parameters['prefix']) ? $parameters['prefix'] : '');
        $generator->setSuffix(!empty($parameters['suffix']) ? $parameters['suffix'] : '');


        $rule->setCouponCodeGenerator($generator);
        $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

        $count = !empty($parameters['count']) ? (int) $parameters['count'] : 1;
        $codes = array();
        for ($i = 0; $i < $count; $i++) {
            $coupon = $rule->acquireCoupon();
            $usage_limit = $coupon->getUsageLimit();
            $usage_per_customer = $coupon->getUsagePerCustomer();
            $expiry_date = $coupon->getExpirationDate();
            $coupon_code = $coupon->getCode();
            $coupon->setUsageLimit(1);
            $coupon->setTimesUsed(0);
            $code = $coupon->getCode();
            $codes['code'] = $code;
            $coupon->delete();

            $collection = Mage::getModel('salesrepcoupon/ctcoupon');
            $collection->setCode($code);
            $collection->setFormate($tbl_formate);
            $collection->setUsageLimit($tbl_usage_limit);
            $collection->setUsagePerCustomer($tbl_usage_per_customer);
            $collection->setFromDate($tbl_from_date);
            $collection->setToDate($tbl_to_date);
            $collection->setTimesUsed(0);
            $collection->setSentViaMail(0);
            $collection->save();
        }

        $this->_redirect('*/*/index');
        Mage::getSingleton('core/session')->addSuccess('Coupon Code Generated Successfully..');
    }

    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('salesrepcoupon/adminhtml_generatecoupon_grid')->toHtml()
        );
    }

}
