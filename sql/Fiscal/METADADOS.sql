SET FOREIGN_KEY_CHECKS = 0;


DROP TABLE IF EXISTS `fis_msg_retorno_rf`;

CREATE TABLE `fis_msg_retorno_rf`
(
    `id`                 bigint(20)                            NOT NULL AUTO_INCREMENT,
    `codigo`             int(11)                               NOT NULL,
    `mensagem`           varchar(2000) COLLATE utf8_swedish_ci NOT NULL,
    `versao`             varchar(10) COLLATE utf8_swedish_ci   NOT NULL,

    UNIQUE KEY `UK_fis_msg_retorno_rf` (`codigo`, `versao`),

    -- campo de controle
    `inserted`           datetime                              NOT NULL,
    `updated`            datetime                              NOT NULL,
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20)                            NOT NULL,
    `user_inserted_id`   bigint(20)                            NOT NULL,
    `user_updated_id`    bigint(20)                            NOT NULL,
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
    `id`                 bigint(20)                           NOT NULL AUTO_INCREMENT,
    `codigo`             int(11)                              NOT NULL,
    `descricao`          varchar(200) COLLATE utf8_swedish_ci NOT NULL,

    -- campo de controle
    `inserted`           datetime                             NOT NULL,
    `updated`            datetime                             NOT NULL,
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20)                           NOT NULL,
    `user_inserted_id`   bigint(20)                           NOT NULL,
    `user_updated_id`    bigint(20)                           NOT NULL,
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
    `id`                       bigint(20)                          NOT NULL AUTO_INCREMENT,
    `dt_emissao`               datetime                                     DEFAULT NULL,
    `numero`                   int(11)                                      DEFAULT NULL,
    `valor_total`              decimal(19, 2)                               DEFAULT NULL,
    `xml_nota`                 longtext COLLATE utf8_swedish_ci,
    `pessoa_emitente_id`       bigint(20)                          NOT NULL,
    `tipo`                     varchar(30) COLLATE utf8_swedish_ci          DEFAULT NULL,
    `entrada_saida`            bit(1)                              NOT NULL COMMENT 'false para SAIDA', -- 0=Entrada; 1=Saída
    `serie`                    int(11)                             NOT NULL,
    `pessoa_destinatario_id`   bigint(20)                                   DEFAULT NULL,

    `documento`                varchar(14)                                  DEFAULT NULL,
    `xnome`                    varchar(255)                                 DEFAULT NULL,
    `inscr_est`                varchar(30)                                  DEFAULT NULL,

    `ambiente`                 varchar(4) COLLATE utf8_swedish_ci           DEFAULT NULL,
    `info_compl`               varchar(3000) COLLATE utf8_swedish_ci        DEFAULT NULL,
    `pessoa_cadastro`          varchar(30) COLLATE utf8_swedish_ci          DEFAULT NULL,
    `total_descontos`          decimal(19, 2)                               DEFAULT '0.00',
    `subtotal`                 decimal(19, 2)                               DEFAULT NULL,
    `transp_especie_volumes`   varchar(200) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `transp_marca_volumes`     varchar(200) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `transp_modalidade_frete`  varchar(30) COLLATE utf8_swedish_ci NOT NULL,
    `transp_numeracao_volumes` varchar(200) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `transp_peso_bruto`        decimal(19, 2)                               DEFAULT NULL,
    `transp_peso_liquido`      decimal(19, 2)                               DEFAULT NULL,
    `transp_qtde_volumes`      decimal(19, 2)                               DEFAULT NULL,
    `transp_fornecedor_id`     bigint(20)                                   DEFAULT NULL,
    `indicador_forma_pagto`    varchar(30) COLLATE utf8_swedish_ci NOT NULL,
    `natureza_operacao`        varchar(60) COLLATE utf8_swedish_ci NOT NULL DEFAULT 'VENDA',
    `a03id_nf_referenciada`    varchar(100) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `finalidade_nf`            varchar(30) COLLATE utf8_swedish_ci NOT NULL,
    `transp_valor_total_frete` decimal(19, 2)                               DEFAULT NULL,
    `dt_saient`                datetime                                     DEFAULT NULL,
    `uuid`                     varchar(32) COLLATE utf8_swedish_ci          DEFAULT NULL,
    `cnf`                      char(8) COLLATE utf8_swedish_ci              DEFAULT NULL,
    `chave_acesso`             char(44) COLLATE utf8_swedish_ci             DEFAULT NULL,
    `protocolo_autoriz`        varchar(255) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `motivo_cancelamento`      varchar(255) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `carta_correcao`           varchar(1000) COLLATE utf8_swedish_ci        DEFAULT NULL,
    `carta_correcao_seq`       int(11)                                      DEFAULT NULL,
    `rand_faturam`             varchar(200) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `nrec`                     varchar(30) COLLATE utf8_swedish_ci          DEFAULT NULL,
    `cstat_lote`               int(11)                                      DEFAULT NULL,
    `xmotivo_lote`             varchar(255) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `cstat`                    int(11)                                      DEFAULT NULL,
    `xmotivo`                  varchar(255) COLLATE utf8_swedish_ci         DEFAULT NULL,
    `manifest_dest`            varchar(255) COLLATE utf8_swedish_ci         DEFAULT NULL,
    -- Confirmação da Operação, Desconhecimento da Operação, Operação Não Realizada, Ciência da Operação

    -- campo de controle
    `inserted`                 datetime                            NOT NULL,
    `updated`                  datetime                            NOT NULL,
    `user_inserted_id`         bigint(20)                          NOT NULL,
    `user_updated_id`          bigint(20)                          NOT NULL,
    `estabelecimento_id`       bigint(20)                          NOT NULL,
    `version`                  int(11)                                      DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `K_fis_nf_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),

    UNIQUE KEY `UF_fis_nf_chave_acesso` (`chave_acesso`),
    UNIQUE KEY `UF_fis_nf` (`numero`, `serie`, `tipo`, `pessoa_emitente_id`, `ambiente`),
    KEY `K_fis_nf_pessoa_emitente` (`pessoa_emitente_id`),
    KEY `K_fis_nf_pessoa_destinatario` (`pessoa_destinatario_id`),
    CONSTRAINT `FK_fis_nf_pessoa_emitente` FOREIGN KEY (`pessoa_emitente_id`) REFERENCES `bse_pessoa` (`id`),
    CONSTRAINT `FK_fis_nf_pessoa_destinatario` FOREIGN KEY (`pessoa_destinatario_id`) REFERENCES `bse_pessoa` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;

# ALTER TABLE fis_nf MODIFY pessoa_emitente_id bigint(20) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY entrada_saida bit(1) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY serie int(11) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY transp_modalidade_frete varchar(30) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY indicador_forma_pagto varchar(30) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY natureza_operacao varchar(60) DEFAULT NULL;
# ALTER TABLE fis_nf MODIFY finalidade_nf varchar(30) DEFAULT NULL;
#
#
#
# ALTER TABLE fis_nf ADD nrec varchar(30) DEFAULT NULL;
# ALTER TABLE fis_nf ADD cstat_lote int(11) DEFAULT NULL;
# ALTER TABLE fis_nf ADD xmotivo_lote varchar(255) DEFAULT NULL;
# ALTER TABLE fis_nf ADD cstat int(11) DEFAULT NULL;
# ALTER TABLE fis_nf ADD xmotivo varchar(255) DEFAULT NULL;


DROP TABLE IF EXISTS `fis_nf_item`;

CREATE TABLE `fis_nf_item`
(
    `id`                 bigint(20)                            NOT NULL AUTO_INCREMENT,
    `cfop`               varchar(20) COLLATE utf8_swedish_ci   NOT NULL,
    `codigo`             varchar(50) COLLATE utf8_swedish_ci   NOT NULL,
    `descricao`          varchar(2000) COLLATE utf8_swedish_ci NOT NULL,
    `icms`               decimal(19, 2)                        NOT NULL,
    `ncm`                varchar(20) COLLATE utf8_swedish_ci   NOT NULL,
    `ordem`              int(11)                               NOT NULL,
    `qtde`               decimal(19, 2)                        NOT NULL,
    `unidade`            varchar(50) COLLATE utf8_swedish_ci   NOT NULL,
    `valor_total`        decimal(19, 2)                        NOT NULL,
    `valor_unit`         decimal(19, 2)                        NOT NULL,
    `nota_fiscal_id`     bigint(20)                            NOT NULL,
    `valor_desconto`     decimal(19, 2)                      DEFAULT NULL,
    `sub_total`          decimal(19, 2)                        NOT NULL,
    `icms_valor`         decimal(19, 2)                      DEFAULT NULL,
    `icms_valor_bc`      decimal(19, 2)                      DEFAULT NULL,
    `ncm_existente`      bit(1)                              DEFAULT NULL,
    `fis_nf_itemcol`     varchar(45) COLLATE utf8_swedish_ci DEFAULT NULL,
    `csosn`              int(11)                             DEFAULT NULL,


    -- campo de controle
    `inserted`           datetime                              NOT NULL,
    `updated`            datetime                              NOT NULL,
    `version`            int(11)                             DEFAULT NULL,
    `estabelecimento_id` bigint(20)                            NOT NULL,
    `user_inserted_id`   bigint(20)                            NOT NULL,
    `user_updated_id`    bigint(20)                            NOT NULL,
    PRIMARY KEY (`id`),
    KEY `K_fis_nf_item_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_item_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_item_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_item_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_item_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_item_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),


    UNIQUE KEY `UK_fis_nf_item` (`nota_fiscal_id`, `ordem`),
    KEY `K_fis_nf_item_nota_fiscal` (`nota_fiscal_id`),
    CONSTRAINT `FK_fis_nf_item_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



DROP TABLE IF EXISTS `fis_nf_cartacorrecao`;

CREATE TABLE `fis_nf_cartacorrecao`
(
    `id`                 bigint(20) NOT NULL AUTO_INCREMENT,
    `nota_fiscal_id`     bigint(20) NOT NULL,
    `carta_correcao`     varchar(1000) COLLATE utf8_swedish_ci DEFAULT NULL,
    `seq`                int(11)    NOT NULL,
    `dt_carta_correcao`  datetime   NOT NULL,
    `msg_retorno`        varchar(1000) COLLATE utf8_swedish_ci DEFAULT NULL,

    -- campo de controle
    `inserted`           datetime   NOT NULL,
    `updated`            datetime   NOT NULL,
    `version`            int(11)                               DEFAULT NULL,
    `estabelecimento_id` bigint(20) NOT NULL,
    `user_inserted_id`   bigint(20) NOT NULL,
    `user_updated_id`    bigint(20) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `K_fis_nf_cartacorrecao_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_cartacorrecao_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_cartacorrecao_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_cartacorrecao_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_cartacorrecao_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_cartacorrecao_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),

    UNIQUE KEY `UK_fis_nf_cartacorrecao` (`nota_fiscal_id`, `seq`),
    KEY `K_fis_nf_cartacorrecao_nota_fiscal` (`nota_fiscal_id`),
    CONSTRAINT `FK_fis_nf_cartacorrecao_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20) NOT NULL,
    `user_inserted_id`   bigint(20) NOT NULL,
    `user_updated_id`    bigint(20) NOT NULL,
    KEY `K_fis_nf_venda_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_venda_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_venda_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_venda_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_venda_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_venda_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),

    UNIQUE KEY `id` (`id`),
    KEY `K_fis_nf_venda_nota_fiscal` (`nota_fiscal_id`),
    KEY `K_fis_nf_venda_venda` (`venda_id`) USING BTREE,
    CONSTRAINT `FK_fis_nf_venda_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`),
    CONSTRAINT `FK_fis_nf_venda_venda` FOREIGN KEY (`venda_id`) REFERENCES `ven_venda` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci
  PACK_KEYS = 0;



