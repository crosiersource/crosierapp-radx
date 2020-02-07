SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


DROP TABLE IF EXISTS `fin_tipo_lancto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_tipo_lancto` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `codigo` int(11) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `icon` varchar(100),
  `obs` varchar(3000) NOT NULL,
  `url` varchar(2000) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_tipo_lancto_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_tipo_lancto_descricao` (`descricao`),

  KEY `K_fin_tipo_lancto_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_tipo_lancto_user_inserted` (`user_inserted_id`),
  KEY `K_fin_tipo_lancto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_tipo_lancto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_tipo_lancto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_tipo_lancto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;


DROP TABLE IF EXISTS `fin_banco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_banco` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `codigo_banco` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `utilizado` bit(1) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_banco_codigo_banco` (`codigo_banco`),
  KEY `K_fin_banco_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_banco_user_inserted` (`user_inserted_id`),
  KEY `K_fin_banco_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_banco_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_banco_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_banco_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;




/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_bandeira_cartao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_bandeira_cartao` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `descricao` varchar(40) NOT NULL,
  `modo_id` bigint(20) NOT NULL,
  `labels` varchar(2000) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

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


) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;




/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_cadeia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_cadeia` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fechada` bit(1) NOT NULL,
  `vinculante` bit(1) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_cadeia_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_cadeia_user_inserted` (`user_inserted_id`),
  KEY `K_fin_cadeia_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_cadeia_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_cadeia_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_cadeia_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_carteira`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_carteira` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) NOT NULL,
  `descricao` varchar(40) NOT NULL,

  `atual` bit(1) NOT NULL DEFAULT b'1',
  `banco_id` bigint(20) DEFAULT NULL,
  `agencia` varchar(30)  DEFAULT NULL,
  `conta` varchar(30)  DEFAULT NULL,

  `abertas` bit(1) NOT NULL,
  `caixa` bit(1) NOT NULL,
  `cheque` bit(1) NOT NULL,
  `concreta` bit(1) NOT NULL,
  `dt_consolidado` date  NOT NULL,
  `limite` decimal(15,2) DEFAULT NULL,
  `operadora_cartao_id` bigint(20) DEFAULT NULL,

  `atual` bit(1) NOT NULL,


  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_carteira_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_carteira_operadora_cartao` (`operadora_cartao_id`),
  KEY `K_fin_carteira_banco` (`banco_id`),
  CONSTRAINT `FK_fin_carteira_banco` FOREIGN KEY (`banco_id`) REFERENCES `fin_banco` (`id`),
  CONSTRAINT `fk_fin_carteira_operadora_cartao` FOREIGN KEY (`operadora_cartao_id`) REFERENCES `fin_operadora_cartao` (`id`),

  KEY `K_fin_carteira_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_carteira_user_inserted` (`user_inserted_id`),
  KEY `K_fin_carteira_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_carteira_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_carteira_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_carteira_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_categoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_categoria` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  
  `codigo` bigint(20) NOT NULL,
  `descricao` varchar(200) NOT NULL,
  `pai_id` bigint(20) DEFAULT NULL,
  
  `centro_custo_dif` bit(1) NOT NULL,
  `codigo_super` bigint(20) NOT NULL,
  `descricao_padrao_moviment` varchar(200)  DEFAULT NULL,
  `totalizavel` bit(1) NOT NULL,
  `descricao_alternativa` varchar(200)  DEFAULT NULL,
  `roles_acess` varchar(2000)  DEFAULT NULL,
  `codigo_ord` bigint(20) DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_categoria_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_categoria_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_categoria_codigo_ord` (`codigo_ord`),
  KEY `K_fin_categoria_pai` (`pai_id`),
  CONSTRAINT `FK_fin_categoria_pai` FOREIGN KEY (`pai_id`) REFERENCES `fin_categoria` (`id`),

  KEY `K_fin_categoria_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_categoria_user_inserted` (`user_inserted_id`),
  KEY `K_fin_categoria_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_categoria_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_categoria_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_categoria_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_centrocusto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_centrocusto` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) NOT NULL,
  `descricao` varchar(40) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_centrocusto_descricao` (`descricao`),
  UNIQUE KEY `UK_fin_centrocusto_codigo` (`codigo`),

  KEY `K_fin_centrocusto_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_centrocusto_user_inserted` (`user_inserted_id`),
  KEY `K_fin_centrocusto_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_centrocusto_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_centrocusto_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_centrocusto_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_grupo` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(40) NOT NULL,
  `ativo` bit(1) NOT NULL,
  `dia_inicio` int(11) NOT NULL,
  `dia_vencto` int(11) NOT NULL,
  `carteira_pagante_id` bigint(20) NOT NULL,
  `categoria_padrao_id` bigint(20) DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_grupo_descricao` (`descricao`),
  KEY `K_fin_grupo_carteira_pagante` (`carteira_pagante_id`),
  KEY `K_fin_grupo_categoria_padrao` (`categoria_padrao_id`),
  CONSTRAINT `FK_fin_grupo_categoria_padrao` FOREIGN KEY (`categoria_padrao_id`) REFERENCES `fin_categoria` (`id`),
  CONSTRAINT `FK_fin_grupo_carteira_pagante` FOREIGN KEY (`carteira_pagante_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_grupo_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_grupo_user_inserted` (`user_inserted_id`),
  KEY `K_fin_grupo_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_grupo_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_grupo_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_grupo_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_grupo_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_grupo_item` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(40) NOT NULL,
  `dt_vencto` date  NOT NULL,
  `fechado` bit(1) NOT NULL,
  `valor_informado` double DEFAULT NULL,
  `anterior_id` bigint(20) DEFAULT NULL,
  `carteira_pagante_id` bigint(20) NOT NULL,
  `movimentacao_pagante_id` bigint(20) NOT NULL,
  `grupo_pai_id` bigint(20) NOT NULL,
  `proximo_id` bigint(20) DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_import_extrato_cabec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_import_extrato_cabec` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `tipo_extrato` varchar(100)  DEFAULT NULL,
  `campo_sistema` varchar(100) CHARACTER SET utf8  NOT NULL,
  `campos_cabecalho` varchar(200) CHARACTER SET utf8  NOT NULL,
  `formato` varchar(100)  DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_import_extrato_cabec_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_import_extrato_cabec_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_import_extrato_cabec_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_import_extrato_cabec_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_modo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_modo` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `codigo` int(11) NOT NULL,
  `descricao` varchar(40) NOT NULL,
  `com_banco_origem` bit(1) NOT NULL,
  `moviment_agrup` bit(1) NOT NULL,
  `transf_caixa` bit(1) NOT NULL,
  `transf_propria` bit(1) NOT NULL,
  `modo_cartao` bit(1) NOT NULL,
  `modo_cheque` bit(1) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_modo_codigo` (`codigo`),
  UNIQUE KEY `UK_fin_modo_descricao` (`descricao`),

  KEY `K_fin_modo_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_modo_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_modo_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_modo_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_modo_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_modo_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_fatura`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_fatura` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `fis_documento_id` bigint(20) DEFAULT NULL,
  `dt_fatura` date  NOT NULL,
  `fechada` bit(1) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  KEY `K_fin_fatura_estabelecimento` (`estabelecimento_id`),
  KEY `K_fin_fatura_user_inserted` (`user_inserted_id`),
  KEY `K_fin_fatura_user_updated` (`user_updated_id`),
  CONSTRAINT `FK_fin_fatura_estabelecimento` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_fatura_user_inserted` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_fatura_user_updated` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;







/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_movimentacao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_movimentacao` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `uuid` char(36) DEFAULT NULL,
  `fatura_id` bigint(20) DEFAULT NULL,
  `modo_id` bigint(20) NOT NULL,
  `documento_banco_id` bigint(20) DEFAULT NULL,
  `documento_num` varchar(200) DEFAULT NULL,

  `pessoa_sacado_id` bigint(20) DEFAULT NULL,
  `pessoa_sacado_info` varchar(400) COLLATE utf8_swedish_ci DEFAULT NULL,
  `pessoa_cedente_id` bigint(20) DEFAULT NULL,
  `pessoa_cedente_info` varchar(400) COLLATE utf8_swedish_ci DEFAULT NULL,

  `fis_nf_id` bigint(20) DEFAULT NULL,

  `quitado` bit(1) NOT NULL,

  `tipo_lancto_id` bigint(20) NOT NULL,
  `carteira_id` bigint(20) NOT NULL,
  `carteira_destino_id` bigint(20) DEFAULT NULL,
  `categoria_id` bigint(20) NOT NULL,
  `centrocusto_id` bigint(20) NOT NULL,
  `grupo_item_id` bigint(20) DEFAULT NULL,
  `status` ENUM('ABERTA', 'REALIZADA') NOT NULL,

  `descricao` varchar(500) NOT NULL,

  `dt_moviment` date  NOT NULL,
  `dt_vencto` date  NOT NULL,
  `dt_vencto_efetiva` date  NOT NULL,
  `dt_pagto` date DEFAULT NULL,
  `dt_util` date  NOT NULL,

  `cheque_banco_id` bigint(20) DEFAULT NULL,
  `cheque_agencia` varchar(30)  DEFAULT NULL,
  `cheque_conta` varchar(30)  DEFAULT NULL,
  `cheque_num_cheque` varchar(20)  DEFAULT NULL,

  `operadora_cartao_id` bigint(20) DEFAULT NULL,
  `bandeira_cartao_id` bigint(20) DEFAULT NULL,
  `plano_pagto_cartao` varchar(50) DEFAULT NULL,

  `recorrente` bit(1) NOT NULL,
  `recorr_dia` int(11) DEFAULT NULL,
  `recorr_frequencia` varchar(50)  DEFAULT NULL,
  `recorr_tipo_repet` varchar(50)  DEFAULT NULL,
  `recorr_variacao` int(11) DEFAULT NULL,

  `valor` decimal(15,2) NOT NULL,
  `descontos` decimal(15,2) DEFAULT NULL,
  `acrescimos` decimal(15,2) DEFAULT NULL,
  `valor_total` decimal(15,2) NOT NULL,

  `cadeia_id` bigint(20) DEFAULT NULL,
  `parcelamento` bit(1) NOT NULL DEFAULT FALSE,
  `cadeia_ordem` int(11) DEFAULT NULL,
  `cadeia_qtde` int(11) DEFAULT NULL,

  `obs` varchar(5000)  DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),


  KEY `K_fin_movimentacao_fatura` (`fatura_id`),
  CONSTRAINT `FK_fin_movimentacao_fatura` FOREIGN KEY (`fatura_id`) REFERENCES `fin_fatura` (`id`),

  UNIQUE KEY UK_fin_movimentacao_uuid (uuid),

  KEY `K_fin_movimentacao_banco` (`documento_banco_id`),
  CONSTRAINT `FK_fin_movimentacao_banco` FOREIGN KEY (`documento_banco_id`) REFERENCES `fin_banco` (`id`),
  KEY `K_fin_movimentacao_pessoa_sacado` (`pessoa_sacado_id`),
  CONSTRAINT `FK_fin_movimentacao_pessoa_sacado` FOREIGN KEY (`pessoa_sacado_id`) REFERENCES `bse_pessoa` (`id`),
  KEY `K_fin_movimentacao_pessoa_cedente` (`pessoa_cedente_id`),
  CONSTRAINT `FK_fin_movimentacao_pessoa_cedente` FOREIGN KEY (`pessoa_cedente_id`) REFERENCES `bse_pessoa` (`id`),
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
  CONSTRAINT `FK_fin_movimentacao_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categoria` (`id`),
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



  KEY `K_fin_movimentacao_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_movimentacao_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_movimentacao_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_movimentacao_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_movimentacao_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_movimentacao_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_operadora_cartao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_operadora_cartao` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(40) NOT NULL,
  `carteira_id` bigint(20) NOT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

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

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;



