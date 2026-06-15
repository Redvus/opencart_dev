<?php
// install.php - выполняется при установке модуля
if (!defined('DIR_APPLICATION')) {
    die('Access Denied');
}

class ControllerExtensionModuleMultistockInstall {
    public function index() {
        $this->load->model('extension/module/multistock');
        $this->model_extension_module_multistock->createTables();
    }
}