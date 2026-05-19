CREATE TABLE IF NOT EXISTS `1c_mart_storage` (
    `id_storage` INT(11) NOT NULL AUTO_INCREMENT,
    `name_storage` varchar(128) NOT NULL,
    `nick` varchar(128) DEFAULT NULL,
    `desc_storage` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id_storage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `1c_mart_product_stock` (
    `id_product` INT(11) NOT NULL,
    `id_storage` INT(11) NOT NULL,
    `stock` int(11) NOT NULL DEFAULT '0',
    UNIQUE KEY `id_product` (`id_product`,`id_storage`),
    KEY `id_storage` (`id_storage`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Добавляем тестовый склад (опционально)
INSERT INTO `1c_mart_storage` (`name_storage`, `nick`, `desc_storage`)
VALUES ('Основной склад', 'main', 'Главный склад по умолчанию');