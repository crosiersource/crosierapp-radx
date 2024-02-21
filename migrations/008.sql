SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `fis_cte`;

CREATE TABLE `fis_cte`
(
  `id`                       bigint(20) NOT NULL AUTO_INCREMENT,
  `uuid`                     char(36)   NOT NULL,
  `chave_acesso`             char(44),
  `dt_emissao`               datetime,
  `ambiente`                 varchar(4),
  `dt_saient`                datetime,
  `numero`                   int(11),
  `natureza_operacao`        varchar(60),
  `cct`                      char(8),
  `xml`                      longtext,
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
  `inserted`                 datetime   NOT NULL,
  `updated`                  datetime   NOT NULL,
  `version`                  int(11),
  `estabelecimento_id`       bigint(20) NOT NULL,
  `user_inserted_id`         bigint(20) NOT NULL,
  `user_updated_id`          bigint(20) NOT NULL,
  `json_data`                json,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UF_fis_cte_chave_acesso` (`chave_acesso`),
  KEY `K_fis_cte_documento_emitente` (`documento_emitente`),
  KEY `K_fis_cte_numero` (`numero`),

  KEY `K_fis_cte_estabelecimento` (`estabelecimento_id`),
  KEY `K_fis_cte_user_inserted` (`user_inserted_id`),
  KEY `K_fis_cte_user_updated` (`user_updated_id`),

  CONSTRAINT `FK_fis_cte_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fis_cte_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fis_cte_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE = InnoDB;



alter table fis_distdfe
  drop key fis_distdfe_nsu;

alter table fis_distdfe
  add cte tinyint(1) null;

update fis_distdfe
set cte = false;

alter table fis_distdfe
  change cte cte tinyint(1) not null;

alter table fis_distdfe
  add UNIQUE KEY `fis_distdfe_nsu` (`nsu`, `documento`, `cte`);


alter table fis_distdfe
  add cte_id bigint(20) null;

alter table fis_distdfe
  add FOREIGN KEY `fis_distdfe_cte` (`cte_id`) REFERENCES `fis_cte` (`id`);
