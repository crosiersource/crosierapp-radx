SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `ven_venda`;

CREATE TABLE `ven_venda`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `dt_venda`           datetime       NOT NULL,
    `cliente_id`         bigint(20),
    `valor_total`        decimal(15, 2) NOT NULL,
    `json_data`          json,

    KEY `K_ven_venda_cliente` (`cliente_id`),
    CONSTRAINT `FK_ven_venda_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `crm_cliente` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_ven_venda_estabelecimento` (`estabelecimento_id`),
    KEY `K_ven_venda_user_inserted` (`user_inserted_id`),
    KEY `K_ven_venda_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_ven_venda_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_ven_venda_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_ven_venda_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `ven_venda_item`;

CREATE TABLE `ven_venda_item`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `venda_id`           bigint(20)     NOT NULL,
    `ordem`              int(11),
    `qtde`               decimal(15, 3) NOT NULL,
    `produto_id`         bigint(20),
    `descricao`          varchar(255)   NOT NULL,
    `preco_venda`        decimal(15, 2) NOT NULL,
    `json_data`          json,

    KEY `K_ven_venda_item_produto` (`produto_id`),
    KEY `K_ven_venda_item_venda` (`venda_id`),
    CONSTRAINT `FK_ven_venda_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),
    CONSTRAINT `FK_ven_venda_item_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_ven_venda_item_estabelecimento` (`estabelecimento_id`),
    KEY `K_ven_venda_item_user_inserted` (`user_inserted_id`),
    KEY `K_ven_venda_item_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_ven_venda_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_ven_venda_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_ven_venda_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;





