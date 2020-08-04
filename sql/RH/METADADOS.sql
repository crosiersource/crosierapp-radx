SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `rh_colaborador`;

CREATE TABLE `rh_colaborador`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `nome`               VARCHAR(255) NOT NULL,
    `cpf`                varchar(20)  NOT NULL,
    `atual`              tinyint(1)   NOT NULL,
    `image_name`         varchar(255),
    `json_data`          json,

    UNIQUE KEY `UK_rh_colaborador` (`cpf`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_rh_colaborador_estabelecimento` (`estabelecimento_id`),
    KEY `K_rh_colaborador_user_inserted` (`user_inserted_id`),
    KEY `K_rh_colaborador_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_rh_colaborador_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_rh_colaborador_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_rh_colaborador_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;