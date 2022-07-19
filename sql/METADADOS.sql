SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `crm_cliente`;

CREATE TABLE `crm_cliente`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `codigo`             varchar(36)  NOT NULL,
  `nome`               VARCHAR(255) NOT NULL, -- nome ou razao social
  `tipo_pessoa`        char(2),               -- PF/PJ
  `nome_fantasia`      VARCHAR(255),          -- nome ou razao social
  `documento`          varchar(20),           -- cpf ou cnpj
  `ie`                 varchar(30),
  `logradouro`         varchar(255),
  `numero`             varchar(60),
  `complemento`        varchar(60),
  `bairro`             varchar(60),
  `cidade`             varchar(60),
  `estado`             char(2),
  `cep`                varchar(8),
  `fone1`              varchar(50),
  `fone2`              varchar(50),
  `fone3`              varchar(50),
  `fone4`              varchar(50),
  `dt_nascimento`      date,
  `ativo`              tinyint(1)   NOT NULL DEFAULT true,
  `json_data`          json,

  UNIQUE KEY `UK_crm_cliente_codigo` (`codigo`),

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
) ENGINE = InnoDB;



INSERT INTO crm_cliente(id, codigo, nome, documento, inserted, updated, version, estabelecimento_id,
                        user_inserted_id, user_updated_id) value (null, 1,
                                                                  'CONSUMIDOR NÃO IDENTIFICADO',
                                                                  '99999999999', now(), now(), 0, 1,
                                                                  1, 1);



DROP TABLE IF EXISTS `est_unidade`;

CREATE TABLE `est_unidade`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `descricao`          varchar(255) NOT NULL,
  `label`              varchar(10)  NOT NULL,
  `casas_decimais`     int          NOT NULL,
  `fator`              int,
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
) ENGINE = InnoDB;



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
) ENGINE = InnoDB;


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
) ENGINE = InnoDB;



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
) ENGINE = InnoDB;



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
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `est_produto`;

CREATE TABLE `est_produto`
(
  `id`                          bigint(20)   NOT NULL AUTO_INCREMENT,

  `codigo`                      varchar(50),
  `ean`                         varchar(50),
  `referencia`                  varchar(50),
  `uuid`                        char(36)     NOT NULL,
  `depto_id`                    bigint(20)   NOT NULL,
  `grupo_id`                    bigint(20)   NOT NULL,
  `subgrupo_id`                 bigint(20)   NOT NULL,
  `fornecedor_id`               bigint(20)   NOT NULL,
  `unidade_padrao_id`           bigint(20)   NOT NULL,
  `nome`                        varchar(255) NOT NULL,
  `marca`                       varchar(255),
  `status`                      enum ('ATIVO','INATIVO'),
  `obs`                         varchar(5000),
  `composicao`                  char(1),
  `qtde_total`                  decimal(15, 3),
  `qtde_minima`                 decimal(15, 3),
  `ecommerce`                   tinyint(1),
  `dt_ult_integracao_ecommerce` datetime,
  `json_data`                   json,

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
  `inserted`                    datetime     NOT NULL,
  `updated`                     datetime     NOT NULL,
  `version`                     int(11),
  `estabelecimento_id`          bigint(20)   NOT NULL,
  `user_inserted_id`            bigint(20)   NOT NULL,
  `user_updated_id`             bigint(20)   NOT NULL,
  KEY `K_est_produto_estabelecimento` (`estabelecimento_id`),
  KEY `K_est_produto_user_inserted` (`user_inserted_id`),
  KEY `K_est_produto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_est_produto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_est_produto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_est_produto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



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

) ENGINE = InnoDB;



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

) ENGINE = InnoDB;


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

) ENGINE = InnoDB;



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
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `est_produto_saldo`;

CREATE TABLE `est_produto_saldo`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `produto_id`         bigint(20)     NOT NULL,
  `qtde`               decimal(15, 2) NOT NULL,
  `json_data`          json,

  KEY `K_est_produto_saldo_produto` (`produto_id`),
  CONSTRAINT `FK_est_produto_saldo_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

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

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `est_fornecedor`;

CREATE TABLE `est_fornecedor`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

  `codigo`             varchar(36)  NOT NULL,
  `nome`               VARCHAR(255) NOT NULL,
  `nome_fantasia`      VARCHAR(255),
  `documento`          varchar(20),
  `inscricao_estadual` varchar(20),
  `logradouro`         varchar(255) default null,
  `numero`             varchar(60)  default null,
  `complemento`        varchar(60)  default null,
  `bairro`             varchar(60)  default null,
  `cidade`             varchar(60)  default null,
  `estado`             char(2)      default null,
  `cep`                varchar(8)   default null,
  `fone1`              varchar(50)  default null,
  `fone2`              varchar(50)  default null,
  `fone3`              varchar(50)  default null,
  `fone4`              varchar(50)  default null,
  `email`              varchar(200) default null,
  `utilizado`          tinyint(1)   NOT NULL,
  `json_data`          json,

  UNIQUE KEY `UK_est_fornecedor_codigo` (`codigo`),

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
) ENGINE = InnoDB;



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

) ENGINE = InnoDB;



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
) ENGINE = InnoDB;



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

) ENGINE = InnoDB;



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
) ENGINE = InnoDB;



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

) ENGINE = InnoDB;



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
) ENGINE = InnoDB;


DROP TABLE IF EXISTS `fin_tipo_lancto`;


CREATE TABLE `fin_tipo_lancto`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

  `codigo`             int(11)      NOT NULL,
  `descricao`          varchar(200) NOT NULL,

  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_tipo_lancto_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_tipo_lancto_descricao` (`descricao`),

  KEY `K_fin_tipo_lancto_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_tipo_lancto_user_inserted` (`user_inserted_id`),
  KEY `K_fin_tipo_lancto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_tipo_lancto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_tipo_lancto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_tipo_lancto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;


DROP TABLE IF EXISTS `fin_banco`;


CREATE TABLE `fin_banco`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,

  `codigo_banco`       int(11)      NOT NULL,
  `nome`               varchar(200) NOT NULL,
  `utilizado`          tinyint(1)   NOT NULL,

  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_banco_codigo_banco` (`codigo_banco`),
  KEY `K_fin_banco_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_banco_user_inserted` (`user_inserted_id`),
  KEY `K_fin_banco_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_banco_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_banco_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_banco_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_bandeira_cartao`;


CREATE TABLE `fin_bandeira_cartao`
(
  `id`                 bigint(20)    NOT NULL AUTO_INCREMENT,

  `descricao`          varchar(40)   NOT NULL,
  `modo_id`            bigint(20)    NOT NULL,
  `labels`             varchar(2000) NOT NULL,

  `inserted`           datetime      NOT NULL,
  `updated`            datetime      NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)    NOT NULL,
  `user_inserted_id`   bigint(20)    NOT NULL,
  `user_updated_id`    bigint(20)    NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_bandeira_cartao_descricao` (`descricao`),
  KEY `K_fin_bandeira_cartao_modo` (`modo_id`),
  CONSTRAINT `FK_fin_bandeira_cartao_modo` FOREIGN KEY (`modo_id`) REFERENCES `fin_modo` (`id`),

  KEY `K_fin_bandeira_cartao_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_bandeira_cartao_user_inserted` (`user_inserted_id`),
  KEY `K_fin_bandeira_cartao_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_bandeira_cartao_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_bandeira_cartao_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_bandeira_cartao_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)


) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_cadeia`;