DROP TABLE IF EXISTS `fis_nf_historico`;

CREATE TABLE `fis_nf_historico`
(
    `id`                 bigint(20)                            NOT NULL AUTO_INCREMENT,
    `fis_nf_id`          bigint(20)                            NOT NULL,
    `codigo_status`      int(11)                               NOT NULL,
    `descricao`          varchar(2000) COLLATE utf8_swedish_ci NOT NULL,
    `obs`                varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
    `dt_historico`       datetime                              NOT NULL,

    PRIMARY KEY (`id`),
    KEY `fis_nf_id` (`fis_nf_id`),
    CONSTRAINT `fis_nf_historico_fk1` FOREIGN KEY (`fis_nf_id`) REFERENCES `fis_nf` (`id`),


    -- campo de controle
    `inserted`           datetime                              NOT NULL,
    `updated`            datetime                              NOT NULL,
    `version`            int(11)                              DEFAULT NULL,
    `estabelecimento_id` bigint(20)                            NOT NULL,
    `user_inserted_id`   bigint(20)                            NOT NULL,
    `user_updated_id`    bigint(20)                            NOT NULL,
    KEY `K_fis_nf_historico_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_historico_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_historico_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_historico_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_historico_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_historico_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci
  PACK_KEYS = 0;



DROP TABLE IF EXISTS `fis_distdfe`;

CREATE TABLE `fis_distdfe`
(
    `id`                 bigint(20)                       NOT NULL AUTO_INCREMENT,
    `chnfe`              char(44) COLLATE utf8_swedish_ci     DEFAULT NULL,
    `tp_evento`          int(11)                              DEFAULT NULL,
    `nseq_evento`        int(11)                              DEFAULT NULL,
    `tipo_distdfe`       varchar(50) COLLATE utf8_swedish_ci  DEFAULT NULL,
    `nsu`                bigint(20)                       NOT NULL,
    `xml`                longtext COLLATE utf8_swedish_ci NOT NULL,
    `status`             varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
    `nota_fiscal_id`     bigint(20)                           DEFAULT NULL,

    `inserted`           datetime                         NOT NULL,
    `updated`            datetime                         NOT NULL,
    `version`            int(11)                              DEFAULT NULL,
    `estabelecimento_id` bigint(20)                       NOT NULL,
    `user_inserted_id`   bigint(20)                       NOT NULL,
    `user_updated_id`    bigint(20)                       NOT NULL,

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
    `id`                 bigint(20)                       NOT NULL AUTO_INCREMENT,
    `nota_fiscal_id`     bigint(20)                       NOT NULL,
    `tp_evento`          int(11)                          NOT NULL,
    `desc_evento`        varchar(200)                     NOT NULL,
    `xml`                longtext COLLATE utf8_swedish_ci NOT NULL,

    -- campo de controle
    `inserted`           datetime                         NOT NULL,
    `updated`            datetime                         NOT NULL,
    `version`            int(11) DEFAULT NULL,
    `estabelecimento_id` bigint(20)                       NOT NULL,
    `user_inserted_id`   bigint(20)                       NOT NULL,
    `user_updated_id`    bigint(20)                       NOT NULL,
    PRIMARY KEY (`id`),
    KEY `K_fis_nf_evento_estabelecimento` (`estabelecimento_id`),
    KEY `K_fis_nf_evento_user_inserted` (`user_inserted_id`),
    KEY `K_fis_nf_evento_user_updated` (`user_updated_id`),
    CONSTRAINT `FK_fis_nf_evento_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
    CONSTRAINT `FK_fis_nf_evento_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
    CONSTRAINT `FK_fis_nf_evento_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`),

    KEY `K_fis_nf_evento_nota_fiscal` (`nota_fiscal_id`),
    CONSTRAINT `FK_fis_nf_evento_nota_fiscal` FOREIGN KEY (`nota_fiscal_id`) REFERENCES `fis_nf` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_swedish_ci;



ALTER TABLE fis_nf ADD dt_protocolo_autoriz DATETIME AFTER protocolo_autoriz;