SET FOREIGN_KEY_CHECKS = 0;


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
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



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

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



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
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



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

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


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
    `msg_retorno`        varchar(2000),
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
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;


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
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci
  PACK_KEYS = 0;



DROP TABLE IF EXISTS `fis_nf_historico`;

CREATE TABLE `fis_nf_historico`
(
    `id`                 bigint(20)     NOT NULL AUTO_INCREMENT,
    `fis_nf_id`          bigint(20)     NOT NULL,
    `codigo_status`      int(11)        NOT NULL,
    `descricao`          varchar(20000) NOT NULL,
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
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci
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
    UNIQUE KEY `fis_distdfe_nsu` (`nsu`),
    KEY `K_fis_distdfe_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_distdfe_user_inserted` (`user_inserted_id`),
    KEY `K_fis_distdfe_user_updated` (`user_updated_id`),
    KEY `FK_fis_distdfe_nf` (`nota_fiscal_id`),
    CONSTRAINT `FK_fis_distdfe_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_distdfe_nf` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`),
    CONSTRAINT `FK_fis_distdfe_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_distdfe_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



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
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;
