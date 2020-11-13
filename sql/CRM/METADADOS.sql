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



INSERT INTO crm_cliente(id, nome, documento, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id) value (null, 'CONSUMIDOR NÃO IDENTIFICADO', '99999999999', now(), now(), 0, 1, 1, 1);


INSERT INTO cfg_app_config(id, chave, app_uuid, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, is_json, valor)
VALUES (null, 'crm_cliente_json_metadata', '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 1, 1, 1, 1,
'{
  "campos": {
    "nome_fantasia": {
      "label": "Nome Fantasia",
      "tipo": "string"
    },
    "tipo_pessoa": {
      "label": "",
      "tipo": "select",
      "sugestoes": [
        "PF",
        "PJ"
      ]
    },
    "dt_nascimento": {
      "label": "Dt Nascimento",
      "tipo": "date"
    },
    "rg": {
      "label": "RG",
      "tipo": "string"
    },
    "inscricao_estadual": {
      "label": "IE",
      "tipo": "string"
    },
    "sexo": {
      "label": "Sexo",
      "tipo": "select",
      "sugestoes": [
        "M",
        "F"
      ]
    },
    "canal": {
      "label": "Canal",
      "tipo": "select",
      "class": "s2allownew",
      "sugestoes": [
        "ECOMMERCE",
        "LOJA FÍSICA"
      ]
    },
    "email": {
      "label": "E-mail",
      "tipo": "email"
    },
    "fone1": {
      "label": "Fone (1)",
      "tipo": "fone"
    },
    "fone2": {
      "label": "Fone (2)",
      "tipo": "fone"
    }
  },
  "abas": {
    "Dados": [],
    "Endereços": [],
    "E-commerce": []
  },
  "enderecoTipos": {
    "FATURAMENTO": "FATURAMENTO",
    "COMERCIAL": "COMERCIAL",
    "ENTREGA": "ENTREGA",
    "RESIDENCIAL": "RESIDENCIAL"
  }
}');