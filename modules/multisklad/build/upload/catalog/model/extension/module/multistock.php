<?php
class ModelExtensionModuleMultistock extends Model {

    // Получение остатков товара на всех складах
    // public function getProductStocks($product_id) {
    //     $result = array();
    //     $query = $this->db->query("SELECT ps.id_storage, ps.stock, s.name_storage, s.nick
    //                               FROM `1c_mart_product_stock` ps
    //                               LEFT JOIN `1c_mart_storage` s ON ps.id_storage = s.id_storage
    //                               WHERE ps.id_product = '" . (int)$product_id . "'
    //                               AND ps.stock > 0
    //                               ORDER BY s.name_storage");

    //     foreach ($query->rows as $row) {
    //         $result[$row['id_storage']] = array(
    //             'quantity' => $row['stock'],
    //             'name' => $row['name_storage'],
    //             'nick' => $row['nick']
    //         );
    //     }
    //     return $result;
    // }

    public function getProductStocks($product_id) {
        $result = array();

        // Получаем ВСЕ склады (даже те, где нет остатков)
        $warehouses = $this->db->query("SELECT * FROM `1c_mart_storage` ORDER BY name_storage");

        foreach ($warehouses->rows as $warehouse) {
            // Получаем остаток для этого склада
            $stock_query = $this->db->query("SELECT stock FROM `1c_mart_product_stock`
                                            WHERE id_product = '" . (int)$product_id . "'
                                            AND id_storage = '" . (int)$warehouse['id_storage'] . "'");

            $quantity = ($stock_query->num_rows > 0) ? (int)$stock_query->row['stock'] : 0;

            $result[$warehouse['id_storage']] = array(
                'quantity' => $quantity,
                'name' => $warehouse['name_storage'],
                'nick' => $warehouse['nick']
            );
        }

        return $result;
    }

    // Получение общего остатка товара
    public function getTotalProductStock($product_id) {
        $query = $this->db->query("SELECT SUM(stock) as total
                                  FROM `1c_mart_product_stock`
                                  WHERE id_product = '" . (int)$product_id . "'");
        return $query->row['total'] ? $query->row['total'] : 0;
    }

    // Обновление остатков (для списания при заказе)
    // public function updateProductStock($product_id, $warehouse_id, $quantity) {
    //     if ($quantity <= 0) {
    //         $this->db->query("DELETE FROM `1c_mart_product_stock`
    //                           WHERE id_product = '" . (int)$product_id . "'
    //                           AND id_storage = '" . (int)$warehouse_id . "'");
    //     } else {
    //         $this->db->query("UPDATE `1c_mart_product_stock`
    //                           SET stock = '" . (int)$quantity . "'
    //                           WHERE id_product = '" . (int)$product_id . "'
    //                           AND id_storage = '" . (int)$warehouse_id . "'");
    //     }
    // }

    public function updateProductStock($product_id, $warehouse_id, $quantity) {
        $quantity = (int)$quantity;

        $this->db->query("INSERT INTO `1c_mart_product_stock`
                          (id_product, id_storage, stock)
                          VALUES ('" . (int)$product_id . "', '" . (int)$warehouse_id . "', '" . $quantity . "')
                          ON DUPLICATE KEY UPDATE stock = '" . $quantity . "'");
    }

    // Получение списка всех складов
    public function getAllStorages() {
        $query = $this->db->query("SELECT * FROM `1c_mart_storage` ORDER BY name_storage");
        return $query->rows;
    }
}
?>