CREATE TABLE `fin_cadeia`
(
  `id`                 bigint(20) NOT NULL AUTO_INCREMENT,
  `fechada`            tinyint(1) NOT NULL,
  `vinculante`         tinyint(1) NOT NULL,

  `inserted`           datetime   NOT NULL,
  `updated`            datetime   NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id`   bigint(20) NOT NULL,
  `user_updated_id`    bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_cadeia_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_cadeia_user_inserted` (`user_inserted_id`),
  KEY `K_fin_cadeia_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_cadeia_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_cadeia_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_cadeia_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_carteira`;


CREATE TABLE `fin_carteira`
(
  `id`                   bigint(20)  NOT NULL AUTO_INCREMENT,
  `codigo`               int(11)     NOT NULL,
  `descricao`            varchar(40) NOT NULL,

  `banco_id`             bigint(20),
  `agencia`              varchar(30),
  `conta`                varchar(30),

  `abertas`              tinyint(1)  NOT NULL,
  `caixa`                tinyint(1)  NOT NULL,
  `cheque`               tinyint(1)  NOT NULL,
  `concreta`             tinyint(1)  NOT NULL,
  `dt_consolidado`       date        NOT NULL,
  `limite`               decimal(15, 2),
  `operadora_cartao_id`  bigint(20),

  `atual`                tinyint(1)  NOT NULL,

  `caixa_status`         enum ('','ABERTO','FECHADO'),
  `caixa_responsavel_id` bigint(20),

  `json_data`            json,


  `inserted`             datetime    NOT NULL,
  `updated`              datetime    NOT NULL,
  `version`              int(11),
  `estabelecimento_id`   bigint(20)  NOT NULL,
  `user_inserted_id`     bigint(20)  NOT NULL,
  `user_updated_id`      bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_carteira_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_carteira_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_carteira_operadora_cartao` (`operadora_cartao_id`),
  KEY `K_fin_carteira_banco` (`banco_id`),
  CONSTRAINT `FK_fin_carteira_banco` FOREIGN KEY (`banco_id`) REFERENCES `fin_banco` (`id`),
  CONSTRAINT `fk_fin_carteira_operadora_cartao` FOREIGN KEY (`operadora_cartao_id`) REFERENCES `fin_operadora_cartao` (`id`),

  KEY `K_fin_carteira_caixa_responsavel` (`caixa_responsavel_id`),
  CONSTRAINT `FK_fin_carteira_caixa_responsavel` FOREIGN KEY (`caixa_responsavel_id`) REFERENCES `sec_user` (`id`),

  KEY `K_fin_carteira_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_carteira_user_inserted` (`user_inserted_id`),
  KEY `K_fin_carteira_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_carteira_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_carteira_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_carteira_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_caixa_operacao`;
CREATE TABLE `fin_caixa_operacao`
(
  `id`                  bigint(20)                     NOT NULL AUTO_INCREMENT,
  `uuid`                char(36)                       NOT NULL,
  `carteira_id`         bigint(20)                     NOT NULL,
  `operacao`            enum ('ABERTURA','FECHAMENTO') NOT NULL,
  `obs`                 varchar(255),
  `dt_operacao`         datetime                       NOT NULL,
  `responsavel_id`      bigint(20)                     NOT NULL,
  `responsavel_dest_id` bigint(20),
  `valor`               decimal(15, 2)                 NOT NULL,

  `json_data`           json,


  `inserted`            datetime                       NOT NULL,
  `updated`             datetime                       NOT NULL,
  `version`             int(11),
  `estabelecimento_id`  bigint(20)                     NOT NULL,
  `user_inserted_id`    bigint(20)                     NOT NULL,
  `user_updated_id`     bigint(20)                     NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_caixa_operacao_uuid` (`uuid`),
  KEY `K_fin_caixa_operacao_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_caixa_operacao_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_caixa_operacao_responsavel` (`responsavel_id`),
  CONSTRAINT `FK_fin_caixa_operacao_responsavel` FOREIGN KEY (`responsavel_id`) REFERENCES `sec_user` (`id`),

  KEY `K_fin_caixa_operacao_responsavel_dest` (`responsavel_dest_id`),
  CONSTRAINT `FK_fin_caixa_operacao_responsavel_dest` FOREIGN KEY (`responsavel_dest_id`) REFERENCES `sec_user` (`id`),

  KEY `K_fin_caixa_operacao_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_caixa_operacao_user_inserted` (`user_inserted_id`),
  KEY `K_fin_caixa_operacao_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_caixa_operacao_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_caixa_operacao_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_caixa_operacao_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_categoria`;


CREATE TABLE `fin_categoria`
(
  `id`                        bigint(20)   NOT NULL AUTO_INCREMENT,

  `codigo`                    bigint(20)   NOT NULL,
  `descricao`                 varchar(200) NOT NULL,
  `pai_id`                    bigint(20),

  `centro_custo_dif`          tinyint(1)   NOT NULL,
  `codigo_super`              bigint(20)   NOT NULL,
  `descricao_padrao_moviment` varchar(200),
  `totalizavel`               tinyint(1)   NOT NULL,
  `descricao_alternativa`     varchar(200),
  `roles_acess`               varchar(2000),
  `codigo_ord`                bigint(20),

  `inserted`                  datetime     NOT NULL,
  `updated`                   datetime     NOT NULL,
  `version`                   int(11),
  `estabelecimento_id`        bigint(20)   NOT NULL,
  `user_inserted_id`          bigint(20)   NOT NULL,
  `user_updated_id`           bigint(20)   NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_categoria_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_categoria_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_categoria_codigo_ord` (`codigo_ord`),
  KEY `K_fin_categoria_pai` (`pai_id`),
  CONSTRAINT `FK_fin_categoria_pai` FOREIGN KEY (`pai_id`) REFERENCES `fin_categoria` (`id`) ON UPDATE CASCADE,

  KEY `K_fin_categoria_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_categoria_user_inserted` (`user_inserted_id`),
  KEY `K_fin_categoria_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_categoria_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_categoria_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_categoria_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_centrocusto`;


CREATE TABLE `fin_centrocusto`
(
  `id`                 bigint(20)  NOT NULL AUTO_INCREMENT,
  `codigo`             int(11)     NOT NULL,
  `descricao`          varchar(40) NOT NULL,

  `inserted`           datetime    NOT NULL,
  `updated`            datetime    NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)  NOT NULL,
  `user_inserted_id`   bigint(20)  NOT NULL,
  `user_updated_id`    bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_centrocusto_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_centrocusto_codigo` (`codigo`),

  KEY `K_fin_centrocusto_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_centrocusto_user_inserted` (`user_inserted_id`),
  KEY `K_fin_centrocusto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_centrocusto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_centrocusto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_centrocusto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_grupo`;


CREATE TABLE `fin_grupo`
(
  `id`                  bigint(20)  NOT NULL AUTO_INCREMENT,
  `descricao`           varchar(40) NOT NULL,
  `ativo`               tinyint(1)  NOT NULL,
  `dia_inicio`          int(11)     NOT NULL,
  `dia_vencto`          int(11)     NOT NULL,
  `carteira_pagante_id` bigint(20)  NOT NULL,
  `categoria_padrao_id` bigint(20),

  `inserted`            datetime    NOT NULL,
  `updated`             datetime    NOT NULL,
  `version`             int(11),
  `estabelecimento_id`  bigint(20)  NOT NULL,
  `user_inserted_id`    bigint(20)  NOT NULL,
  `user_updated_id`     bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_grupo_descricao` (`descricao`),
  KEY `K_fin_grupo_carteira_pagante` (`carteira_pagante_id`),
  KEY `K_fin_grupo_categoria_padrao` (`categoria_padrao_id`),
  CONSTRAINT `FK_fin_grupo_categoria_padrao` FOREIGN KEY (`categoria_padrao_id`) REFERENCES `fin_categoria` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_fin_grupo_carteira_pagante` FOREIGN KEY (`carteira_pagante_id`) REFERENCES `fin_carteira` (`id`) ON UPDATE CASCADE,

  KEY `K_fin_grupo_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_grupo_user_inserted` (`user_inserted_id`),
  KEY `K_fin_grupo_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_grupo_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_grupo_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_grupo_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_grupo_item`;


CREATE TABLE `fin_grupo_item`
(
  `id`                      bigint(20)  NOT NULL AUTO_INCREMENT,
  `descricao`               varchar(40) NOT NULL,
  `dt_vencto`               date        NOT NULL,
  `fechado`                 tinyint(1)  NOT NULL,
  `valor_informado`         double,
  `anterior_id`             bigint(20),
  `carteira_pagante_id`     bigint(20)  NOT NULL,
  `movimentacao_pagante_id` bigint(20),
  `grupo_pai_id`            bigint(20)  NOT NULL,
  `proximo_id`              bigint(20),

  `inserted`                datetime    NOT NULL,
  `updated`                 datetime    NOT NULL,
  `version`                 int(11),
  `estabelecimento_id`      bigint(20)  NOT NULL,
  `user_inserted_id`        bigint(20)  NOT NULL,
  `user_updated_id`         bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_grupo_item_descricao` (`descricao`),
  KEY `K_fin_grupo_item_anterior` (`anterior_id`),
  KEY `K_fin_grupo_item_carteira_pagante` (`carteira_pagante_id`),
  KEY `K_fin_grupo_item_movimentacao_pagante` (`movimentacao_pagante_id`),
  KEY `K_fin_grupo_item_grupo_pai` (`grupo_pai_id`),
  KEY `K_fin_grupo_item_proximo` (`proximo_id`),
  CONSTRAINT `FK_fin_grupo_item_proximo` FOREIGN KEY (`proximo_id`) REFERENCES `fin_grupo_item` (`id`),
  CONSTRAINT `FK_fin_grupo_item_grupo_pai` FOREIGN KEY (`grupo_pai_id`) REFERENCES `fin_grupo` (`id`),
  CONSTRAINT `FK_fin_grupo_item_carteira_pagante` FOREIGN KEY (`carteira_pagante_id`) REFERENCES `fin_carteira` (`id`),
  CONSTRAINT `FK_fin_grupo_item_movimentacao_pagante` FOREIGN KEY (`movimentacao_pagante_id`) REFERENCES `fin_movimentacao` (`id`),
  CONSTRAINT `FK_fin_grupo_item_anterior` FOREIGN KEY (`anterior_id`) REFERENCES `fin_grupo_item` (`id`),

  KEY `K_fin_grupo_item_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_grupo_item_user_inserted` (`user_inserted_id`),
  KEY `K_fin_grupo_item_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_grupo_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_grupo_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_grupo_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_import_extrato_cabec`;


CREATE TABLE `fin_import_extrato_cabec`
(
  `id`                 bigint(20)                      NOT NULL AUTO_INCREMENT,
  `tipo_extrato`       varchar(100),
  `campo_sistema`      varchar(100) CHARACTER SET utf8 NOT NULL,
  `campos_cabecalho`   varchar(200) CHARACTER SET utf8 NOT NULL,
  `formato`            varchar(100),

  `inserted`           datetime                        NOT NULL,
  `updated`            datetime                        NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)                      NOT NULL,
  `user_inserted_id`   bigint(20)                      NOT NULL,
  `user_updated_id`    bigint(20)                      NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_import_extrato_cabec_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_import_extrato_cabec_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_import_extrato_cabec_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_modo`;


CREATE TABLE `fin_modo`
(
  `id`                 bigint(20)  NOT NULL AUTO_INCREMENT,
  `codigo`             int(11)     NOT NULL,
  `descricao`          varchar(40) NOT NULL,
  `com_banco_origem`   tinyint(1)  NOT NULL,
  `moviment_agrup`     tinyint(1)  NOT NULL,
  `transf_caixa`       tinyint(1)  NOT NULL,
  `transf_propria`     tinyint(1)  NOT NULL,
  `modo_cartao`        tinyint(1)  NOT NULL,
  `modo_cheque`        tinyint(1)  NOT NULL,

  `inserted`           datetime    NOT NULL,
  `updated`            datetime    NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)  NOT NULL,
  `user_inserted_id`   bigint(20)  NOT NULL,
  `user_updated_id`    bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_modo_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_modo_descricao` (`descricao`),

  KEY `K_fin_modo_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_modo_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_modo_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_modo_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_modo_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_modo_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_fatura`;


CREATE TABLE `fin_fatura`
(
  `id`                 bigint(20) NOT NULL AUTO_INCREMENT,

  `dt_fatura`          datetime   NOT NULL,
  `fechada`            tinyint(1) NOT NULL,
  `quitada`            tinyint(1) NOT NULL,
  `transacional`       tinyint(1) NOT NULL,
  `json_data`          json,

  `inserted`           datetime   NOT NULL,
  `updated`            datetime   NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id`   bigint(20) NOT NULL,
  `user_updated_id`    bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_fatura_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_fatura_user_inserted` (`user_inserted_id`),
  KEY `K_fin_fatura_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_fatura_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_fatura_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_fatura_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_movimentacao`;


CREATE TABLE `fin_movimentacao`
(
  `id`                      bigint(20)                              NOT NULL AUTO_INCREMENT,

  `uuid`                    char(36),
  `fatura_id`               bigint(20),
  `fatura_ordem`            int(11),
  `modo_id`                 bigint(20)                              NOT NULL,
  `documento_banco_id`      bigint(20),
  `documento_num`           varchar(200),

  `sacado`                  varchar(500),
  `cedente`                 varchar(500),

  `movimentacao_pagante_id` bigint(20)                                       default null,

  `quitado`                 tinyint(1)                              NOT NULL,

  `tipo_lancto_id`          bigint(20)                              NOT NULL,
  `carteira_id`             bigint(20)                              NOT NULL,
  `carteira_destino_id`     bigint(20),
  `categoria_id`            bigint(20)                              NOT NULL,
  `centrocusto_id`          bigint(20)                              NOT NULL,
  `grupo_item_id`           bigint(20),
  `status`                  ENUM ('ABERTA','REALIZADA','ESTORNADA') NOT NULL,

  `descricao`               varchar(500)                            NOT NULL,

  `dt_moviment`             date                                    NOT NULL,
  `dt_vencto`               date                                    NOT NULL,
  `dt_vencto_efetiva`       date                                    NOT NULL,
  `dt_pagto`                date,
  `dt_util`                 date                                    NOT NULL,

  `cheque_banco_id`         bigint(20),
  `cheque_agencia`          varchar(30),
  `cheque_conta`            varchar(30),
  `cheque_num_cheque`       varchar(20),

  `parcelamento`            tinyint(1)                              NOT NULL DEFAULT FALSE,
  `qtde_parcelas`           int(11),
  `parcela_num`             int,

  `cadeia_id`               bigint(20),
  `cadeia_ordem`            int(11),
  `cadeia_qtde`             int(11),

  `operadora_cartao_id`     bigint(20),
  `bandeira_cartao_id`      bigint(20),
  `id_transacao_cartao`     varchar(255),
  `num_cartao`              varchar(50),

  `recorrente`              tinyint(1)                              NOT NULL,
  `recorr_dia`              int(11),
  `recorr_frequencia`       varchar(50),
  `recorr_tipo_repet`       varchar(50),
  `recorr_variacao`         int(11),

  `valor`                   decimal(15, 2)                          NOT NULL,
  `descontos`               decimal(15, 2),
  `acrescimos`              decimal(15, 2),
  `valor_total`             decimal(15, 2)                          NOT NULL,


  `obs`                     varchar(5000),
  `uuid_importacao`         char(36),
  `json_data`               json,

  `inserted`                datetime                                NOT NULL,
  `updated`                 datetime                                NOT NULL,
  `version`                 int(11),
  `estabelecimento_id`      bigint(20)                              NOT NULL,
  `user_inserted_id`        bigint(20)                              NOT NULL,
  `user_updated_id`         bigint(20)                              NOT NULL,

  PRIMARY KEY (`id`),

  KEY `K_fin_movimentacao_dt_vencto_efetiva` (`dt_vencto_efetiva`),
  KEY `K_fin_movimentacao_dt_pagto` (`dt_pagto`),
  KEY `K_fin_movimentacao_dt_util` (`dt_util`),
  KEY `K_fin_movimentacao_valor` (`valor`),
  KEY `K_fin_movimentacao_valor_total` (`valor_total`),
  KEY `K_fin_movimentacao_status` (`status`),

  KEY `K_fin_movimentacao_fatura` (`fatura_id`),
  CONSTRAINT `FK_fin_movimentacao_fatura` FOREIGN KEY (`fatura_id`) REFERENCES `fin_fatura` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  UNIQUE KEY UK_fin_movimentacao_uuid (uuid),

  KEY `K_fin_movimentacao_banco` (`documento_banco_id`),
  CONSTRAINT `FK_fin_movimentacao_banco` FOREIGN KEY (`documento_banco_id`) REFERENCES `fin_banco` (`id`),

  KEY `K_fin_movimentacao_tipo_lancto` (`tipo_lancto_id`),
  CONSTRAINT `FK_fin_movimentacao_tipo_lancto` FOREIGN KEY (`tipo_lancto_id`) REFERENCES `fin_tipo_lancto` (`id`),

  KEY `K_fin_movimentacao_bandeira_cartao` (`bandeira_cartao_id`),
  CONSTRAINT `FK_fin_movimentacao_bandeira_cartao` FOREIGN KEY (`bandeira_cartao_id`) REFERENCES `fin_bandeira_cartao` (`id`),

  KEY `K_fin_movimentacao_cadeia` (`cadeia_id`),
  CONSTRAINT `FK_fin_movimentacao_cadeia` FOREIGN KEY (`cadeia_id`) REFERENCES `fin_cadeia` (`id`),

  KEY `K_fin_movimentacao_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_movimentacao_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_movimentacao_carteira_destino` (`carteira_destino_id`),
  CONSTRAINT `FK_fin_movimentacao_carteira_destino` FOREIGN KEY (`carteira_destino_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_movimentacao_categoria` (`categoria_id`),
  CONSTRAINT `FK_fin_movimentacao_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categoria` (`id`) ON UPDATE CASCADE,

  KEY `K_fin_movimentacao_centrocusto` (`centrocusto_id`),
  CONSTRAINT `FK_fin_movimentacao_centrocusto` FOREIGN KEY (`centrocusto_id`) REFERENCES `fin_centrocusto` (`id`),

  KEY `K_fin_movimentacao_cheque_banco` (`cheque_banco_id`),
  CONSTRAINT `FK_fin_movimentacao_cheque_banco` FOREIGN KEY (`cheque_banco_id`) REFERENCES `fin_banco` (`id`),

  KEY `K_fin_movimentacao_grupo_item` (`grupo_item_id`),
  CONSTRAINT `FK_fin_movimentacao_grupo_item` FOREIGN KEY (`grupo_item_id`) REFERENCES `fin_grupo_item` (`id`),

  KEY `K_fin_movimentacao_modo` (`modo_id`),
  CONSTRAINT `FK_fin_movimentacao_modo` FOREIGN KEY (`modo_id`) REFERENCES `fin_modo` (`id`),

  KEY `K_fin_movimentacao_operadora_cartao` (`operadora_cartao_id`),
  CONSTRAINT `FK_fin_movimentacao_operadora_cartao` FOREIGN KEY (`operadora_cartao_id`) REFERENCES `fin_operadora_cartao` (`id`),

  KEY `K_fin_movimentacao_movimentacao_pagante` (`movimentacao_pagante_id`),
  CONSTRAINT `FK_fin_movimentacao_movimentacao_pagante` FOREIGN KEY (`movimentacao_pagante_id`) REFERENCES `fin_movimentacao` (`id`),


  KEY `K_fin_movimentacao_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_movimentacao_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_movimentacao_user_updated_id` (`user_updated_id`),
  KEY `K_fin_movimentacao_updated` (`updated`),
  CONSTRAINT `FK_fin_movimentacao_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_movimentacao_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_movimentacao_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_saldo`;


CREATE TABLE `fin_saldo`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,

  `carteira_id`        bigint(20)     NOT NULL,
  `dt_saldo`           date           NOT NULL,
  `total_realizadas`   decimal(15, 2) NOT NULL,
  `total_pendencias`   decimal(15, 2) NOT NULL,


  `inserted`           datetime       NOT NULL,
  `updated`            datetime       NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)     NOT NULL,
  `user_inserted_id`   bigint(20)     NOT NULL,
  `user_updated_id`    bigint(20)     NOT NULL,

  PRIMARY KEY (`id`),

  UNIQUE KEY `UK_fin_saldo` (`dt_saldo`, `carteira_id`),
  KEY `K_fin_saldo_dt_saldo` (`dt_saldo`),

  KEY `K_fin_saldo_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_saldo_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_saldo_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_saldo_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_saldo_user_updated_id` (`user_updated_id`),
  KEY `K_fin_saldo_updated` (`updated`),
  CONSTRAINT `FK_fin_saldo_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_saldo_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_saldo_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_operadora_cartao`;


CREATE TABLE `fin_operadora_cartao`
(
  `id`                 bigint(20)  NOT NULL AUTO_INCREMENT,
  `descricao`          varchar(40) NOT NULL,
  `carteira_id`        bigint(20)  NOT NULL,
  `ativa`              tinyint(1),

  `inserted`           datetime    NOT NULL,
  `updated`            datetime    NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)  NOT NULL,
  `user_inserted_id`   bigint(20)  NOT NULL,
  `user_updated_id`    bigint(20)  NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_operadora_cartao_descricao` (`descricao`),

  KEY `K_fin_operadora_cartao_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_operadora_cartao_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_operadora_cartao_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_operadora_cartao_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_operadora_cartao_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_operadora_cartao_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_operadora_cartao_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_operadora_cartao_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_reg_conf`;


CREATE TABLE `fin_reg_conf`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `descricao`          varchar(200) NOT NULL,
  `dt_registro`        date         NOT NULL,
  `obs`                varchar(5000),
  `valor`              decimal(19, 2),
  `carteira_id`        bigint(20),

  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_reg_conf` (`descricao`, `carteira_id`, `dt_registro`),
  KEY `K_fin_reg_conf_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_reg_conf_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_reg_conf_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_reg_conf_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_reg_conf_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_reg_conf_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_reg_conf_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_reg_conf_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fin_regra_import_linha`;


CREATE TABLE `fin_regra_import_linha`
(
  `id`                  bigint(20)   NOT NULL AUTO_INCREMENT,

  `regra_regex_java`    varchar(500),

  `padrao_descricao`    varchar(500) NOT NULL,
  `status`              varchar(50)  NOT NULL,
  `tipo_lancto_id`      bigint(20)   NOT NULL,
  `carteira_id`         bigint(20),
  `carteira_destino_id` bigint(20),
  `categoria_id`        bigint(20)   NOT NULL,
  `centrocusto_id`      bigint(20)   NOT NULL,
  `modo_id`             bigint(20)   NOT NULL,
  `sinal_valor`         int(11)      NOT NULL,

  `cheque_banco_id`     bigint(20),
  `cheque_agencia`      varchar(30),
  `cheque_conta`        varchar(30),
  `cheque_num_cheque`   varchar(20),

  `inserted`            datetime     NOT NULL,
  `updated`             datetime     NOT NULL,
  `version`             int(11),
  `estabelecimento_id`  bigint(20)   NOT NULL,
  `user_inserted_id`    bigint(20)   NOT NULL,
  `user_updated_id`     bigint(20)   NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_regra_import_linha_tipo_lancto` (`tipo_lancto_id`),
  KEY `K_fin_regra_import_linha_carteira` (`carteira_id`),
  KEY `K_fin_regra_import_linha_carteira_destino` (`carteira_destino_id`),
  KEY `K_fin_regra_import_linha_categoria` (`categoria_id`),
  KEY `K_fin_regra_import_linha_centrocusto` (`centrocusto_id`),
  KEY `K_fin_regra_import_linha_cheque_banco` (`cheque_banco_id`),
  KEY `K_fin_regra_import_linha_modo` (`modo_id`),
  CONSTRAINT `FK_fin_regra_import_linha_tipo_lancto` FOREIGN KEY (`tipo_lancto_id`) REFERENCES `fin_tipo_lancto` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_centrocusto` FOREIGN KEY (`centrocusto_id`) REFERENCES `fin_centrocusto` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categoria` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `FK_fin_regra_import_linha_modo` FOREIGN KEY (`modo_id`) REFERENCES `fin_modo` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_cheque_banco` FOREIGN KEY (`cheque_banco_id`) REFERENCES `fin_banco` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_carteira_destino` FOREIGN KEY (`carteira_destino_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_regra_import_linha_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_regra_import_linha_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_regra_import_linha_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_regra_import_linha_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fis_msg_retorno_rf`;

CREATE TABLE `fis_msg_retorno_rf`
(
  `id`                 bigint(20)    NOT NULL AUTO_INCREMENT,
  `codigo`             int(11)       NOT NULL,
  `mensagem`           varchar(2000) NOT NULL,
  `versao`             varchar(10)   NOT NULL,

  UNIQUE KEY `UK_fis_msg_retorno_rf` (`codigo`, `versao`),

  -- campo de controle
  `inserted`           datetime      NOT NULL,
  `updated`            datetime      NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)    NOT NULL,
  `user_inserted_id`   bigint(20)    NOT NULL,
  `user_updated_id`    bigint(20)    NOT NULL,
  PRIMARY KEY (`id`),
  KEY `K_fis_msg_retorno_rf_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_msg_retorno_rf_user_inserted` (`user_inserted_id`),
  KEY `K_fis_msg_retorno_rf_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fis_msg_retorno_rf_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_msg_retorno_rf_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_msg_retorno_rf_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fis_ncm`;

CREATE TABLE `fis_ncm`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `codigo`             int(11)      NOT NULL,
  `descricao`          varchar(200) NOT NULL,

  -- campo de controle
  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,
  PRIMARY KEY (`id`),
  KEY `K_fis_ncm_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_ncm_user_inserted` (`user_inserted_id`),
  KEY `K_fis_ncm_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fis_ncm_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_ncm_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_ncm_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fis_nf`;

CREATE TABLE `fis_nf`
(
  `id`                       bigint(20)     NOT NULL AUTO_INCREMENT,
  `dt_emissao`               datetime,
  `numero`                   int(11),
  `valor_total`              decimal(15, 2),
  `xml_nota`                 longtext,
  `resumo`                   tinyint(1),
  `documento_emitente`       varchar(14),
  `xnome_emitente`           varchar(255),
  `inscr_est_emitente`       varchar(30),
  `logradouro_emitente`      varchar(60),
  `numero_emitente`          varchar(60),
  `complemento_emitente`     varchar(60),
  `bairro_emitente`          varchar(60),
  `cidade_emitente`          varchar(60),
  `estado_emitente`          varchar(2),
  `cep_emitente`             varchar(9),
  `fone_emitente`            varchar(50),
  `tipo`                     varchar(30),
  `serie`                    int(11),
  `documento_destinatario`   varchar(14),
  `xnome_destinatario`       varchar(255),
  `inscr_est`                varchar(30),
  `logradouro_destinatario`  varchar(60),
  `numero_destinatario`      varchar(60),
  `complemento_destinatario` varchar(60),
  `bairro_destinatario`      varchar(60),
  `cidade_destinatario`      varchar(60),
  `estado_destinatario`      varchar(2),
  `cep_destinatario`         varchar(9),
  `email_destinatario`       varchar(200),
  `fone_destinatario`        varchar(50),
  `ambiente`                 varchar(4),
  `info_compl`               varchar(3000),
  `total_descontos`          decimal(15, 2) DEFAULT '0.00',
  `subtotal`                 decimal(15, 2),
  `transp_documento`         varchar(14),
  `transp_nome`              varchar(200),
  `transp_inscr_est`         varchar(50),
  `transp_endereco`          varchar(200),
  `transp_cidade`            varchar(120),
  `transp_estado`            varchar(2),
  `transp_especie_volumes`   varchar(200),
  `transp_marca_volumes`     varchar(200),
  `transp_modalidade_frete`  varchar(30),
  `transp_numeracao_volumes` varchar(200),
  `transp_peso_bruto`        decimal(15, 2),
  `transp_peso_liquido`      decimal(15, 2),
  `transp_qtde_volumes`      decimal(15, 2),
  `transp_fornecedor_id`     bigint(20),
  `indicador_forma_pagto`    varchar(30),
  `natureza_operacao`        varchar(60),
  `a03id_nf_referenciada`    varchar(100),
  `finalidade_nf`            varchar(30),
  `transp_valor_total_frete` decimal(15, 2),
  `dt_saient`                datetime,
  `uuid`                     varchar(32),
  `cnf`                      char(8),
  `chave_acesso`             char(44),
  `protocolo_autoriz`        varchar(255),
  `dt_protocolo_autoriz`     datetime,
  `motivo_cancelamento`      varchar(255),
  `carta_correcao`           varchar(1000),
  `carta_correcao_seq`       int(11),
  `rand_faturam`             varchar(200),
  `inserted`                 datetime       NOT NULL,
  `updated`                  datetime       NOT NULL,
  `version`                  int(11),
  `estabelecimento_id`       bigint(20)     NOT NULL,
  `user_inserted_id`         bigint(20)     NOT NULL,
  `user_updated_id`          bigint(20)     NOT NULL,
  `nsu`                      int(11),
  `nrec`                     varchar(30),
  `cstat_lote`               int(11),
  `xmotivo_lote`             varchar(255),
  `cstat`                    int(11),
  `xmotivo`                  varchar(255),
  `manifest_dest`            varchar(255),
  `dt_manifest_dest`         datetime,
  `entrada_saida`            enum ('E','S') NOT NULL,
  `json_data`                json,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UF_fis_nf_chave_acesso` (`chave_acesso`),
  KEY `K_fis_nf_documento_emitente` (`documento_emitente`),
  KEY `K_fis_nf_numero` (`numero`),

  KEY `K_fis_nf_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_user_updated` (`user_updated_id`),

  CONSTRAINT `FK_fis_nf_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fis_nf_item`;

CREATE TABLE `fis_nf_item`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `nota_fiscal_id`     bigint(20)     NOT NULL,

  `csosn`              int(11),
  `cfop`               varchar(20)    NOT NULL,
  `cst`                varchar(10),
  `cest`               varchar(20),
  `ean`                varchar(50),
  `codigo`             varchar(50)    NOT NULL,
  `descricao`          varchar(2000)  NOT NULL,
  `ncm`                varchar(20),
  `ordem`              int(11)        NOT NULL,
  `unidade`            varchar(50)    NOT NULL,
  `qtde`               decimal(15, 2) NOT NULL,
  `valor_unit`         decimal(15, 2) NOT NULL,
  `sub_total`          decimal(15, 2) NOT NULL,
  `valor_desconto`     decimal(15, 2),
  `valor_total`        decimal(15, 2) NOT NULL,

  `icms`               decimal(15, 2),
  `icms_valor`         decimal(15, 2),
  `icms_valor_bc`      decimal(15, 2),
  `icms_mod_bc`        varchar(10),

  `pis`                decimal(15, 2),
  `pis_valor`          decimal(15, 2),
  `pis_valor_bc`       decimal(15, 2),

  `cofins`             decimal(15, 2),
  `cofins_valor`       decimal(15, 2),
  `cofins_valor_bc`    decimal(15, 2),

  `ncm_existente`      tinyint(1),

  `json_data`          json,

  `inserted`           datetime       NOT NULL,
  `updated`            datetime       NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)     NOT NULL,
  `user_inserted_id`   bigint(20)     NOT NULL,
  `user_updated_id`    bigint(20)     NOT NULL,

  PRIMARY KEY (`id`),

  UNIQUE KEY `UK_fis_nf_item` (`nota_fiscal_id`, `ordem`),
  KEY `K_fis_nf_item_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_item_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_item_user_updated` (`user_updated_id`),
  KEY `K_fis_nf_item_nota_fiscal` (`nota_fiscal_id`),

  CONSTRAINT `FK_fis_nf_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_item_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_fis_nf_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;


DROP TABLE IF EXISTS `fis_nf_cartacorrecao`;

CREATE TABLE `fis_nf_cartacorrecao`
(
  `id`                 bigint(20) NOT NULL AUTO_INCREMENT,
  `nota_fiscal_id`     bigint(20) NOT NULL,
  `carta_correcao`     varchar(1000),
  `seq`                int(11)    NOT NULL,
  `dt_carta_correcao`  datetime   NOT NULL,
  `inserted`           datetime   NOT NULL,
  `updated`            datetime   NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id`   bigint(20) NOT NULL,
  `user_updated_id`    bigint(20) NOT NULL,
  `msg_retorno`        varchar(20000),
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fis_nf_cartacorrecao` (`nota_fiscal_id`, `seq`),
  KEY `K_fis_nf_cartacorrecao_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_cartacorrecao_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_cartacorrecao_user_updated` (`user_updated_id`),
  KEY `K_fis_nf_cartacorrecao_nota_fiscal` (`nota_fiscal_id`),
  CONSTRAINT `FK_fis_nf_cartacorrecao_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_cartacorrecao_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_fis_nf_cartacorrecao_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_cartacorrecao_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;


DROP TABLE IF EXISTS `fis_nf_venda`;

CREATE TABLE `fis_nf_venda`
(
  `id`                 bigint(20) NOT NULL AUTO_INCREMENT,
  `venda_id`           bigint(20) NOT NULL,
  `nota_fiscal_id`     bigint(20) NOT NULL,

  -- campo de controle
  `inserted`           datetime   NOT NULL,
  `updated`            datetime   NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id`   bigint(20) NOT NULL,
  `user_updated_id`    bigint(20) NOT NULL,


  KEY `K_fis_nf_venda_nota_fiscal` (`nota_fiscal_id`),
  KEY `K_fis_nf_venda_venda` (`venda_id`) USING BTREE,
  CONSTRAINT `FK_fis_nf_venda_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_fis_nf_venda_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  PRIMARY KEY `id` (`id`),
  KEY `K_fis_nf_venda_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_venda_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_venda_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fis_nf_venda_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_venda_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_venda_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  PACK_KEYS = 0;



DROP TABLE IF EXISTS `fis_nf_historico`;

CREATE TABLE `fis_nf_historico`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `fis_nf_id`          bigint(20)     NOT NULL,
  `codigo_status`      int(11)        NOT NULL,
  `descricao`          varchar(15000) NOT NULL,
  `obs`                varchar(255),
  `dt_historico`       datetime       NOT NULL,
  `inserted`           datetime       NOT NULL,
  `updated`            datetime       NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)     NOT NULL,
  `user_inserted_id`   bigint(20)     NOT NULL,
  `user_updated_id`    bigint(20)     NOT NULL,

  KEY `fis_nf_id` (`fis_nf_id`),
  CONSTRAINT `FK_fis_nf_historico_nota_fiscal` FOREIGN KEY (`fis_nf_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  PRIMARY KEY (`id`),

  KEY `K_fis_nf_historico_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_historico_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_historico_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fis_nf_historico_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_historico_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_historico_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  PACK_KEYS = 0;



DROP TABLE IF EXISTS `fis_distdfe`;

CREATE TABLE `fis_distdfe`
(
  `id`                    bigint(20) NOT NULL AUTO_INCREMENT,
  `documento`             varchar(14),
  `chnfe`                 char(44),
  `tp_evento`             int(11),
  `nseq_evento`           int(11),
  `tipo_distdfe`          varchar(50),
  `nsu`                   bigint(20) NOT NULL,
  `xml`                   longtext   NOT NULL,
  `inserted`              datetime   NOT NULL,
  `updated`               datetime   NOT NULL,
  `version`               int(11),
  `estabelecimento_id`    bigint(20) NOT NULL,
  `user_inserted_id`      bigint(20) NOT NULL,
  `user_updated_id`       bigint(20) NOT NULL,
  `status`                varchar(255),
  `nota_fiscal_id`        bigint(20),
  `proprio`               tinyint(1),
  `nota_fiscal_evento_id` bigint(20),
  PRIMARY KEY (`id`),
  UNIQUE KEY `fis_distdfe_nsu` (`nsu`, `documento`),
  KEY `K_fis_distdfe_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_distdfe_user_inserted` (`user_inserted_id`),
  KEY `K_fis_distdfe_user_updated` (`user_updated_id`),
  KEY `FK_fis_distdfe_nf` (`nota_fiscal_id`),
  CONSTRAINT `FK_fis_distdfe_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_distdfe_nf` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`),
  CONSTRAINT `FK_fis_distdfe_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_distdfe_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `fis_nf_evento`;

CREATE TABLE `fis_nf_evento`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `nota_fiscal_id`     bigint(20)   NOT NULL,
  `tp_evento`          int(11)      NOT NULL,
  `nseq_evento`        int(11)      NOT NULL,
  `desc_evento`        varchar(200) NOT NULL,
  `xml`                longtext     NOT NULL,
  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,
  PRIMARY KEY (`id`),
  KEY `K_fis_nf_evento_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_nf_evento_user_inserted` (`user_inserted_id`),
  KEY `K_fis_nf_evento_user_updated` (`user_updated_id`),
  KEY `K_fis_nf_evento_nota_fiscal` (`nota_fiscal_id`),
  CONSTRAINT `FK_fis_nf_evento_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_nf_evento_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_fis_nf_evento_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_nf_evento_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;


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
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ven_plano_pagto`;

CREATE TABLE `ven_plano_pagto`
(
  `id`                 bigint(20)   NOT NULL AUTO_INCREMENT,
  `codigo`             varchar(20)  NOT NULL,
  `descricao`          varchar(200) NOT NULL,
  `ativo`              tinyint(1)   NOT NULL,
  `json_data`          json,

  UNIQUE KEY `UK_ven_plano_pagto` (`codigo`),

  -- campos de controle
  PRIMARY KEY (`id`),
  `inserted`           datetime     NOT NULL,
  `updated`            datetime     NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)   NOT NULL,
  `user_inserted_id`   bigint(20)   NOT NULL,
  `user_updated_id`    bigint(20)   NOT NULL,
  KEY `K_ven_plano_pagto_estabelecimento` (`estabelecimento_id`),
  KEY `K_ven_plano_pagto_user_inserted` (`user_inserted_id`),
  KEY `K_ven_plano_pagto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ven_plano_pagto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ven_plano_pagto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ven_plano_pagto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB;



INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '001', 'A VISTA (ESPÉCIE)', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '002', 'A VISTA (CHEQUE)', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '003', 'A VISTA (CARTÃO DÉBITO)', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '011', 'CARTÃO DE CRÉDITO (30DD/VENCTO)', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '012', 'CARTÃO DE CRÉDITO (2X)', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '099', 'MÚLTIPLAS FORMAS', true);

INSERT INTO ven_plano_pagto(id, inserted, updated, version, estabelecimento_id, user_inserted_id,
                            user_updated_id,
                            codigo, descricao, ativo)
VALUES (null, now(), now(), 0, 1, 1, 1, '013', 'FATURADO', true);


DROP TABLE IF EXISTS `ven_venda`;

CREATE TABLE `ven_venda`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `dt_venda`           datetime       NOT NULL,
  `plano_pagto_id`     bigint(20),
  `vendedor_id`        bigint(20),
  `cliente_id`         bigint(20),
  `subtotal`           decimal(15, 2) NOT NULL,
  `desconto`           decimal(15, 2) NOT NULL,
  `valor_total`        decimal(15, 2) NOT NULL,
  `status`             varchar(50)    NOT NULL,
  `json_data`          json,

  KEY `K_ven_venda_plano_pagto` (`plano_pagto_id`),
  CONSTRAINT `FK_ven_venda_plano_pagto` FOREIGN KEY (`plano_pagto_id`) REFERENCES `ven_plano_pagto` (`id`),

  KEY `K_ven_venda_vendedor` (`plano_pagto_id`),
  CONSTRAINT `FK_ven_venda_vendedor` FOREIGN KEY (`vendedor_id`) REFERENCES `rh_colaborador` (`id`),

  KEY `K_ven_venda_cliente` (`cliente_id`),
  CONSTRAINT `FK_ven_venda_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `crm_cliente` (`id`),

  -- campos de controle
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

) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ven_venda_item`;

CREATE TABLE `ven_venda_item`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `venda_id`           bigint(20)     NOT NULL,
  `ordem`              int(11),
  `qtde`               decimal(15, 3) NOT NULL,
  `unidade_id`         bigint(20)     NOT NULL,
  `produto_id`         bigint(20),
  `descricao`          varchar(255)   NOT NULL,
  `preco_venda`        decimal(15, 2) NOT NULL,
  `subtotal`           decimal(15, 2) NOT NULL,
  `desconto`           decimal(15, 2) NOT NULL,
  `total`              decimal(15, 2) NOT NULL,
  `devolucao`          tinyint(1)     NOT NULL,
  `json_data`          json,

  KEY `K_ven_venda_item_produto` (`produto_id`),
  CONSTRAINT `FK_ven_venda_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `est_produto` (`id`),

  KEY `K_ven_venda_item_unidade` (`unidade_id`),
  CONSTRAINT `FK_ven_venda_item_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `est_unidade` (`id`),

  KEY `K_ven_venda_item_venda` (`venda_id`),
  CONSTRAINT `FK_ven_venda_item_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  -- campos de controle
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
) ENGINE = InnoDB;


DROP TABLE IF EXISTS `ven_venda_pagto`;

CREATE TABLE `ven_venda_pagto`
(
  `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
  `venda_id`           bigint(20)     NOT NULL,
  `plano_pagto_id`     bigint(20)     NOT NULL,
  `valor_pagto`        decimal(15, 2) NOT NULL,
  `json_data`          json,

  KEY `K_ven_venda_pagto_venda` (`venda_id`),
  CONSTRAINT `FK_ven_venda_pagto_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,

  KEY `K_ven_venda_pagto_plano_pagto` (`plano_pagto_id`),
  CONSTRAINT `FK_ven_venda_pagto_plano_pagto` FOREIGN KEY (`plano_pagto_id`) REFERENCES `ven_plano_pagto` (`id`),

  -- campos de controle
  PRIMARY KEY (`id`),
  `inserted`           datetime       NOT NULL,
  `updated`            datetime       NOT NULL,
  `version`            int(11),
  `estabelecimento_id` bigint(20)     NOT NULL,
  `user_inserted_id`   bigint(20)     NOT NULL,
  `user_updated_id`    bigint(20)     NOT NULL,
  KEY `K_ven_venda_pagto_estabelecimento` (`estabelecimento_id`),
  KEY `K_ven_venda_pagto_user_inserted` (`user_inserted_id`),
  KEY `K_ven_venda_pagto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ven_venda_pagto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ven_venda_pagto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ven_venda_pagto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ecomm_cliente_config`;

CREATE TABLE `ecomm_cliente_config`
(
  `id`                       bigint   NOT NULL AUTO_INCREMENT,
  `uuid`                     char(36) NOT NULL,
  `cliente_id`               bigint   NOT NULL,
  `ativo`                    tinyint  NOT NULL,
  `json_data`                json,
  `tray_dt_exp_access_token` datetime,

  UNIQUE KEY `UK_ecomm_cliente_config_uuid` (`uuid`),

  UNIQUE KEY `UK_ecomm_cliente_config_cliente` (`cliente_id`),
  CONSTRAINT `FK_ecomm_cliente_config_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `crm_cliente` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,

  -- campo de controle
  PRIMARY KEY (`id`),
  `inserted`                 datetime NOT NULL,
  `updated`                  datetime NOT NULL,
  `version`                  int,
  `estabelecimento_id`       bigint   NOT NULL,
  `user_inserted_id`         bigint   NOT NULL,
  `user_updated_id`          bigint   NOT NULL,
  KEY `K_ecomm_cliente_config_estabelecimento` (`estabelecimento_id`),
  KEY `K_ecomm_cliente_config_user_inserted` (`user_inserted_id`),
  KEY `K_ecomm_cliente_config_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ecomm_cliente_config_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ecomm_cliente_config_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ecomm_cliente_config_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ecomm_tray_venda`;

CREATE TABLE `ecomm_tray_venda`
(
  `id`                 bigint      NOT NULL AUTO_INCREMENT,
  `uuid`               char(36)    NOT NULL,
  `cliente_config_id`  bigint      NOT NULL,
  `id_tray`            bigint      NOT NULL,
  `dt_venda`           datetime    NOT NULL,
  `status`             varchar(50) NOT NULL,
  `point_sale`         varchar(50) NOT NULL,
  `valor_total`        decimal(15, 2),
  `cliente_id`         varchar(50),
  `cliente_nome`       varchar(255),
  `json_data`          json,

  UNIQUE KEY `UK_ecomm_tray_venda_uuid` (`uuid`),
  UNIQUE KEY `UK_ecomm_tray_venda` (`cliente_config_id`, `id_tray`),

  KEY `UK_ecomm_tray_venda_cliente_config` (`cliente_config_id`),
  CONSTRAINT `FK_ecomm_tray_venda_cliente_config` FOREIGN KEY (`cliente_config_id`) REFERENCES `ecomm_cliente_config` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,

  -- campo de controle
  PRIMARY KEY (`id`),
  `inserted`           datetime    NOT NULL,
  `updated`            datetime    NOT NULL,
  `version`            int,
  `estabelecimento_id` bigint      NOT NULL,
  `user_inserted_id`   bigint      NOT NULL,
  `user_updated_id`    bigint      NOT NULL,
  KEY `K_ecomm_tray_venda_estabelecimento` (`estabelecimento_id`),
  KEY `K_ecomm_tray_venda_user_inserted` (`user_inserted_id`),
  KEY `K_ecomm_tray_venda_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ecomm_tray_venda_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ecomm_tray_venda_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ecomm_tray_venda_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ecomm_ml_item`;

CREATE TABLE `ecomm_ml_item`
(
  `id`                   bigint       NOT NULL AUTO_INCREMENT,
  `uuid`                 char(36)     NOT NULL,
  `cliente_config_id`    bigint       NOT NULL,
  `mercadolivre_user_id` varchar(50)  NOT NULL, -- id do user no ml (para achar dentro do clienteConfig.jsonData['mercadolivre'][$i]['me']['id']
  `mercadolivre_id`      varchar(50)  NOT NULL, -- id do item no ml
  `descricao`            varchar(255) NOT NULL,
  `preco_venda`          decimal(15, 2),
  `json_data`            json,

  UNIQUE KEY `UK_ecomm_ml_item_uuid` (`uuid`),
  UNIQUE KEY `UK_ecomm_ml_item_mercadolivre_id` (`mercadolivre_id`),

  KEY `UK_ecomm_ml_item_cliente_config` (`cliente_config_id`),
  CONSTRAINT `FK_ecomm_ml_item_cliente_config` FOREIGN KEY (`cliente_config_id`) REFERENCES `ecomm_cliente_config` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,

  -- campo de controle
  PRIMARY KEY (`id`),
  `inserted`             datetime     NOT NULL,
  `updated`              datetime     NOT NULL,
  `version`              int,
  `estabelecimento_id`   bigint       NOT NULL,
  `user_inserted_id`     bigint       NOT NULL,
  `user_updated_id`      bigint       NOT NULL,
  KEY `K_ecomm_ml_item_estabelecimento` (`estabelecimento_id`),
  KEY `K_ecomm_ml_item_user_inserted` (`user_inserted_id`),
  KEY `K_ecomm_ml_item_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ecomm_ml_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ecomm_ml_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ecomm_ml_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



DROP TABLE IF EXISTS `ecomm_ml_pergunta`;

CREATE TABLE `ecomm_ml_pergunta`
(
  `id`                 bigint      NOT NULL AUTO_INCREMENT,
  `uuid`               char(36)    NOT NULL,
  `mercadolivre_id`    bigint      NOT NULL, -- id da pergunta no ml
  `ml_item_id`         bigint      NOT NULL,
  `dt_pergunta`        datetime    NOT NULL,
  `status`             varchar(50) NOT NULL,
  `json_data`          json,

  UNIQUE KEY `UK_ecomm_ml_pergunta_uuid` (`uuid`),
  UNIQUE KEY `UK_ecomm_ml_pergunta_mercadolivre_id` (`mercadolivre_id`),

  KEY `UK_ecomm_ml_pergunta_item` (`ml_item_id`),
  CONSTRAINT `FK_ecomm_ml_pergunta_item` FOREIGN KEY (`ml_item_id`) REFERENCES `ecomm_ml_item` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT,

  -- campo de controle
  PRIMARY KEY (`id`),
  `inserted`           datetime    NOT NULL,
  `updated`            datetime    NOT NULL,
  `version`            int,
  `estabelecimento_id` bigint      NOT NULL,
  `user_inserted_id`   bigint      NOT NULL,
  `user_updated_id`    bigint      NOT NULL,
  KEY `K_ecomm_ml_pergunta_estabelecimento` (`estabelecimento_id`),
  KEY `K_ecomm_ml_pergunta_user_inserted` (`user_inserted_id`),
  KEY `K_ecomm_ml_pergunta_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_ecomm_ml_pergunta_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_ecomm_ml_pergunta_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_ecomm_ml_pergunta_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;










