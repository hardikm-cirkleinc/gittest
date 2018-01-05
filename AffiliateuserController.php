<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Ct_SalesRepCoupon_Adminhtml_AffiliateuserController extends Mage_Adminhtml_Controller_Action {

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
                ->_setActiveMenu('salesrepcoupon')
                ->_title($this->__('Apply Coupon'));
        $this->_addContent($this->getLayout()->createBlock('salesrepcoupon/adminhtml_affiliateuser_view'));
        $this->renderLayout();
    }

    /* send affiliate user coupon code start here */

    public function massSendCouponAction() {
        $user_Ids = $this->getRequest()->getParam('user_id');
        foreach ($user_Ids as $user_id) {
            $user_data = Mage::getModel('admin/user')->load($user_id)->getData();
            $email = $user_data['email'];
            if ($email) {
                $user_email_collection[$user_id] = $email;
            }
        }

        $count_user_collection = count($user_email_collection);

        $couponCollection = Mage::getModel('salesrepcoupon/ctcoupon')->getCollection();
        $i = 0;
        foreach ($couponCollection as $coupon) {
            if ($i < $count_user_collection) {
                if (!$coupon->getSentViaMail() && $coupon->getUsageLimit() > 0 && $coupon->getUsagePerCustomer() > 0) {
                    $i++;
                    $couponcolls[$coupon->getId()] = $coupon->getCode();
                }
            }
        }

        $two_array = array_combine($user_email_collection, $couponcolls);

        foreach ($two_array as $email => $couponcode) {
            // Use Magento Functionality for sending a mail
            // http://www.jyotiranjan.in/blog/create-custom-transactional-email-in-magento/

            $template_id = 'send_affiliate_user_promocode';
            $email_to = $email;
            $customer_name = 'Shijin Coupon Code';
            $email_template = Mage::getModel('core/email_template')->loadDefault($template_id);
            $custom_variable = 'my custom variable for my custom eamil template';
            $email_template_variables = array(
                'custom_variable' => $custom_variable,
                'coupon_code' => $couponcode
            );

            $sender_name = Mage::getStoreConfig('trans_email/ident_general/name');
            $sender_email = Mage::getStoreConfig('trans_email/ident_general/email');
            $email_template->setSenderName($sender_name);
            $email_template->setSenderEmail($sender_email);

            if ($email_template->send($email_to, $customer_name, $email_template_variables)) {
                $user_collection = Mage::getModel('admin/user')->load($email, 'email');
                $user_id = $user_collection->getUserId();
                $collection = Mage::getModel('salesrepcoupon/ctcoupon')->load($couponcode, 'code');
                $collection->setData('sent_mail_user_id', $user_id);
                $collection->setSentViaMail(1);
                $collection->save();
                Mage::getSingleton('adminhtml/session')->addSuccess('Successfully Sent Coupon Code to ' . $user_collection->getEmail());
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Some Error was Occured');
            }
        }

        /*
         * Use PHP Funcionality to Sending mai;
         * https://www.tutorialrepublic.com/php-tutorial/php-send-email.php
         * 
          $to = 'hardikm.cirkleinc@gmail.com';
          $subject = 'the subject';
          $message = 'hello';
          $headers = 'From: bhaving.cirkleinc@gmail.com' . "\r\n" .
          'Reply-To: bhaving.cirkleinc@gmail.com' . "\r\n" .
          'X-Mailer: PHP/' . phpversion();

          mail($to, $subject, $message, $headers);
         */

        $this->_redirect('*/*/index');
    }

    /* send affiliate user coupon code end here */
}
