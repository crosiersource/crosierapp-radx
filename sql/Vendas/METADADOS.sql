SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `ven_plano_pagto`;

CREATE TABLE `ven_plano_pagto`
(
    `id`                 bigint(20)                           NOT NULL AUTO_INCREMENT,
    `codigo`             varchar(255) COLLATE utf8_swedish_ci NOT NULL,
    `descricao`          varchar(255) COLLATE utf8_swedish_ci NOT NULL,

    UNIQUE KEY `UK_ven_plano_pagto_codigo` (`codigo`),
    UNIQUE KEY `UK_ven_plano_pagto_descricao` (`descricao`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime                             NOT NULL,
    `updated`            datetime                             NOT NULL,
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20)                           NOT NULL,
    `user_inserted_id`   bigint(20)                           NOT NULL,
    `user_updated_id`    bigint(20)                           NOT NULL,
    KEY `K_ven_plano_pagto_estabelecimento` (`estabelecimento_id`),
    KEY `K_ven_plano_pagto_user_inserted` (`user_inserted_id`),
    KEY `K_ven_plano_pagto_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_ven_plano_pagto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_ven_plano_pagto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_ven_plano_pagto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `ven_tipo_venda`;

CREATE TABLE `ven_tipo_venda`
(
    `id`                 bigint(20)                           NOT NULL AUTO_INCREMENT,
    `descricao`          varchar(100) COLLATE utf8_swedish_ci NOT NULL,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime                             NOT NULL,
    `updated`            datetime                             NOT NULL,
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20)                           NOT NULL,
    `user_inserted_id`   bigint(20)                           NOT NULL,
    `user_updated_id`    bigint(20)                           NOT NULL,
    KEY `K_ven_tipo_venda_estabelecimento` (`estabelecimento_id`),
    KEY `K_ven_tipo_venda_user_inserted` (`user_inserted_id`),
    KEY `K_ven_tipo_venda_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_ven_tipo_venda_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_ven_tipo_venda_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_ven_tipo_venda_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),

    UNIQUE KEY `UK_ven_tipo_venda` (`descricao`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


DROP TABLE IF EXISTS `ven_venda`;

CREATE TABLE `ven_venda`
(
    `id`                 bigint(20)                          NOT NULL AUTO_INCREMENT,
    `dt_venda`           datetime                            NOT NULL,
    `pv`                 int(11)                               DEFAULT NULL,
    `sub_total`          decimal(19, 2)                      NOT NULL,
    `valor_total`        decimal(19, 2)                      NOT NULL,
    `desconto_especial`  decimal(19, 2)                      NOT NULL,
    `desconto_plano`     decimal(19, 2)                      NOT NULL,
    `historico_desconto`  varchar(2000) COLLATE utf8_swedish_ci DEFAULT NULL,
    `plano_pagto_id`     bigint(20)                          NOT NULL,
    `vendedor_id`        bigint(20)                          NOT NULL,
    `tipo_venda_id`      bigint(20)                          NOT NULL,
    `cliente_id`         bigint(20)                            DEFAULT NULL,
    `status`             varchar(30) COLLATE utf8_swedish_ci NOT NULL,
    `obs`                varchar(3000) COLLATE utf8_swedish_ci DEFAULT NULL,

    UNIQUE KEY `UK_ven_venda` (`pv`, `mesano`),
    KEY `K_ven_venda_vendedor` (`vendedor_id`),
    KEY `K_ven_venda_tipo_venda` (`tipo_venda_id`),
    KEY `K_ven_venda_plano_pagto` (`plano_pagto_id`),
    KEY `K_ven_venda_mesano` (`mesano`),
    CONSTRAINT `FK_ven_venda_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `rh_funcionario` (`id`),
    CONSTRAINT `FK_ven_venda_tipo_venda` FOREIGN KEY (`tipo_venda_id`) REFERENCES `ven_tipo_venda` (`id`),
    CONSTRAINT `FK_ven_venda_plano_pagto` FOREIGN KEY (`plano_pagto_id`) REFERENCES `ven_plano_pagto` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime                            NOT NULL,
    `updated`            datetime                            NOT NULL,
    `version`            int(11)                               DEFAULT NULL,
    `estabelecimento_id` bigint(20)                          NOT NULL,
    `user_inserted_id`   bigint(20)                          NOT NULL,
    `user_updated_id`    bigint(20)                          NOT NULL,
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
    `produto_id`         bigint(20)                            DEFAULT NULL,
    `obs`                varchar(5000) COLLATE utf8_swedish_ci DEFAULT NULL,
    `ordem`              int(11)                               DEFAULT NULL,
    `alteracao_preco`    bit(1)         NOT NULL,
    `preco_venda`        decimal(19, 2) NOT NULL,
    `preco_custo`        decimal(19, 2)                        DEFAULT NULL,
    `dt_custo`           datetime                              DEFAULT NULL,
    `qtde`               decimal(19, 2) NOT NULL,
    `nc_descricao`       varchar(200) COLLATE utf8_swedish_ci  DEFAULT NULL,
    `nc_reduzido`        bigint(20)                            DEFAULT NULL,
    `nc_grade_tamanho`   varchar(200) COLLATE utf8_swedish_ci  DEFAULT NULL,
    `ncm`                varchar(20) COLLATE utf8_swedish_ci   DEFAULT NULL,
    `ncm_existente`      bit(1)                                DEFAULT NULL,

    KEY `K_ven_venda_item_produto` (`produto_id`),
    KEY `K_ven_venda_item_venda` (`venda_id`),
    CONSTRAINT `FK_ven_venda_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),
    CONSTRAINT `FK_ven_venda_item_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11)                               DEFAULT NULL,
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





