<?php
defined('_JEXEC') or die();

class pm_hbepay extends PaymentRoot{

    function showPaymentForm($params, $pmconfigs){
        include(dirname(__FILE__)."/paymentform.php");
    }

    function loadLanguageFile(){
        $lang = JFactory::getLanguage();
        $langtag = $lang->getTag(); 

        if ($langtag == 'RU') {
          require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_hbepay/lang/ru-RU.php');
        } else { 
          require_once(JPATH_ROOT.'/components/com_jshopping/payments/pm_hbepay/lang/en-GB.php');
        }
      }

	function showAdminFormParams($params){
    	$orders = JSFactory::getModel('orders', 'JshoppingModel'); 
        $this->loadLanguageFile();
          include(dirname(__FILE__)."/adminparamsform.php");
    	}

	function checkTransaction($params, $order, $act){
		$order->order_total = $this->fixOrderTotal($order);
        
        if ($act == "return")
            return array(1);
        else if($act == "cancel")
            return array(0, 'Invalid response. Order ID: '.$order->order_id);
	}

	function showEndForm($params, $order) {
        $lang = JFactory::getLanguage();
        $langtag = $lang->getTag();
        if ($params['testmode']){
            $mode = "test";
        } else{
            $mode = "prod";
        }
        $pm_method = $this->getPmMethod();
        $invoice_id = $order->order_number;

        $post_url       = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_hbepay&order_id=".$order->order_id;
        $failure_post_url  = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=pm_hbepay&order_id=".$order->order_id;
        $return         = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_hbepay&order_id=".$order->order_id;
        $cancel_return  = JURI::root(). "index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=pm_hbepay&order_id=".$order->order_id;

        $inputs = [
            'hbp_client_id'             => $params['client_id'],
            'hbp_amount'                => $this->fixOrderTotal($order),
            'hbp_currency'              => $order->currency_code_iso,
            'hbp_client_secret'         => $params['client_secret'],
            'hbp_env'                   => $mode,
            'hbp_description'           => $params['description'],
            'hbp_terminal'              => $params['terminal'],
            'hbp_back_link'             => $return,
            'hbp_failure_back_link'     => $cancel_return,
            'hbp_post_link'             => $post_url,
            'hbp_failure_post_link'     => $failure_post_url,
            'hbp_invoice_id'            => $invoice_id,
            'hbp_language'              => $langtag
        ];
        
        $this->paymentGateway($inputs);

        die;
    }
    
    function paymentGateway($inputs) {
        // initiate api urls
        $test_url = "https://testoauth.homebank.kz/epay2/oauth2/token";
        $prod_url = "https://epay-oauth.homebank.kz/oauth2/token";
        $test_page = "https://test-epay.homebank.kz/payform/payment-api.js";
        $prod_page = "https://epay.homebank.kz/payform/payment-api.js";

        $token_api_url = "";
        $pay_page = "";
        $err_exist = false;
        $err = "";

        // initiate default variables
        $hbp_account_id = "";
        $hbp_telephone = "";
        $hbp_email = "";
        $hbp_currency = "KZT";
        $hbp_language = "RU";
        $hbp_description = "Оплата в интернет магазине";

        $hbp_env = $inputs['hbp_env'];
        $hbp_client_id = $inputs['hbp_client_id'];
        $hbp_client_secret = $inputs['hbp_client_secret'];
        $hbp_terminal = $inputs['hbp_terminal'];
        $hbp_invoice_id = $inputs['hbp_invoice_id'];
        $hbp_amount = $inputs['hbp_amount'];
        if(isset($inputs['hbp_currency'])) {
            $hbp_currency = $inputs['hbp_currency'];
        }
        $hbp_back_link = $inputs['hbp_back_link'];
        $hbp_failure_back_link = $inputs['hbp_failure_back_link'];
        $hbp_post_link = $inputs['hbp_post_link'];
        $hbp_failure_post_link = $inputs['hbp_failure_post_link'];
        if(isset($inputs['hbp_language'])) {
            $hbp_language = $inputs['hbp_language'];
        }
        if(isset($inputs['hbp_description'])) {
            $hbp_description = $inputs['hbp_description'];
        }
        if(isset($inputs['hbp_account_id'])) {
            $hbp_account_id = $inputs['hbp_account_id'];
        }
        if(isset($inputs['hbp_telephone'])) {
            $hbp_telephone = $inputs['hbp_telephone'];
        }
        if(isset($inputs['hbp_email'])) {
            $hbp_email = $inputs['hbp_email'];
        }

        if ($hbp_env == "test") {
            $token_api_url = $test_url;
            $pay_page = $test_page;
        } else {
            $token_api_url = $prod_url;
            $pay_page = $prod_page;
        }
        
        $fields = [
            'grant_type'      => 'client_credentials', 
            'scope'           => 'payment usermanagement',
            'client_id'       => $hbp_client_id,
            'client_secret'   => $hbp_client_secret,
            'invoiceID'       => $hbp_invoice_id,
            'amount'          => $hbp_amount,
            'currency'        => $hbp_currency,
            'terminal'        => $hbp_terminal,
            'postLink'        => $hbp_post_link,
            'failurePostLink' => $hbp_failure_post_link
          ];
        
          $fields_string = http_build_query($fields);
        
          $ch = curl_init();
        
          curl_setopt($ch, CURLOPT_URL, $token_api_url);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        
          $result = curl_exec($ch);
        
          $json_result = json_decode($result, true);
          if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200:
                    $hbp_auth = (object) $json_result;
        
                    $hbp_payment_object = (object) [
                        "invoiceId" => $hbp_invoice_id,
                        "backLink" => $hbp_back_link,
                        "failureBackLink" => $hbp_failure_back_link,
                        "postLink" => $hbp_post_link,
                        "failurePostLink" => $hbp_failure_post_link,
                        "language" => $hbp_language,
                        "description" => $hbp_description,
                        "accountId" => $hbp_account_id,
                        "terminal" => $hbp_terminal,
                        "amount" => $hbp_amount,
                        "currency" => $hbp_currency,
                        "auth" => $hbp_auth,
                        "phone" => $hbp_telephone,
                        "email" => $hbp_email
                    ];
                ?>
                <script src="<?=$pay_page?>"></script>
                <script>
                    halyk.pay(<?= json_encode($hbp_payment_object) ?>);
                </script>
            <?php
                    break;
                default:
                    echo 'Неожиданный код HTTP: ', $http_code, "\n";
            }
        }
    }

    function getUrlParams($pmconfigs){
        $params = array();
        $params['order_id'] = JFactory::getApplication()->input->getInt("order_id");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 1;
        return $params;
    }

	function fixOrderTotal($order){
        $total = $order->order_total;
        return $total;
    }
}