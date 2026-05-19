<?php
class ControllerExtensionModuleMultistock extends Controller {

    public function index() {
        $this->load->language('extension/module/multistock');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');
        $this->load->model('extension/module/multistock');

        // Сохранение формы
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            // Обработка действий со складами
            if (isset($this->request->post['create_tables'])) {
                $this->model_extension_module_multistock->createTables();
                $this->session->data['success'] = $this->language->get('text_tables_created');
            }

            if (isset($this->request->post['add_storage'])) {
                $this->model_extension_module_multistock->addStorage(
                    $this->request->post['storage_name'],
                    $this->request->post['storage_nick'],
                    $this->request->post['storage_desc']
                );
                $this->session->data['success'] = $this->language->get('text_storage_added');
            }

            if (isset($this->request->post['delete_storage']) && isset($this->request->post['delete_ids'])) {
                foreach ($this->request->post['delete_ids'] as $id) {
                    $this->model_extension_module_multistock->deleteStorage($id);
                }
                $this->session->data['success'] = $this->language->get('text_storage_deleted');
            }

            // Сохраняем настройки модуля
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('multistock', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        // Получаем данные модуля
        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        $data['heading_title'] = $this->language->get('heading_title');

        // Поля формы
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info) && isset($module_info['name'])) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info) && isset($module_info['status'])) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = 1;
        }

        // Получаем список складов
        $storages = $this->model_extension_module_multistock->getStorages();
        $data['storages'] = $storages ? $storages : [];

        $data['user_token'] = $this->session->data['user_token'];

        // URL для действия формы
        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/multistock', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/multistock', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Хлебные крошки
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/multistock', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/multistock', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/multistock')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('extension/module/multistock');
        $this->model_extension_module_multistock->createTables();
    }

    public function uninstall() {
        $this->load->model('extension/module/multistock');
        $this->model_extension_module_multistock->deleteTables();
    }
}