<?php
class ControllerExtensionModulePtproducts extends Controller {
    private $error = array();

    public function index() {

        $this->load->language('extension/module/ptproducts');

        $this->document->setTitle($this->language->get('page_title'));

        $this->load->model('setting/module');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');
        $this->load->model('tool/image');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('ptproducts', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('plaza/module', 'user_token=' . $this->session->data['user_token']));
        }

        $this->load->model('localisation/language');

        $data['languages'] = array();

        $languages = $this->model_localisation_language->getLanguages();

        foreach ($languages as $language){
            if ($language['status']) {
                $data['languages'][] = array(
                    'name'  => $language['name'],
                    'language_id' => $language['language_id'],
                    'code' => $language['code']
                );
            }
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['width'])) {
            $data['error_width'] = $this->error['width'];
        } else {
            $data['error_width'] = '';
        }

        if (isset($this->error['height'])) {
            $data['error_height'] = $this->error['height'];
        } else {
            $data['error_height'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('plaza/module', 'user_token=' . $this->session->data['user_token'])
        );

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/ptproducts', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/ptproducts', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/ptproducts', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/ptproducts', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('plaza/module', 'user_token=' . $this->session->data['user_token']);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['module_title'])) {
            $data['module_title'] = $this->request->post['module_title'];
        } elseif (!empty($module_info)) {
            $data['module_title'] = $module_info['module_title'];
        } else {
            $data['module_title'] = array();
        }

        if (isset($this->request->post['show_module_description'])) {
            $data['show_module_description'] = $this->request->post['show_module_description'];
        } elseif (!empty($module_info)) {
            $data['show_module_description'] = $module_info['show_module_description'];
        } else {
            $data['show_module_description'] = '';
        }

        if (isset($this->request->post['module_description'])) {
            $data['module_description'] = $this->request->post['module_description'];
        } elseif (!empty($module_info)) {
            $data['module_description'] = $module_info['module_description'];
        } else {
            $data['module_description'] = array();
        }

        if (isset($this->request->post['module_type'])) {
            $data['module_type'] = $this->request->post['module_type'];
        } elseif (!empty($module_info)) {
            $data['module_type'] = $module_info['module_type'];
        } else {
            $data['module_type'] = 'single_tab';
        }

        if (isset($this->request->post['single_product_collection'])) {
            $data['single_product_collection'] = $this->request->post['single_product_collection'];
        } elseif (!empty($module_info)) {
            $data['single_product_collection'] = $module_info['single_product_collection'];
        } else {
            $data['single_product_collection'] = 'specified';
        }

        $data['single_specified_products'] = array();

        if (!empty($this->request->post['single_specified_products'])) {
            $single_specified_products = $this->request->post['single_specified_products'];
        } elseif (!empty($module_info['single_specified_products'])) {
            $single_specified_products = $module_info['single_specified_products'];
        } else {
            $single_specified_products = array();
        }

        foreach ($single_specified_products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                $data['single_specified_products'][] = array(
                    'product_id' => $product_info['product_id'],
                    'name'       => $product_info['name']
                );
            }
        }

        if (isset($this->request->post['single_category'])) {
            $data['single_category'] = $this->request->post['single_category'];
        } elseif (!empty($module_info)) {
            $data['single_category'] = $module_info['single_category'];
        } else {
            $data['single_category'] = 20;
        }

        $data['categories'] = array();

        $all_cate_count = $this->model_catalog_category->getTotalCategories();

        $filter_data = array(
            'start' => 0,
            'limit' => (int) $all_cate_count
        );

        $categories = $this->model_catalog_category->getCategories($filter_data);

        foreach ($categories as $category) {
            $category_info = $this->model_catalog_category->getCategory($category['category_id']);

            if ($category_info) {
                $data['categories'][] = array(
                    'category_id' => $category_info['category_id'],
                    'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
                );
            }
        }

        if (isset($this->request->post['single_category_product_type'])) {
            $data['single_category_product_type'] = $this->request->post['single_category_product_type'];
        } elseif (!empty($module_info)) {
            $data['single_category_product_type'] = $module_info['single_category_product_type'];
        } else {
            $data['single_category_product_type'] = 'all';
        }

        $data['single_category_products'] = array();

        if (!empty($this->request->post['single_category_products'])) {
            $single_category_products = $this->request->post['single_category_products'];
        } elseif (!empty($module_info['single_category_products'])) {
            $single_category_products = $module_info['single_category_products'];
        } else {
            $single_category_products = array();
        }

        foreach ($single_category_products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if ($product_info) {
                $data['single_category_products'][] = array(
                    'product_id' => $product_info['product_id'],
                    'name'       => $product_info['name']
                );
            }
        }

        if (isset($this->request->post['single_category_product_special_type'])) {
            $data['single_category_product_special_type'] = $this->request->post['single_category_product_special_type'];
        } elseif (!empty($module_info)) {
            $data['single_category_product_special_type'] = $module_info['single_category_product_special_type'];
        } else {
            $data['single_category_product_special_type'] = '';
        }

        $this->document->addStyle('view/stylesheet/plaza/themeadmin.css');
        $this->document->addScript('view/javascript/plaza/switch-toggle/js/bootstrap-toggle.min.js');
        $this->document->addScript('view/javascript/plaza/selection/js/bootstrap-select.min.js');
        $this->document->addStyle('view/javascript/plaza/switch-toggle/css/bootstrap-toggle.min.css');
        $this->document->addStyle('view/javascript/plaza/selection/css/bootstrap-select.min.css');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('plaza/module/ptproducts', $data));
    }

    public function autoGetProductsByCategory() {
        $this->load->model('plaza/catalog');
        $this->load->model('tool/image');

        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['category_id'])) {
            $category_id = $this->request->get['category_id'];
        } else {
            $category_id = '';
        }

        $filter_data = array(
            'filter_category_id' => $category_id,
            'filter_name'  => $filter_name,
            'start'        => 0,
            'limit'        => 5
        );

        $results = $this->model_plaza_catalog->getProductsByCategory($filter_data);

        foreach ($results as $result) {
            $image = $this->model_tool_image->resize($result['image'],40,40);
            $json[] = array(
                'product_id' => $result['product_id'],
                'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                'image'      => $image,
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ptproducts')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

//        if (!$this->request->post['width']) {
//            $this->error['width'] = $this->language->get('error_width');
//        }
//
//        if (!$this->request->post['height']) {
//            $this->error['height'] = $this->language->get('error_height');
//        }

        return !$this->error;
    }
}