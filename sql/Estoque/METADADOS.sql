SET FOREIGN_KEY_CHECKS = 0;



DROP TABLE IF EXISTS `est_unidade`;

CREATE TABLE `est_unidade`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `descricao`          varchar(255) NOT NULL,
    `label`              varchar(10)  NOT NULL,
    `casas_decimais`     int          NOT NULL,
    `atual`              tinyint(1)   NOT NULL,
    `json_info`          varchar(3000), -- informações sobre conversões (não são montados campos customizados aqui)
    `json_data`          json,          -- campo padrão caso sejam necessários campos customizados para algum cliente

    UNIQUE KEY `UK_est_unidade` (`label`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_unidade_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_unidade_user_inserted` (`user_inserted_id`),
    KEY `K_est_unidade_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_unidade_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_unidade_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_unidade_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_depreciacao_preco`;

CREATE TABLE `est_depreciacao_preco`
(
    `id`                 bigint(20) NOT NULL AUTO_INCREMENT,
    `porcentagem`        double     NOT NULL,
    `prazo_fim`          int(11)    NOT NULL,
    `prazo_ini`          int(11)    NOT NULL,
    UNIQUE KEY `UK_est_depreciacao_preco_prazo_ini` (`prazo_ini`),
    UNIQUE KEY `UK_est_depreciacao_preco_prazo_fim` (`prazo_fim`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime   NOT NULL,
    `updated`            datetime   NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20) NOT NULL,
    `user_inserted_id`   bigint(20) NOT NULL,
    `user_updated_id`    bigint(20) NOT NULL,
    KEY `K_est_depreciacao_preco_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_depreciacao_preco_user_inserted` (`user_inserted_id`),
    KEY `K_est_depreciacao_preco_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_depreciacao_preco_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_depreciacao_preco_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_depreciacao_preco_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


-- DEPTO >> GRUPO >> SUBGRUPO

DROP TABLE IF EXISTS `est_depto`;
CREATE TABLE `est_depto`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `uuid`               char(36)     NOT NULL,
    `codigo`             varchar(50)  NOT NULL,
    `nome`               varchar(255) NOT NULL,
    `json_data`          json,

    UNIQUE KEY `UK_est_depto_uuid` (`uuid`),
    UNIQUE KEY `UK_est_depto_codigo` (`codigo`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_depto_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_depto_user_inserted` (`user_inserted_id`),
    KEY `K_est_depto_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_depto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_depto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_depto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_grupo`;
CREATE TABLE `est_grupo`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `uuid`               char(36)     NOT NULL,
    `depto_id`           bigint(20)   NOT NULL,
    `codigo`             varchar(50)  NOT NULL,
    `nome`               varchar(255) NOT NULL,
    `json_data`          json,

    KEY `K_est_grupo_depto` (`depto_id`),
    CONSTRAINT `FK_est_grupo_depto` FOREIGN KEY (`depto_id`) REFERENCES `est_depto` (`id`),

    UNIQUE KEY `UK_est_grupo_codigo` (`codigo`, `depto_id`),
    UNIQUE KEY `UK_est_grupo_uuid` (`uuid`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_grupo_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_grupo_user_inserted` (`user_inserted_id`),
    KEY `K_est_grupo_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_grupo_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_grupo_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_grupo_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_subgrupo`;
CREATE TABLE `est_subgrupo`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `uuid`               char(36)     NOT NULL,
    `codigo`             varchar(50)  NOT NULL,
    `nome`               varchar(255) NOT NULL,
    `grupo_id`           bigint(20)   NOT NULL,
    `json_data`          json,


    KEY `K_est_subgrupo_grupo` (`grupo_id`),
    CONSTRAINT `FK_est_subgrupo_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `est_grupo` (`id`),

    UNIQUE KEY `UK_est_subgrupo_codigo` (`codigo`, `grupo_id`),
    UNIQUE KEY `UK_est_subgrupo_uuid` (`uuid`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_subgrupo_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_subgrupo_user_inserted` (`user_inserted_id`),
    KEY `K_est_subgrupo_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_subgrupo_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_subgrupo_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_subgrupo_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_produto`;

CREATE TABLE `est_produto`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

    `codigo`             varchar(50),
    `uuid`               char(36)     NOT NULL,
    `depto_id`           bigint(20)   NOT NULL,
    `grupo_id`           bigint(20)   NOT NULL,
    `subgrupo_id`        bigint(20)   NOT NULL,
    `fornecedor_id`      bigint(20)   NOT NULL,
    `unidade_padrao_id` bigint(20) NOT NULL,
    `nome`               varchar(255) NOT NULL,
    `status`             enum ('ATIVO','INATIVO'),
    `obs`                varchar(5000),
    `composicao`         char(1),
    `json_data`          json,

    UNIQUE KEY `K_est_produto_uuid` (`uuid`),
    UNIQUE KEY `K_est_produto_codigo` (`codigo`),
    KEY `K_est_produto_nome` (`nome`),

    KEY `K_est_produto_depto` (`depto_id`),
    CONSTRAINT `FK_est_produto_depto` FOREIGN KEY (`depto_id`) REFERENCES `est_depto` (`id`),

    KEY `K_est_produto_grupo` (`grupo_id`),
    CONSTRAINT `FK_est_produto_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `est_grupo` (`id`),

    KEY `K_est_produto_subgrupo` (`subgrupo_id`),
    CONSTRAINT `FK_est_produto_subgrupo` FOREIGN KEY (`subgrupo_id`) REFERENCES `est_subgrupo` (`id`),

    KEY `K_est_produto_fornecedor` (`fornecedor_id`),
    CONSTRAINT `FK_est_produto_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `est_fornecedor` (`id`),

    KEY `K_est_produto_unidade` (`unidade_padrao_id`),
    CONSTRAINT `FK_est_produto_unidade` FOREIGN KEY (`unidade_padrao_id`) REFERENCES `est_unidade` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_produto_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_produto_user_inserted` (`user_inserted_id`),
    KEY `K_est_produto_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_produto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_produto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_produto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_produto_imagem`;

CREATE TABLE `est_produto_imagem`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

    `produto_id`         bigint(20)   NOT NULL,
    `image_name`         varchar(255) NOT NULL,
    `descricao`          varchar(255),
    `ordem`              integer,

    UNIQUE KEY `UK_est_produto_imagem` (`produto_id`, `ordem`),
    UNIQUE KEY `UK_est_produto_imagem_image_name` (`image_name`),
    KEY `K_est_produto_imagem_produto` (`produto_id`),
    CONSTRAINT `FK_est_produto_imagem_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_produto_imagem_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_produto_imagem_user_inserted` (`user_inserted_id`),
    KEY `K_est_produto_imagem_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_produto_imagem_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_produto_imagem_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_produto_imagem_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_lista_preco`;

CREATE TABLE `est_lista_preco`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `descricao`          varchar(255) NOT NULL,
    `dt_vigencia_ini`    datetime     NOT NULL,
    `dt_vigencia_fim`    datetime,


    UNIQUE KEY `UK_est_lista_preco` (`descricao`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_lista_preco_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_lista_preco_user_inserted` (`user_inserted_id`),
    KEY `K_est_lista_preco_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_lista_preco_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_lista_preco_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_lista_preco_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


DROP TABLE IF EXISTS `est_produto_preco`;

CREATE TABLE `est_produto_preco`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `lista_id`           bigint(20)     NOT NULL,
    `produto_id`         bigint(20)     NOT NULL,
    `unidade_id`         bigint(20)     NOT NULL,
    `coeficiente`        decimal(15, 2) NOT NULL,
    `custo_operacional`  decimal(15, 2) NOT NULL,
    `dt_custo`           date           NOT NULL,
    `dt_preco_venda`     date           NOT NULL,
    `margem`             decimal(15, 2) NOT NULL,
    `prazo`              int(11)        NOT NULL,
    `preco_custo`        decimal(15, 2) NOT NULL,
    `preco_prazo`        decimal(15, 2) NOT NULL,
    `preco_promo`        decimal(15, 2),
    `preco_vista`        decimal(15, 2) NOT NULL,
    `custo_financeiro`   decimal(15, 2) NOT NULL,
    `atual`              tinyint(1)     NOT NULL,
    `json_data`          json,

    UNIQUE KEY `UK_est_produto_preco` (`produto_id`, `lista_id`, `unidade_id`),

    KEY `K_est_produto_preco_produto` (`produto_id`),
    CONSTRAINT `FK_est_produto_preco_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    KEY `K_est_produto_preco_unidade` (`unidade_id`),
    CONSTRAINT `FK_est_produto_preco_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `est_unidade` (`id`),

    KEY `K_est_produto_preco_lista` (`lista_id`),
    CONSTRAINT `FK_est_produto_preco_lista` FOREIGN KEY (`lista_id`) REFERENCES `est_lista_preco` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_produto_preco_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_produto_preco_user_inserted` (`user_inserted_id`),
    KEY `K_est_produto_preco_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_produto_preco_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_produto_preco_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_produto_preco_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_produto_composicao`;

CREATE TABLE `est_produto_composicao`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `produto_pai_id`     bigint(20)     NOT NULL,
    `produto_filho_id`   bigint(20)     NOT NULL,
    `ordem`              integer        NOT NULL,
    `qtde`               decimal(15, 3) NOT NULL,
    `preco_atual`        decimal(15, 2) NOT NULL,
    `preco_composicao`   decimal(15, 2) NOT NULL,

    UNIQUE KEY `UK_est_produto_composicao` (`produto_pai_id`, `produto_filho_id`),

    CONSTRAINT `FK_est_produto_composicao_produto_pai` FOREIGN KEY (`produto_pai_id`) REFERENCES `est_produto` (`id`),
    CONSTRAINT `FK_est_produto_composicao_produto_filho` FOREIGN KEY (`produto_filho_id`) REFERENCES `est_produto` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_produto_composicao_user_inserted` (`user_inserted_id`),
    CONSTRAINT `FK_est_produto_composicao_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    KEY `K_est_produto_composicao_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_produto_composicao_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),
    KEY `K_est_produto_composicao_estabelecimento` (`estabelecimento_id`),
    CONSTRAINT `FK_est_produto_composicao_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_produto_saldo`;

CREATE TABLE `est_produto_saldo`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `produto_id`         bigint(20)     NOT NULL,
    `unidade_id`         bigint(20)     NOT NULL,
    `qtde`               decimal(15, 2) NOT NULL,
    `json_data`          json,

    KEY `K_est_produto_saldo_produto` (`produto_id`),
    CONSTRAINT `FK_est_produto_saldo_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    KEY `K_est_produto_saldo_unidade` (`unidade_id`),
    CONSTRAINT `FK_est_produto_saldo_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `est_unidade` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_produto_saldo_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_produto_saldo_user_inserted` (`user_inserted_id`),
    KEY `K_est_produto_saldo_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_produto_saldo_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_produto_saldo_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_produto_saldo_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `est_fornecedor`;

CREATE TABLE `est_fornecedor`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

    `nome`               VARCHAR(255) NOT NULL,
    `nome_fantasia`      VARCHAR(255),
    `documento`          varchar(20),
    `inscricao_estadual` varchar(20),
    `json_data`          json,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_fornecedor_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_fornecedor_user_inserted` (`user_inserted_id`),
    KEY `K_est_fornecedor_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_fornecedor_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_fornecedor_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_fornecedor_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `est_pedidocompra`;

CREATE TABLE `est_pedidocompra`
(
    `id`                 bigint(20)  NOT NULL AUTO_INCREMENT,
    `dt_emissao`         datetime    NOT NULL,
    `dt_prev_entrega`    datetime,
    `prazos_pagto`       varchar(50),
    `responsavel`        varchar(80),
    `fornecedor_id`      bigint(20),
    `fornecedor_nome`    VARCHAR(200) GENERATED ALWAYS AS (`json_data` ->> '$.fornecedor_nome'),
    `subtotal`           decimal(15, 2),
    `desconto`           decimal(15, 2),
    `total`              decimal(15, 2),
    `status`             varchar(30) NOT NULL,
    `obs`                varchar(3000),
    `json_data`          json,

    KEY `K_est_pedidocompra_fornecedor` (`fornecedor_id`),
    CONSTRAINT `FK_est_pedidocompra_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `est_fornecedor` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime    NOT NULL,
    `updated`            datetime    NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)  NOT NULL,
    `user_inserted_id`   bigint(20)  NOT NULL,
    `user_updated_id`    bigint(20)  NOT NULL,
    KEY `K_est_pedidocompra_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_pedidocompra_user_inserted` (`user_inserted_id`),
    KEY `K_est_pedidocompra_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_pedidocompra_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_pedidocompra_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_pedidocompra_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_pedidocompra_item`;

CREATE TABLE `est_pedidocompra_item`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `pedidocompra_id`    bigint(20)     NOT NULL,
    `ordem`              int(11)        NOT NULL,
    `qtde`               decimal(15, 2) NOT NULL,
    `referencia`         varchar(50),
    `descricao`          varchar(500)   NOT NULL,
    `preco_custo`        decimal(15, 2),
    `desconto`           decimal(15, 2),
    `total`              decimal(15, 2),
    `json_data`          json,

    KEY `K_est_pedidocompra_item_pedidocompra` (`pedidocompra_id`),
    CONSTRAINT `FK_est_pedidocompra_item_pedidocompra` FOREIGN KEY (`pedidocompra_id`) REFERENCES `est_pedidocompra` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_pedidocompra_item_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_pedidocompra_item_user_inserted` (`user_inserted_id`),
    KEY `K_est_pedidocompra_item_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_pedidocompra_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_pedidocompra_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_pedidocompra_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `est_romaneio`;

CREATE TABLE `est_romaneio`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `fornecedor_id`      bigint(20),
    `notafiscal_id`      bigint(20),
    `status`             varchar(100) NOT NULL,
    `dt_emissao`         datetime     NOT NULL,
    `dt_prev_entrega`    datetime,
    `dt_entrega`         datetime,
    `prazos_pagto`       varchar(100),
    `valor_total`        decimal(15, 2),
    `json_data`          json,

    KEY `K_est_romaneio_fornecedor` (`fornecedor_id`),
    CONSTRAINT `FK_est_romaneio_fornecedor` FOREIGN KEY (`fornecedor_id`) REFERENCES `est_fornecedor` (`id`),

    KEY `K_est_romaneio_notafiscal` (`notafiscal_id`),
    CONSTRAINT `FK_est_romaneio_notafiscal` FOREIGN KEY (`notafiscal_id`) REFERENCES `fis_nf` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_romaneio_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_romaneio_user_inserted` (`user_inserted_id`),
    KEY `K_est_romaneio_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_romaneio_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_romaneio_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_romaneio_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_romaneio_item`;

CREATE TABLE `est_romaneio_item`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `romaneio_id`        bigint(20)     NOT NULL,
    `produto_id`         bigint(20),
    `ordem`              int(11)        NOT NULL,
    `qtde`               decimal(15, 2) NOT NULL,
    `qtde_conferida`     decimal(15, 2),
    `descricao`          varchar(3000)  NOT NULL,
    `preco_custo`        decimal(15, 2),
    `total`              decimal(15, 2),
    `json_data`          json,

    KEY `K_est_romaneio_item_romaneio` (`romaneio_id`),
    CONSTRAINT `FK_est_romaneio_item_romaneio` FOREIGN KEY (`romaneio_id`) REFERENCES `est_romaneio` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    KEY `K_est_romaneio_item_produto` (`produto_id`),
    CONSTRAINT `FK_est_romaneio_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_romaneio_item_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_romaneio_item_user_inserted` (`user_inserted_id`),
    KEY `K_est_romaneio_item_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_romaneio_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_romaneio_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_romaneio_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `est_entrada`;

CREATE TABLE `est_entrada`
(
    `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
    `dt_lote`            datetime     NOT NULL,
    `descricao`          varchar(255) NOT NULL,
    `responsavel`        varchar(255) NOT NULL,
    `status`             varchar(100) NOT NULL, -- [ABERTO,CANCELADO,INTEGRADO]
    `dt_integracao`      datetime,
    `json_data`          json,


    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime     NOT NULL,
    `updated`            datetime     NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)   NOT NULL,
    `user_inserted_id`   bigint(20)   NOT NULL,
    `user_updated_id`    bigint(20)   NOT NULL,
    KEY `K_est_entrada_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_entrada_user_inserted` (`user_inserted_id`),
    KEY `K_est_entrada_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_entrada_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_entrada_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_entrada_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `est_entrada_item`;

CREATE TABLE `est_entrada_item`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `entrada_id`         bigint(20)     NOT NULL,
    `produto_id`         bigint(20),
    `unidade_id`         bigint(20),
    `qtde`               decimal(15, 2) NOT NULL,
    `json_data`          json,

    KEY `K_est_entrada_item_entrada` (`entrada_id`),
    CONSTRAINT `FK_est_entrada_item_entrada` FOREIGN KEY (`entrada_id`) REFERENCES `est_entrada` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

    KEY `K_est_entrada_item_produto` (`produto_id`),
    CONSTRAINT `FK_est_entrada_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),

    KEY `K_est_entrada_item_unidade` (`unidade_id`),
    CONSTRAINT `FK_est_entrada_item_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `est_unidade` (`id`),

    -- campo de controle
    PRIMARY KEY (`id`),
    `inserted`           datetime       NOT NULL,
    `updated`            datetime       NOT NULL,
    `version`            int(11),
    `estabelecimento_id` bigint(20)     NOT NULL,
    `user_inserted_id`   bigint(20)     NOT NULL,
    `user_updated_id`    bigint(20)     NOT NULL,
    KEY `K_est_entrada_item_estabelecimento` (`estabelecimento_id`),
    KEY `K_est_entrada_item_user_inserted` (`user_inserted_id`),
    KEY `K_est_entrada_item_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_est_entrada_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_est_entrada_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_est_entrada_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;

