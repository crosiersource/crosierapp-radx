SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `crm_cliente`;

CREATE TABLE `crm_cliente`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `nome`               VARCHAR(255) NOT NULL,
    `documento`          varchar(20),
    `json_data`          json,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_crm_cliente_estabelecimento` (`estabelecimento_id`),
    KEY `K_crm_cliente_user_inserted` (`user_inserted_id`),
    KEY `K_crm_cliente_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_crm_cliente_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_crm_cliente_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_crm_cliente_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;