/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_reg_conf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_reg_conf` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(200) NOT NULL,
  `dt_registro` date  NOT NULL,
  `obs` varchar(5000)  DEFAULT NULL,
  `valor` decimal(19,2) DEFAULT NULL,
  `carteira_id` bigint(20) DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_fin_reg_conf` (`descricao`,`carteira_id`,`dt_registro`),
  KEY `K_fin_reg_conf_carteira` (`carteira_id`),
  CONSTRAINT `FK_fin_reg_conf_carteira` FOREIGN KEY (`carteira_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_reg_conf_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_reg_conf_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_reg_conf_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_reg_conf_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_reg_conf_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_reg_conf_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;





/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fin_regra_import_linha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fin_regra_import_linha` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,

  `regra_regex_java` varchar(500)  DEFAULT NULL,

  `padrao_descricao` varchar(500) NOT NULL,
  `status` varchar(50) NOT NULL,
  `tipo_lancto_id` bigint(20) NOT NULL,
  `carteira_id` bigint(20) DEFAULT NULL,
  `carteira_destino_id` bigint(20) DEFAULT NULL,
  `categoria_id` bigint(20) NOT NULL,
  `centrocusto_id` bigint(20) NOT NULL,
  `modo_id` bigint(20) NOT NULL,
  `sinal_valor` int(11) NOT NULL,

  `cheque_banco_id` bigint(20) DEFAULT NULL,
  `cheque_agencia` varchar(30)  DEFAULT NULL,
  `cheque_conta` varchar(30)  DEFAULT NULL,
  `cheque_num_cheque` varchar(20)  DEFAULT NULL,

  `inserted` datetime  NOT NULL,
  `updated` datetime  NOT NULL,
  `version` int(11) DEFAULT NULL,
  `estabelecimento_id` bigint(20) NOT NULL,
  `user_inserted_id` bigint(20) NOT NULL,
  `user_updated_id` bigint(20) NOT NULL,

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
  CONSTRAINT `FK_fin_regra_import_linha_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `fin_categoria` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_modo` FOREIGN KEY (`modo_id`) REFERENCES `fin_modo` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_cheque_banco` FOREIGN KEY (`cheque_banco_id`) REFERENCES `fin_banco` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_carteira_destino` FOREIGN KEY (`carteira_destino_id`) REFERENCES `fin_carteira` (`id`),

  KEY `K_fin_regra_import_linha_estabelecimento_id` (`estabelecimento_id`),
  KEY `K_fin_regra_import_linha_user_inserted_id` (`user_inserted_id`),
  KEY `K_fin_regra_import_linha_user_updated_id` (`user_updated_id`),
  CONSTRAINT `FK_fin_regra_import_linha_estabelecimento_id` FOREIGN KEY (`estabelecimento_id`) REFERENCES `cfg_estabelecimento` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_user_inserted_id` FOREIGN KEY (`user_inserted_id`) REFERENCES `sec_user` (`id`),
  CONSTRAINT `FK_fin_regra_import_linha_user_updated_id` FOREIGN KEY (`user_updated_id`) REFERENCES `sec_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

