<?php
class ControllerExtensionModuleSoOnepageCheckout extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/so_onepagecheckout');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['objlang']	= $this->language;

		$this->load->model('setting/setting');
		$this->load->model('extension/module/so_onepagecheckout');
		$this->load->model('localisation/country');
		
		$this->document->addStyle('view/javascript/so_onepagecheckout/css/so_onepagecheckout.css');

		$module_id = '';
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_so_onepagecheckout', $this->request->post);
			
			if(isset($this->request->post['save_stay']) && $this->request->post['save_stay'] == 1) {
				$this->response->redirect($this->url->link('extension/module/so_onepagecheckout', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}else{
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}

		// Save and Stay --------------------------------------------------------------
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_onepagecheckout', 'user_token=' . $this->session->data['user_token'], 'SSL')
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/so_onepagecheckout', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
			);			
		}

		// Get country list
		$data['countries']	= $this->model_localisation_country->getCountries();
		$data['user_token']	= $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['error_name'])) {
			$data['error_name'] = $this->error['error_name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['error_product_image_width'])) {
			$data['error_product_image_width'] = $this->error['error_product_image_width'];
		} else {
			$data['error_product_image_width'] = '';
		}

		if (isset($this->error['error_product_image_height'])) {
			$data['error_product_image_height'] = $this->error['error_product_image_height'];
		} else {
			$data['error_product_image_height'] = '';
		}
		
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/so_onepagecheckout', 'user_token=' . $this->session->data['user_token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/so_onepagecheckout', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		if (isset($this->request->post['module_so_onepagecheckout_status'])) {
			$data['status'] = $this->request->post['module_so_onepagecheckout_status'];
		} else {
			$data['status'] = $this->config->get('module_so_onepagecheckout_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_name'])) {
			$data['name'] = $this->request->post['module_so_onepagecheckout_name'];
		} else {
			$data['name'] = $this->config->get('module_so_onepagecheckout_name');
		}

		if (isset($this->request->post['module_so_onepagecheckout_layout'])) {
			$data['layout'] = $this->request->post['module_so_onepagecheckout_layout'];
		} else {
			$data['layout'] = $this->config->get('module_so_onepagecheckout_layout');
		}

		if (isset($this->request->post['module_so_onepagecheckout_country_id'])) {
			$data['country_id'] = $this->request->post['module_so_onepagecheckout_country_id'];
		} else {
			$data['country_id'] = $this->config->get('module_so_onepagecheckout_country_id');
		}

		if (isset($this->request->post['module_so_onepagecheckout_zone_id'])) {
			$data['zone_id'] = $this->request->post['module_so_onepagecheckout_zone_id'];
		} else {
			$data['zone_id'] = $this->config->get('module_so_onepagecheckout_zone_id');
		}

		if (isset($this->request->post['module_so_onepagecheckout_register_checkout'])) {
			$data['register_checkout'] = $this->request->post['module_so_onepagecheckout_register_checkout'];
		} else {
			$data['register_checkout'] = $this->config->get('module_so_onepagecheckout_register_checkout');
		}

		if (isset($this->request->post['module_so_onepagecheckout_guest_checkout'])) {
			$data['guest_checkout'] = $this->request->post['module_so_onepagecheckout_guest_checkout'];
		} else {
			$data['guest_checkout'] = $this->config->get('module_so_onepagecheckout_guest_checkout');
		}

		if (isset($this->request->post['module_so_onepagecheckout_login_checkout'])) {
			$data['login_checkout'] = $this->request->post['module_so_onepagecheckout_login_checkout'];
		} else {
			$data['login_checkout'] = $this->config->get('module_so_onepagecheckout_login_checkout');
		}

		if (isset($this->request->post['module_so_onepagecheckout_account_open'])) {
			$data['account_open'] = $this->request->post['module_so_onepagecheckout_account_open'];
		} else {
			$data['account_open'] = $this->config->get('module_so_onepagecheckout_account_open');
		}

		if (isset($this->request->post['module_so_onepagecheckout_shopping_cart_status'])) {
			$data['shopping_cart_status'] = $this->request->post['module_so_onepagecheckout_shopping_cart_status'];
		} else {
			$data['shopping_cart_status'] = $this->config->get('module_so_onepagecheckout_shopping_cart_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_product_weight'])) {
			$data['show_product_weight'] = $this->request->post['module_so_onepagecheckout_show_product_weight'];
		} else {
			$data['show_product_weight'] = $this->config->get('module_so_onepagecheckout_show_product_weight');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_product_qnty_update'])) {
			$data['show_product_qnty_update'] = $this->request->post['module_so_onepagecheckout_show_product_qnty_update'];
		} else {
			$data['show_product_qnty_update'] = $this->config->get('module_so_onepagecheckout_show_product_qnty_update');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_product_removecart'])) {
			$data['show_product_removecart'] = $this->request->post['module_so_onepagecheckout_show_product_removecart'];
		} else {
			$data['show_product_removecart'] = $this->config->get('module_so_onepagecheckout_show_product_removecart');
		}

		if (isset($this->request->post['module_so_onepagecheckout_product_image_width'])) {
			$data['product_image_width'] = $this->request->post['module_so_onepagecheckout_product_image_width'];
		} else {
			$data['product_image_width'] = $this->config->get('module_so_onepagecheckout_product_image_width');
		}

		if (isset($this->request->post['module_so_onepagecheckout_product_image_height'])) {
			$data['product_image_height'] = $this->request->post['module_so_onepagecheckout_product_image_height'];
		} else {
			$data['product_image_height'] = $this->config->get('module_so_onepagecheckout_product_image_height');
		}

		if (isset($this->request->post['module_so_onepagecheckout_coupon_login_status'])) {
			$data['coupon_login_status'] = $this->request->post['module_so_onepagecheckout_coupon_login_status'];
		} else {
			$data['coupon_login_status'] = $this->config->get('module_so_onepagecheckout_coupon_login_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_coupon_register_status'])) {
			$data['coupon_register_status'] = $this->request->post['module_so_onepagecheckout_coupon_register_status'];
		} else {
			$data['coupon_register_status'] = $this->config->get('module_so_onepagecheckout_coupon_register_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_coupon_guest_status'])) {
			$data['coupon_guest_status'] = $this->request->post['module_so_onepagecheckout_coupon_guest_status'];
		} else {
			$data['coupon_guest_status'] = $this->config->get('module_so_onepagecheckout_coupon_guest_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_reward_login_status'])) {
			$data['reward_login_status'] = $this->request->post['module_so_onepagecheckout_reward_login_status'];
		} else {
			$data['reward_login_status'] = $this->config->get('module_so_onepagecheckout_reward_login_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_reward_register_status'])) {
			$data['reward_register_status'] = $this->request->post['module_so_onepagecheckout_reward_register_status'];
		} else {
			$data['reward_register_status'] = $this->config->get('module_so_onepagecheckout_reward_register_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_reward_guest_status'])) {
			$data['reward_guest_status'] = $this->request->post['module_so_onepagecheckout_reward_guest_status'];
		} else {
			$data['reward_guest_status'] = $this->config->get('module_so_onepagecheckout_reward_guest_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_voucher_login_status'])) {
			$data['voucher_login_status'] = $this->request->post['module_so_onepagecheckout_voucher_login_status'];
		} else {
			$data['voucher_login_status'] = $this->config->get('module_so_onepagecheckout_voucher_login_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_voucher_register_status'])) {
			$data['voucher_register_status'] = $this->request->post['module_so_onepagecheckout_voucher_register_status'];
		} else {
			$data['voucher_register_status'] = $this->config->get('module_so_onepagecheckout_voucher_register_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_voucher_guest_status'])) {
			$data['voucher_guest_status'] = $this->request->post['module_so_onepagecheckout_voucher_guest_status'];
		} else {
			$data['voucher_guest_status'] = $this->config->get('module_so_onepagecheckout_voucher_guest_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_delivery_method_status'])) {
			$data['delivery_method_status'] = $this->request->post['module_so_onepagecheckout_delivery_method_status'];
		} else {
			$data['delivery_method_status'] = $this->config->get('module_so_onepagecheckout_delivery_method_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_default_shipping'])) {
			$data['default_shipping'] = $this->request->post['module_so_onepagecheckout_default_shipping'];
		} else {
			$data['default_shipping'] = $this->config->get('module_so_onepagecheckout_default_shipping');
		}

		$shipping_methods = $this->model_extension_module_so_onepagecheckout->getShippingMethods();
		foreach ($shipping_methods as $shipping_method) {
			if (isset($this->request->post['module_so_onepagecheckout_'.$shipping_method['code'].'_status'])) {
				$data['shipping'][$shipping_method['code'].'_status'] = $this->request->post['module_so_onepagecheckout_'.$shipping_method['code'].'_status'];
			} else {
				$data['shipping'][$shipping_method['code'].'_status'] = $this->config->get('module_so_onepagecheckout_'.$shipping_method['code'].'_status');
			}
		}
		
		if (isset($this->request->post['module_so_onepagecheckout_payment_method_status'])) {
			$data['payment_method_status'] = $this->request->post['module_so_onepagecheckout_payment_method_status'];
		} else {
			$data['payment_method_status'] = $this->config->get('module_so_onepagecheckout_payment_method_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_default_payment'])) {
			$data['default_payment'] = $this->request->post['module_so_onepagecheckout_default_payment'];
		} else {
			$data['default_payment'] = $this->config->get('module_so_onepagecheckout_default_payment');
		}

		$payment_methods = $this->model_extension_module_so_onepagecheckout->getPaymentMethods();
		foreach ($payment_methods as $payment_method) {
			if (isset($this->request->post['module_so_onepagecheckout_'.$payment_method['code'].'_status'])) {
				$data['payment'][$payment_method['code'].'_status'] = $this->request->post['module_so_onepagecheckout_'.$payment_method['code'].'_status'];
			} else {
				$data['payment'][$payment_method['code'].'_status'] = $this->config->get('module_so_onepagecheckout_'.$payment_method['code'].'_status');
			}
		}

		if (isset($this->request->post['module_so_onepagecheckout_comment_status'])) {
			$data['comment_status'] = $this->request->post['module_so_onepagecheckout_comment_status'];
		} else {
			$data['comment_status'] = $this->config->get('module_so_onepagecheckout_comment_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_require_comment_status'])) {
			$data['require_comment_status'] = $this->request->post['module_so_onepagecheckout_require_comment_status'];
		} else {
			$data['require_comment_status'] = $this->config->get('module_so_onepagecheckout_require_comment_status');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_newsletter'])) {
			$data['show_newsletter'] = $this->request->post['module_so_onepagecheckout_show_newsletter'];
		} else {
			$data['show_newsletter'] = $this->config->get('module_so_onepagecheckout_show_newsletter');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_privacy'])) {
			$data['show_privacy'] = $this->request->post['module_so_onepagecheckout_show_privacy'];
		} else {
			$data['show_privacy'] = $this->config->get('module_so_onepagecheckout_show_privacy');
		}

		if (isset($this->request->post['module_so_onepagecheckout_show_term'])) {
			$data['show_term'] = $this->request->post['module_so_onepagecheckout_show_term'];
		} else {
			$data['show_term'] = $this->config->get('module_so_onepagecheckout_show_term');
		}

		$data['payment_methods']	= $this->model_extension_module_so_onepagecheckout->getPaymentMethods();
		$data['shipping_methods']	= $this->model_extension_module_so_onepagecheckout->getShippingMethods();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/so_onepagecheckout', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/so_onepagecheckout')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['module_so_onepagecheckout_name']) < 3) || (utf8_strlen($this->request->post['module_so_onepagecheckout_name']) > 64)) {
			$this->error['error_name'] = $this->language->get('error_name');
			$this->error['warning'] = $this->language->get('error_warning');
		}

		if (!empty($this->request->post['product_image_width'])) {
			if (!is_numeric($this->request->post['product_image_width'])) {
				$this->error['error_product_image_width'] = $this->language->get('error_product_image_width');
				$this->error['warning'] = $this->language->get('error_warning');
			}
		}

		if (!empty($this->request->post['product_image_height'])) {
			if (!is_numeric($this->request->post['product_image_height'])) {
				$this->error['error_product_image_height'] = $this->language->get('error_product_image_height');
				$this->error['warning'] = $this->language->get('error_warning');
			}
		}
		
		return !$this->error;
	}

	function install() {
		$this->load->model('setting/setting');
		
		$setting_data	= array(
			'module_so_onepagecheckout_status'	=> 1,
			'module_so_onepagecheckout_name'		=> 'So Onepage Checkout',
			'module_so_onepagecheckout_layout'		=> 1,
			'module_so_onepagecheckout_country_id'	=> 223,
			'module_so_onepagecheckout_zone_id'	=> 3655,	
			'module_so_onepagecheckout_register_checkout'	=> 1,
			'module_so_onepagecheckout_guest_checkout'	=> 1,
			'module_so_onepagecheckout_login_checkout'	=> 1,
			'module_so_onepagecheckout_account_open'	=> 'register',
			'module_so_onepagecheckout_shopping_cart_status'	=> 1,
			'module_so_onepagecheckout_show_product_weight'	=> 1,
			'module_so_onepagecheckout_show_product_qnty_update'	=> 1,
			'module_so_onepagecheckout_show_product_removecart'	=> 1,
			'module_so_onepagecheckout_product_image_width'	=> 80,
			'module_so_onepagecheckout_product_image_height'	=> 80,
			'module_so_onepagecheckout_coupon_login_status'	=> 1,
			'module_so_onepagecheckout_coupon_register_status'	=> 1,
			'module_so_onepagecheckout_coupon_guest_status'	=> 1,
			'module_so_onepagecheckout_reward_login_status'	=> 1,
			'module_so_onepagecheckout_reward_register_status'	=> 1,
			'module_so_onepagecheckout_reward_guest_status'	=> 1,
			'module_so_onepagecheckout_voucher_login_status'	=> 1,
			'module_so_onepagecheckout_voucher_register_status'	=> 1,
			'module_so_onepagecheckout_voucher_guest_status'	=> 1,
			'module_so_onepagecheckout_delivery_method_status'	=> 1,
			'module_so_onepagecheckout_default_shipping'	=> 'flat',
			'module_so_onepagecheckout_flat_status'	=> 1,
			'module_so_onepagecheckout_free_status'	=> 1,
			'module_so_onepagecheckout_payment_method_status'	=> 1,
			'module_so_onepagecheckout_default_payment'	=> 'bank_transfer',
			'module_so_onepagecheckout_bank_transfer_status'	=> 1,
			'module_so_onepagecheckout_cod_status'	=> 1,
			'module_so_onepagecheckout_comment_status'	=> 1,
			'module_so_onepagecheckout_require_comment_status'	=> 1,
			'module_so_onepagecheckout_show_newsletter'	=> 1,
			'module_so_onepagecheckout_show_privacy'	=> 1,
			'module_so_onepagecheckout_show_term'	=> 1
		);

		$this->model_setting_setting->editSetting('module_so_onepagecheckout', $setting_data);
	}

	function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_so_onepagecheckout');
		$this->model_setting_module->deleteModulesByCode('so_onepagecheckout');
	}
}