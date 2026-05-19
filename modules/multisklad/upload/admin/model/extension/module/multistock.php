<?php
class ModelExtensionModuleMultistock extends Model {

    // Создание таблиц (если их нет)
    public function createTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "1c_mart_storage` (
            `id_storage` INT(11) NOT NULL AUTO_INCREMENT,
            `name_storage` varchar(128),
            `nick` varchar(128),
            `desc_storage` varchar(255),
            PRIMARY KEY (`id_storage`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "1c_mart_product_stock` (
            `id_product` INT(11) NOT NULL,
            `id_storage` INT(11) NOT NULL,
            `stock` int(11),
            UNIQUE KEY `id_product` (`id_product`,`id_storage`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;");
    }

    // Удаление таблиц
    public function deleteTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "1c_mart_storage`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "1c_mart_product_stock`");
    }

    // Очистка остатков
    public function clearStock() {
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "1c_mart_product_stock`");
    }

    // Получение списка складов
    public function getStorages() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "1c_mart_storage` ORDER BY name_storage");
        return $query->rows;
    }

    // Получение склада по ID
    public function getStorage($storage_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "1c_mart_storage` WHERE id_storage = '" . (int)$storage_id . "'");
        return $query->row;
    }

    // Добавление склада
    public function addStorage($name, $nick, $desc) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "1c_mart_storage`
                          (name_storage, nick, desc_storage)
                          VALUES ('" . $this->db->escape($name) . "',
                                  '" . $this->db->escape($nick) . "',
                                  '" . $this->db->escape($desc) . "')");
        return $this->db->getLastId();
    }

    // Обновление складов
    public function updateStorages($storages) {
        foreach ($storages as $storage_id => $data) {
            $this->db->query("UPDATE `" . DB_PREFIX . "1c_mart_storage`
                              SET nick = '" . $this->db->escape($data['nick']) . "',
                                  desc_storage = '" . $this->db->escape($data['desc']) . "'
                              WHERE id_storage = '" . (int)$storage_id . "'");
        }
    }

    // Удаление складов
    public function removeStorages($ids) {
        foreach ($ids as $id) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "1c_mart_storage` WHERE id_storage = '" . (int)$id . "'");
            $this->db->query("DELETE FROM `" . DB_PREFIX . "1c_mart_product_stock` WHERE id_storage = '" . (int)$id . "'");
        }
    }

    // Получение остатков товара по всем складам
    public function getProductStocks($product_id) {
        $result = array();
        $query = $this->db->query("SELECT id_storage, stock FROM `" . DB_PREFIX . "1c_mart_product_stock` WHERE id_product = '" . (int)$product_id . "'");
        foreach ($query->rows as $row) {
            $result[$row['id_storage']] = $row['stock'];
        }
        return $result;
    }

    // Обновление остатков товара на складе
    public function updateProductStock($product_id, $warehouse_id, $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            // Если количество 0 или меньше, удаляем запись
            $this->db->query("DELETE FROM `" . DB_PREFIX . "1c_mart_product_stock`
                              WHERE id_product = '" . (int)$product_id . "'
                              AND id_storage = '" . (int)$warehouse_id . "'");
        } else {
            // Обновляем или вставляем
            $this->db->query("INSERT INTO `" . DB_PREFIX . "1c_mart_product_stock`
                              (id_product, id_storage, stock)
                              VALUES ('" . (int)$product_id . "', '" . (int)$warehouse_id . "', '" . $quantity . "')
                              ON DUPLICATE KEY UPDATE stock = '" . $quantity . "'");
        }

        // Обновляем общее количество в таблице product для совместимости
        $total = $this->getTotalProductStock($product_id);
        $this->db->query("UPDATE `" . DB_PREFIX . "product` SET quantity = '" . $total . "' WHERE product_id = '" . (int)$product_id . "'");
    }

    // Получение общего остатка товара
    public function getTotalProductStock($product_id) {
        $query = $this->db->query("SELECT SUM(stock) as total FROM `" . DB_PREFIX . "1c_mart_product_stock` WHERE id_product = '" . (int)$product_id . "'");
        return $query->row['total'] ? $query->row['total'] : 0;
    }
}
?>