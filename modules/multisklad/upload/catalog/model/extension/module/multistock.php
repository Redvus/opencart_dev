<?php
class ModelExtensionModuleMultistock extends Model {

    // Получение остатков товара на всех складах
    public function getProductStocks($product_id) {
        $result = array();
        $query = $this->db->query("SELECT ps.id_storage, ps.stock, s.name_storage, s.nick
                                  FROM `" . DB_PREFIX . "1c_mart_product_stock` ps
                                  LEFT JOIN `" . DB_PREFIX . "1c_mart_storage` s ON ps.id_storage = s.id_storage
                                  WHERE ps.id_product = '" . (int)$product_id . "'
                                  AND ps.stock > 0
                                  ORDER BY s.name_storage");

        foreach ($query->rows as $row) {
            $result[$row['id_storage']] = array(
                'quantity' => $row['stock'],
                'name' => $row['name_storage'],
                'nick' => $row['nick']
            );
        }
        return $result;
    }

    // Получение общего остатка товара
    public function getTotalProductStock($product_id) {
        $query = $this->db->query("SELECT SUM(stock) as total
                                  FROM `" . DB_PREFIX . "1c_mart_product_stock`
                                  WHERE id_product = '" . (int)$product_id . "'");
        return $query->row['total'] ? $query->row['total'] : 0;
    }

    // Обновление остатков (для списания при заказе)
    public function updateProductStock($product_id, $warehouse_id, $quantity) {
        if ($quantity <= 0) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "1c_mart_product_stock`
                              WHERE id_product = '" . (int)$product_id . "'
                              AND id_storage = '" . (int)$warehouse_id . "'");
        } else {
            $this->db->query("UPDATE `" . DB_PREFIX . "1c_mart_product_stock`
                              SET stock = '" . (int)$quantity . "'
                              WHERE id_product = '" . (int)$product_id . "'
                              AND id_storage = '" . (int)$warehouse_id . "'");
        }
    }

    // Получение списка всех складов
    public function getAllStorages() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "1c_mart_storage` ORDER BY name_storage");
        return $query->rows;
    }
}
?>