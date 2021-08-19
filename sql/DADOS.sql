START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;



DELETE
FROM cfg_app
WHERE uuid = '9121ea11-dc5d-4a22-9596-187f5452f95a';

INSERT INTO `cfg_app` (`id`, `uuid`, `inserted`, `updated`, `nome`, `obs`, `estabelecimento_id`, `user_inserted_id`,
 `user_updated_id`)
VALUES (null, '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 'crosierapp-radx', 'Módulos "raíz" do Crosier: CRM, RH, Financeiro, Vendas, Estoque, Fiscal', 1,
 1, 1);


DELETE
FROM cfg_app_config
WHERE app_uuid = '440e429c-b711-4411-87ed-d95f7281cd43'
 AND chave = 'produto_form.ordem_abas';

INSERT INTO cfg_app_config
 (id, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, app_uuid, chave, valor)
VALUES (null, now(), now(), 1, 1, 1, '440e429c-b711-4411-87ed-d95f7281cd43', 'produto_form.ordem_abas', 'Produto,Descritivos,Complementos,Fotos,Preços,ERP,Fiscal');



DELETE
FROM cfg_app_config
WHERE chave = 'est_produto_json_metadata'
  AND app_uuid = '9121ea11-dc5d-4a22-9596-187f5452f95a';

INSERT INTO cfg_app_config(id, chave, app_uuid, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, is_json, valor)
VALUES (null, 'est_produto_json_metadata', '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 1, 1, 1, 1,
        '{
  "campos": {
    "depto_codigo": {
      "tipo": "string"
    },
    "depto_nome": {
      "label": "Depto",
      "tipo": "string"
    },
    "grupo_codigo": {
      "tipo": "string"
    },
    "grupo_nome": {
      "label": "Grupo",
      "tipo": "string"
    },
    "subgrupo_codigo": {
      "tipo": "string"
    },
    "subgrupo_nome": {
      "label": "Subgrupo",
      "tipo": "string"
    },
    "fornecedor_nome": {
      "label": "Fornecedor",
      "tipo": "string"
    },
    "fornecedor_documento": {
      "tipo": "string",
      "mask": "cpfcnpj"
    },
    "descricao_produto": {
      "label": "Descrição",
      "obs": "",
      "notuppercase": true,
      "tipo": "html"
    },
    "titulo": {
      "label": "Título",
      "obs": "Título completo do produto",
      "notuppercase": true,
      "tipo": "string",
      "soma_preench": 20
    },
    "caracteristicas": {
      "label": "Características",
      "tipo": "html",
      "soma_preench": 15
    },
    "ean": {
      "label": "EAN",
      "obs": "European Article Number",
      "tipo": "string",
      "soma_preench": 5,
      "trim": false
    },
    "referencia": {
      "label": "Referência",
      "tipo": "string"
    },
    "cod_fornecedor": {
      "label": "Cód Forn",
      "tipo": "string"
    },
    "referencias_extras": {
      "label": "Referências Extras",
      "tipo": "tags_dinam",
      "endpoint": "/est/produto/findValuesTagsDin/",
      "class": "s2allownew notuppercase"
    },
    "ncm": {
      "label": "NCM",
      "obs": "Nomenclatura Comum do Mercosul",
      "tipo": "string"
    },
    "composicao": {
      "label": "Composição",
      "obs": "Informa se esse faz parte de uma composição de produtos",
      "tipo": "bool"
    },
    "porcent_preench": {
      "label": "Preench (%)",
      "obs": "Resultado do cálculo do total de preenchimento deste produto",
      "tipo": "decimal2"
    },
    "porcent_preench_campos_faltantes": {
      "label": "Quais campos ainda precisam ser informados para que o preench chegue em 100%",
      "tipo": "string"
    },
    "preco_custo": {
      "label": "Preço Custo",
      "tipo": "preco",
      "disabled": true
    },
    "preco_tabela": {
      "label": "Preço Tabela",
      "tipo": "preco",
      "disabled": true
    },
    "preco_site": {
      "label": "Preço Site",
      "tipo": "preco",
      "disabled": true
    },
    "preco_atacado": {
      "label": "Preço Atacado",
      "tipo": "preco",
      "disabled": true
    },
    "especif_tec": {
      "label": "Especificações Técnicas",
      "tipo": "html",
      "soma_preench": 15
    },
    "itens_inclusos": {
      "label": "Itens Inclusos",
      "tipo": "html",
      "soma_preench": 15
    },
    "compativel_com": {
      "label": "Compatível com",
      "tipo": "html"
    },
    "dimensoes": {
      "label": "Dimensões",
      "formato": "A,decimal2,cm|L,decimal2,cm|C,decimal2,cm",
      "obs": "(altura x largura x comprimento)",
      "tipo": "compo",
      "soma_preench": 5
    },
    "peso": {
      "label": "Peso",
      "sufixo": "kg",
      "tipo": "decimal3",
      "soma_preench": 5
    },
    "video_url": {
      "label": "Vídeo",
      "prefixo": "URL",
      "notuppercase": true,
      "tipo": "string"
    },
    "ecommerce_id": {
      "label": "E-commerce Id",
      "obs": "Id do produto no e-commerce",
      "tipo": "int",
      "disabled": true
    },
    "ecommerce_dt_integr": {
      "label": "Integrado em",
      "obs": "Data em que o produto foi integrado ao ecommerce",
      "tipo": "datetime",
      "disabled": true
    },
    "ecommerce_integr_por": {
      "label": "Integrado por",
      "obs": "Usuário que realizou a última integração",
      "tipo": "string",
      "disabled": true
    },
    "st": {
      "label": "ST",
      "sufixo": "%",
      "obs": "Substituição Tributária",
      "tipo": "decimal2"
    },
    "icms": {
      "label": "ICMS",
      "sufixo": "%",
      "tipo": "decimal2"
    },
    "ipi": {
      "label": "IPI",
      "sufixo": "%",
      "tipo": "decimal2"
    },
    "pis": {
      "label": "PIS",
      "sufixo": "%",
      "tipo": "decimal2"
    },
    "cofins": {
      "label": "COFINS",
      "sufixo": "%",
      "tipo": "decimal2"
    },
    "preco_custo_fiscal": {
      "label": "Preço CF",
      "tipo": "preco",
      "obs": "Preço de Custo Fiscal",
      "disabled": true
    },
    "marca": {
      "label": "Marca",
      "notuppercase": true,
      "tipo": "select",
      "class": "s2allownew notuppercase",
      "info_integr_ecommerce": {
        "sugestoes_ids": [],
        "tipo_campo_ecommerce": "marca",
        "ecommerce_id": ""
      }
    },
    "qtde_estoque_total": {
      "label": "Estoque Total",
      "obs": "Informa o total em estoque",
      "tipo": "decimal3",
      "disabled": true
    },
    "qtde_estoque_min": {
      "label": "Estoque Mínimo",
      "tipo": "decimal3",
      "disabled": true
    },
    "promocao_de": {
      "label": "De",
      "obs": "Valor original para configuração de promoção",
      "tipo": "preco"
    },
    "promocao_por": {
      "label": "Por",
      "obs": "Valor do produto na promoção",
      "tipo": "preco"
    },
    "promocao_dt_ini": {
      "label": "Validade Inicial",
      "tipo": "datetime"
    },
    "promocao_dt_fim": {
      "label": "Validade Final",
      "tipo": "datetime"
    },
    "promocao_qtde_parcelas": {
      "label": "Qtde Parcelas",
      "tipo": "int"
    },
    "promocao_parcelas_dt_ini": {
      "label": "Validade Inicial",
      "tipo": "datetime"
    },
    "promocao_parcelas_dt_fim": {
      "label": "Validade Final",
      "tipo": "datetime"
    },
    "qtde_imagens": {
      "label": "Qtde Imagens",
      "obs": "Atualizado dinamicamente conforme as imagens são adicionadas/removidas",
      "tipo": "int"
    },
    "imagem1": {
      "label": "Imagem 1",
      "obs": "Link para a primeira imagem",
      "tipo": "string"
    }
  },
  "abas": {
    "Produto": [
      "marca"
    ],
    "Descritivos": [
      "descricao_produto",
      "caracteristicas",
      "especif_tec",
      "itens_inclusos",
      "compativel_com"
    ],
    "Complementos": [
      "dimensoes",
      "peso"
    ],
    "Fotos": [
      "video_url"
    ],
    "Preços": [
      "preco_tabela",
      "preco_site",
      "preco_atacado"
    ],
    "Fiscal": [
      "ncm",
      "preco_custo",
      "st",
      "icms",
      "ipi",
      "pis",
      "cofins",
      "preco_custo_fiscal"
    ],
    "Estoques": [
      "qtde_estoque_total",
      "qtde_estoque_min"
    ],
    "Promoção": [
      "promocao_de",
      "promocao_por",
      "promocao_dt_ini",
      "promocao_dt_fim",
      "promocao_qtde_parcelas",
      "promocao_parcelas_dt_ini",
      "promocao_parcelas_dt_fim"
    ]
  }
}');



DELETE
FROM cfg_app_config
WHERE chave = 'crm_cliente_json_metadata'
 AND app_uuid = '9121ea11-dc5d-4a22-9596-187f5452f95a';

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





DELETE
FROM cfg_app_config
WHERE chave = 'est_fornecedor_json_metadata'
 AND app_uuid = '9121ea11-dc5d-4a22-9596-187f5452f95a';



INSERT INTO cfg_app_config(id, chave, app_uuid, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, is_json, valor)
VALUES (null, 'est_fornecedor_json_metadata', '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 1, 1, 1, 1,
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
 "filial_prop": {
 "label": "",
 "tipo": "select",
 "sugestoes": [
 "S",
 "N"
 ]
 },
 "inscricao_estadual": {
 "label": "IE",
 "tipo": "string"
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
 "Endereços": []
 },
 "enderecoTipos": {
 "FATURAMENTO": "FATURAMENTO",
 "COMERCIAL": "COMERCIAL",
 "ENTREGA": "ENTREGA",
 "RESIDENCIAL": "RESIDENCIAL"
 }
 }');



insert into est_lista_preco
values (1, 'VAREJO', '1900-01-01', null, now(), now(), 0, 1, 1, 1);


DELETE FROM est_depto WHERE codigo = '00';

INSERT INTO est_depto(id , uuid , codigo , nome , json_data , inserted , updated , version , estabelecimento_id , user_inserted_id , user_updated_id)
VALUES (null, uuid(), '00', 'INDEFINIDO', NULL, now(), now(), 0, 1, 1, 1);

select LAST_INSERT_ID() into @lastInsertId;

DELETE FROM est_grupo WHERE codigo = '00';

INSERT INTO est_grupo(id , uuid , depto_id, codigo , nome , json_data , inserted , updated , version , estabelecimento_id , user_inserted_id , user_updated_id)
VALUES (null, uuid(), @lastInsertId, '00', 'INDEFINIDO', NULL, now(), now(), 0, 1, 1, 1);

select LAST_INSERT_ID() into @lastInsertId;

DELETE FROM est_subgrupo WHERE codigo = '00';

INSERT INTO est_subgrupo(id , uuid , grupo_id, codigo , nome , json_data , inserted , updated , version , estabelecimento_id , user_inserted_id , user_updated_id)
VALUES (null, uuid(), @lastInsertId, '00', 'INDEFINIDO', NULL, now(), now(), 0, 1, 1, 1);





INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 1, 0, 15, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.981, 16, 30, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.969, 31, 45, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.958, 46, 60, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.946, 61, 75, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.935, 76, 90, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.924, 91, 105, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.914, 106, 120, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.904, 121, 135, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.894, 136, 1999999999, now(), now(), 0, 1, 1, 1);



TRUNCATE TABLE fin_centrocusto;

INSERT INTO `fin_centrocusto` (`id`, `codigo`, `descricao`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'GLOBAL', now(), now(), NULL, 1, 1, 1);


TRUNCATE TABLE fin_operadora_cartao;

INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 'CIELO', 21, now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (2, 'REDECARD', 22, now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (3, 'STONE', 23, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (4, 'MODERNINHA', 24, now(), now(), 0, 1, 1, 1);

TRUNCATE TABLE fin_carteira;

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
 user_inserted_id, user_updated_id, atual)
VALUES (1, 1, 'GERAL', null, null, null, true, false, false, true, '1900-01-01', 0, null, now(), now(), 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (21, 21, 'CIELO', null, null, null, true, false, false, true, '1900-01-01', 0, 1, now(), now(), 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (22, 22, 'REDECARD', null, null, null, true, false, false, true, '1900-01-01', 0, 2, now(), now(), 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (23, 23, 'STONE', null, null, null, true, false, false, true, '1900-01-01', 0, 3, now(), now(), 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (24, 24, 'MODERNINHA', null, null, null, true, false, false, true, '1900-01-01', 0, 4, now(), now(), 0, 1, 1, 1, true);


INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
 user_inserted_id, user_updated_id, atual)
VALUES (50, 50, 'MOVIMENTAÇÕES AGRUPADAS', null, null, null, true, false, false, true, '1900-01-01', 0, null, now(), now(), 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (99, 99, 'INDEFINIDA', null, null, null, true, false, false, false, '1900-01-01', 0, null, now(), now(), 0, 1, 1, 1, true);


ALTER TABLE fin_carteira AUTO_INCREMENT = 100;


TRUNCATE TABLE fin_tipo_lancto;

REPLACE INTO `fin_tipo_lancto` (`id`, `codigo`, `descricao`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (20, 20, 'MOVIMENTAÇÃO NORMAL', now(), now(), 0, 1, 1, 1),
 (60, 60, 'TRANSFERÊNCIA ENTRE CARTEIRAS', now(), now(), 0, 1, 1, 1),
 (61, 61, 'TRANSFERÊNCIA DE ENTRADA DE CAIXA', now(), now(), 0, 1, 1, 1),
 (62, 62, 'FATURA TRANSACIONAL', now(), now(), 0, 1, 1, 1),
 (63, 63, 'MOVIMENTAÇÃO CARTÃO CRÉDITO/DÉBITO', now(), now(), 0, 1, 1, 1);

TRUNCATE TABLE fin_categoria;

REPLACE INTO `fin_categoria` (`id`, `codigo`, `descricao`, `pai_id`, `centro_custo_dif`, `codigo_super`, `descricao_padrao_moviment`, `totalizavel`, `descricao_alternativa`, `roles_acess`, `codigo_ord`,
 `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'ENTRADAS', NULL, 0, 1, NULL, 0, NULL, NULL, 100000000000, now(), now(), 0, 1, 1, 1),
 (2, 2, 'SAÍDAS', NULL, 0, 2, NULL, 0, NULL, NULL, 200000000000, now(), now(), 0, 1, 1, 1),
 (101, 101, 'RECEB. VENDAS INTERNAS', 1, 0, 1, '', 0, '', '', 101000000000, now(), now(), 3, 1, 1, 1),
 (102, 102, 'RECEB. VENDAS EXTERNAS', 1, 0, 1, NULL, 0, NULL, NULL, 102000000000, now(), now(), 1, 1, 1, 1),
 (103, 103, 'ENTRADA - EMPRÉSTIMO', 1, 0, 1, NULL, 0, NULL, NULL, 103000000000, now(), now(), 0, 1, 1, 1),
 (150, 150, 'RECEB. OUTROS', 1, 0, 1, NULL, 0, NULL, NULL, 150000000000, now(), now(), 1, 1, 1, 1),
 (151, 151, 'ENTRADA - AJUSTE DE CAIXA', 1, 0, 1, NULL, 0, NULL, NULL, 151000000000, now(), now(), 0, 1, 1, 1),
 (160, 160, 'RECEB. PARA TERCEIROS', 1, 0, 1, NULL, 0, NULL, NULL, 160000000000, now(), now(), 0, 1, 1, 1),
 (170, 170, 'OUTRAS ENTRADAS', 1, 0, 1, '', 0, '', '', 170000000000, now(), now(), 0, 1, 1, 1),
 (179, 179, 'REAPRESENTAÇÃO DE CHEQUE (E)', 1, 0, 1, '', 0, '', '', 179000000000, now(), now(), 0, 1, 1, 1),
 (180, 180, 'ENTRADAS PESSOAIS', 1, 0, 1, '', 0, '', null, 180000000000, now(), now(), 2, 1, 1, 1),
 (188, 188, 'MOVIMENTAÇÃO ESTORNADA (E)', 1, 0, 1, '%s', 0, NULL, NULL, 188000000000, now(), now(), 0, 1, 1, 1),
 (189, 189, 'ESTORNO DE SAÍDA DIRECIONADA', 1, 0, 1, '', 0, '', '', 189000000000, now(), now(), 2, 1, 1, 1),
 (110, 110, 'RECEB. FATURA', 1, 0, 1, '', 0, '', '', 110000000000, now(), now(), 0, 1, 1, 1),
 (191, 191, 'TRANSFERÊNCIA DE FATURA', 1, 0, 1, '', 0, '', '', 191000000000, now(), now(), 0, 1, 1, 1),
 (291, 291, 'TRANSFERÊNCIA PARA FATURA', 2, 0, 2, '', 0, '', '', 291000000000, now(), now(), 0, 1, 1, 1),
 (195, 195, 'ENTRADAS - A CONFERIR', 1, 0, 1, '', 0, '', NULL, 195000000000, now(), now(), 0, 1, 1, 1),
 (295, 295, 'SAIDAS - A CONFERIR', 1, 0, 2, '', 0, '', NULL, 295000000000, now(), now(), 0, 1, 1, 1),
 (199, 199, 'TRANSFERÊNCIA DE CONTA', 1, 0, 1, '', 0, '', '', 199000000000, now(), now(), 0, 1, 1, 1),
 (299, 299, 'TRANSFERÊNCIA PARA CONTA', 2, 0, 2, '', 0, '', '', 299000000000, now(), now(), 0, 1, 1, 1),
 (201, 201, 'CUSTOS FIXOS', 2, 0, 2, NULL, 0, NULL, NULL, 201000000000, now(), now(), 0, 1, 1, 1),
 (202, 202, 'CUSTOS VARIÁVEIS', 2, 0, 2, NULL, 0, NULL, NULL, 202000000000, now(), now(), 0, 1, 1, 1),
 (203, 203, 'EMPRÉSTIMOS', 2, 0, 2, NULL, 0, NULL, NULL, 203000000000, now(), now(), 1, 1, 1, 1),
 (204, 204, 'INVESTIMENTOS', 2, 0, 2, '', 0, '', '', 204000000000, now(), now(), 0, 1, 1, 1),
 (209, 209, 'SAÍDAS VIRTUAIS', 2, 0, 2, NULL, 0, NULL, NULL, 209000000000, now(), now(), 0, 1, 1, 1), -- apenas para ser "PAI" da 209001
 (250, 250, 'OUTRAS SAÍDAS', 2, 0, 2, NULL, 0, NULL, NULL, 250000000000, now(), now(), 0, 1, 1, 1),
 (251, 251, 'SAÍDA - AJUSTE DE CAIXA', 2, 0, 2, NULL, 0, NULL, NULL, 251000000000, now(), now(), 0, 1, 1, 1),
 (252, 252, 'AJUSTE COBRANÇA FATURA', 2, 0, 2, '', 0, '', '', 252000000000, now(), now(), 0, 1, 1, 1),
 (260, 260, 'PAGTO TERCEIROS', 2, 0, 2, NULL, 0, NULL, NULL, 260000000000, now(), now(), 0, 1, 1, 1),
 (270, 270, 'MOVIMENTAÇÃO ESTORNADA (S)', 2, 0, 2, NULL, 0, NULL, NULL, 270000000000, now(), now(), 0, 1, 1, 1),
 (279, 279, 'REAPRESENTAÇÃO DE CHEQUE (S)', 2, 0, 2, NULL, 0, NULL, NULL, 279000000000, now(), now(), 0, 1, 1, 1),
 (280, 280, 'SAÍDAS PESSOAIS', 2, 0, 2, '', 0, '', 'ROLE_FINANCEIRO_PROPRIET', 280000000000, now(), now(), 2, 1, 1, 1),
 (290, 290, 'PAGTO. GRUPO MOVIMENT.', 2, 0, 2, NULL, 0, NULL, NULL, 290000000000, now(), now(), 1, 1, 1, 1),
 (103001, 103001, 'ENTRADA GIRO RÁPIDO', 45, 0, 1, NULL, 0, NULL, NULL, 103001000000, now(), now(), 0, 1, 1, 1),
 (103002, 103002, 'ENTRADA CAPITAL DE GIRO', 45, 0, 1, NULL, 0, NULL, NULL, 103002000000, now(), now(), 1, 1, 1, 1),
 (201001, 201001, 'LUZ', 9, 0, 2, NULL, 0, NULL, NULL, 201001000000, now(), now(), 0, 1, 1, 1),
 (201002, 201002, 'TELEFONE FIXO / INTERNET', 9, 0, 2, NULL, 0, NULL, NULL, 201002000000, now(), now(), 1, 1, 1, 1),
 (201003, 201003, 'TELEFONE CELULAR', 9, 0, 2, NULL, 0, NULL, NULL, 201003000000, now(), now(), 0, 1, 1, 1),
 (201004, 201004, 'ÁGUA', 9, 0, 2, NULL, 0, NULL, NULL, 201004000000, now(), now(), 0, 1, 1, 1),
 (201005, 201005, 'SEGUROS', 9, 0, 2, NULL, 0, NULL, NULL, 201005000000, now(), now(), 0, 1, 1, 1),
 (201006, 201006, 'MATERIAIS DE EXPEDIENTE', 9, 0, 2, NULL, 0, NULL, NULL, 201006000000, now(), now(), 0, 1, 1, 1),
 (201007, 201007, 'COMBUSTÍVEIS E DESP. VIAGENS', 9, 0, 2, NULL, 0, NULL, NULL, 201007000000, now(), now(), 0, 1, 1, 1),
 (201008, 201008, 'MARKETING/PUBLICIDADE', 9, 0, 2, NULL, 0, NULL, NULL, 201008000000, now(), now(), 0, 1, 1, 1),
 (201009, 201009, 'FRETES', 9, 0, 2, 'PAGTO DE FRETE', 0, NULL, NULL, 201009000000, now(), now(), 1, 1, 1, 1),
 (201010, 201010, 'EMBALAGENS', 9, 0, 2, NULL, 0, NULL, NULL, 201010000000, now(), now(), 0, 1, 1, 1),
 (201011, 201011, 'TAXAS, ENCARGOS, DESP. BANCÁRIAS', 9, 0, 2, '', 0, '', '', 201011000000, now(), now(), 1, 1, 1, 1),
 (201012, 201012, 'IRFF', 9, 0, 2, NULL, 0, NULL, NULL, 201012000000, now(), now(), 1, 1, 1, 1),
 (201013, 201013, 'TAXAS E IMPOSTOS DIVERSOS', 9, 0, 2, NULL, 0, NULL, NULL, 201013000000, now(), now(), 0, 1, 1, 1),
 (201014, 201014, 'ACIPG/CDL', 9, 0, 2, NULL, 0, NULL, NULL, 201014000000, now(), now(), 0, 1, 1, 1),
 (201015, 201015, 'DESP. LICITAÇÕES', 9, 0, 2, NULL, 0, NULL, NULL, 201015000000, now(), now(), 0, 1, 1, 1),
 (201016, 201016, 'MÁQUINAS E EQUIPAMENTOS', 9, 0, 2, NULL, 0, NULL, NULL, 201016000000, now(), now(), 0, 1, 1, 1),
 (201017, 201017, 'IPTU', 9, 0, 2, '', 0, '', '', 201017000000, now(), now(), 0, 1, 1, 1),
 (201018, 201018, 'MANUTENÇÕES EM VEÍCULOS', 9, 0, 2, '', 0, '', '', 201018000000, now(), now(), 0, 1, 1, 1),
 (201019, 201019, 'ALUGUEL', 9, 0, 2, '%S', 0, '', '', 201019000000, now(), now(), 0, 1, 1, 1),
 (201099, 201099, 'CUSTOS DIVERSOS', 9, 0, 2, '', 0, '', '', 201099000000, now(), now(), 0, 1, 1, 1),
 (201100, 201100, 'DEPTO. PESSOAL', 9, 0, 2, NULL, 0, NULL, NULL, 201100000000, now(), now(), 0, 1, 1, 1),
 (202001, 202001, 'CUSTOS DE MERCADORIAS', 10, 0, 2, 'PAGTO A FORNECEDOR', 0, '', '', 202001000000, now(), now(), 1, 1, 1, 1),
 (202002, 202002, 'IMPOSTO SUPER-SIMPLES', 10, 0, 2, NULL, 0, NULL, NULL, 202002000000, now(), now(), 0, 1, 1, 1),
 (202003, 202003, 'SAÍDA DIRECIONADA', 10, 0, 2, '', 0, '', 'ROLE_FINANCEIRO_PROPRIET', 202003000000, now(), now(), 3, 1, 1, 1),
 (202004, 202004, 'TAXA ADMIN. CREDIÁRIO', 10, 0, 2, NULL, 0, NULL, NULL, 202004000000, now(), now(), 0, 1, 1, 1),
 (202005, 202005, 'CUSTO FINANCEIRO CARTÕES', 10, 0, 2, NULL, 0, NULL, NULL, 202005000000, now(), now(), 0, 1, 1, 1),
 (202006, 202006, 'DOAÇÕES, AJUDAS, CONTRIBUIÇÕES', 10, 0, 2, NULL, 0, NULL, NULL, 202006000000, now(), now(), 0, 1, 1, 1),
 (203001, 203001, 'PAGTO. JUROS/TAXAS', 39, 0, 2, NULL, 0, NULL, NULL, 203001000000, now(), now(), 0, 1, 1, 1),
 (203002, 203002, 'PAGTO. CAPITAL', 39, 0, 2, NULL, 0, NULL, NULL, 203002000000, now(), now(), 0, 1, 1, 1),
 (203003, 203003, 'PAGTO. GIRO RÁPIDO', 39, 0, 2, NULL, 0, NULL, NULL, 203003000000, now(), now(), 2, 1, 1, 1),
 (201100001, 201100001, 'FOLHA DE PAGAMENTO', 28, 0, 2, NULL, 0, NULL, NULL, 201100001000, now(), now(), 0, 1, 1, 1),
 (201100002, 201100002, 'INSS', 28, 0, 2, NULL, 0, NULL, NULL, 201100002000, now(), now(), 0, 1, 1, 1),
 (201100003, 201100003, 'FGTS', 28, 0, 2, NULL, 0, NULL, NULL, 201100003000, now(), now(), 0, 1, 1, 1),
 (201100004, 201100004, 'VALE TRANSPORTE', 28, 0, 2, NULL, 0, NULL, NULL, 201100004000, now(), now(), 0, 1, 1, 1),
 (201100005, 201100005, 'SINDICATO', 28, 0, 2, NULL, 0, NULL, NULL, 201100005000, now(), now(), 0, 1, 1, 1),
 (201100006, 201100006, 'HONORÁRIOS CONTÁBEIS', 28, 0, 2, NULL, 0, NULL, NULL, 201100006000, now(), now(), 0, 1, 1, 1),
 (201100007, 201100007, 'MÃO-DE-OBRA', 28, 0, 2, NULL, 0, NULL, NULL, 201100007000, now(), now(), 0, 1, 1, 1),
 (201100008, 201100008, 'CONVÊNIOS', 28, 0, 2, NULL, 0, NULL, NULL, 201100008000, now(), now(), 0, 1, 1, 1),
  (201100999, 201100999, 'ENCARGOS SOCIAIS (GERAIS)', 28, 0, 2, NULL, 0, NULL, NULL, 201100999000, now(), now(), 0, 1, 1, 1),
 (202005001, 202005001, 'CUSTO FINANCEIRO CARTÕES DE CRÉDITO', 51, 0, 2, 'CUSTO FINANCEIRO CARTÕES DE CRÉDITO', 0, NULL, NULL, 202005001000, now(), now(), 1, 1, 1, 1),
 (202005002, 202005002, 'CUSTO FINANCEIRO CARTÕES DE DÉBITO', 51, 0, 2, 'CUSTO FINANCEIRO CARTÕES DE DÉBITO', 0, NULL, NULL, 202005002000, now(), now(), 1, 1, 1, 1),
 (203002001, 203002001, 'PAGTO. CAPITAL LONGO PRAZO', 47, 0, 2, NULL, 0, NULL, NULL, 203002001000, now(), now(), 0, 1, 1, 1),
 (203002002, 203002002, 'PAGTO. CAPITAL CURTO PRAZO', 47, 0, 2, NULL, 0, NULL, NULL, 203002002000, now(), now(), 0, 1, 1, 1),
 (203002003, 203002003, 'PAGTO. DDPCG', 47, 0, 2, NULL, 0, NULL, NULL, 203002003000, now(), now(), 0, 1, 1, 1);


TRUNCATE TABLE fin_modo;


INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'EM ESPÉCIE', 0, 0, 0, 1, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (2, 2, 'DÉBITO AUTOMÁTICO', 1, 0, 0, 0, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (3, 3, 'CHEQUE PRÓPRIO', 0, 0, 0, 1, 0, 1, now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (7, 7, 'PIX/TRANSF. BANCÁRIA', 1, 0, 1, 1, 0, 0, now(), now(), 4, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (5, 5, 'DEPÓSITO BANCÁRIO', 1, 0, 0, 1, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (99, 99, 'INDEFINIDO', 0, 0, 0, 0, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (6, 6, 'BOLETO/GUIA/DDA', 1, 0, 0, 0, 0, 0, now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (4, 4, 'CHEQUE TERCEIROS', 0, 0, 1, 1, 0, 1, now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (9, 9, 'RECEB. CARTÃO CRÉDITO', 0, 0, 0, 1, 1, 0, now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (10, 10, 'RECEB. CARTÃO DÉBITO', 0, 0, 0, 1, 1, 0, now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (11, 11, 'TRANSF. ENTRE CONTAS', 0, 0, 1, 1, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (50, 50, 'MOVIMENTAÇÃO AGRUPADA', 0, 1, 0, 0, 0, 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
 `user_inserted_id`, `user_updated_id`)
VALUES (60, 60, 'VIRTUAL', 0, 0, 0, 0, 0, 0, now(), now(), 0, 1, 1, 1);



TRUNCATE TABLE fin_banco;

INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (266, 1, 'BANCO DO BRASIL', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (267, 3, 'BANCO DA AMAZONIA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (268, 4, 'BANCO DO NORDESTE DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (269, 12, 'BANCO STANDARD DE INVESTIMENTOS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (270, 21, 'BANESTES BANCO DO ESTADO DO ESPIRITO SANTO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (271, 24, 'BANCO DE PERNAMBUCO -BANDEPE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (272, 25, 'BANCO ALFA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (273, 27, 'BANCO DO ESTADO DE SANTA CATARINA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (274, 29, 'BANCO BANERJ', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (275, 31, 'BANCO BEG', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (276, 33, 'BANCO SANTANDER', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (277, 34, 'BANCO DO ESTADO DO AMAZONAS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (278, 36, 'BANCO BRADESCO BBI', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (279, 37, 'BANCO DO ESTADO DO PARA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (280, 38, 'BANCO BANESTADO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (281, 39, 'BANCO DO ESTADO DO PIAUI - BEP', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (282, 40, 'BANCO CARGILL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (283, 41, 'BANCO DO ESTADO DO RIO GRANDE DO SUL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (284, 44, 'BANCO BVA SA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (285, 45, 'BANCO OPPORTUNITY', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (286, 47, 'BANCO DO ESTADO DE SERGIPE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (287, 62, 'HIPERCARD BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (288, 63, 'BANCO IBI - BANCO MULTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (289, 64, 'GOLDMAN SACHS DO BRASIL BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (290, 65, 'LEMON BANK BANCO MÚLTIPLO S..A', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (291, 66, 'BANCO MORGAN STANLEY', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (292, 69, 'BPN BRASIL BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (293, 70, 'BRB - BANCO DE BRASILIA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (294, 72, 'BANCO RURAL MAIS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (295, 73, 'BB BANCO POPULAR DO BRASL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (296, 74, 'BANCO J.SAFRA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (297, 75, 'BANCO CR2', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (298, 76, 'BANCO KDB DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (299, 78, 'BES INVESTIMENTO DO BRASIL - BANCO DE INVESTIMENTO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (300, 95, 'BANCO CONFIDENCE DE CÂMBIO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (301, 96, 'BANCO BM&F DE SERVIÇOS DE LIQUIDAÇÃO E CUSTÓDIA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (302, 104, 'CAIXA ECONÔMICA FEDERAL', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (303, 107, 'BANCO BBM', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (304, 116, 'BANCO ÚNICO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (305, 119, 'BANCO WESTERN UNION DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (306, 125, 'BRASIL PLURAL - BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (307, 151, 'BANCO NOSSA CAIXA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (308, 175, 'BANCO FINASA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (309, 184, 'BANCO ITAÚ - BBA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (310, 204, 'BANCO BRADESCO CARTÕES', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (311, 208, 'BANCO UBS PACTUAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (312, 212, 'BANCO MATONE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (313, 213, 'BANCO ARBI', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (314, 214, 'BANCO DIBENS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (315, 215, 'BANCO ACOMERCIAL E DE INVESTIMENTO SUDAMERIS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (316, 217, 'BANCO JOHN DEERE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (317, 218, 'BANCO BONSUCESSO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (318, 222, 'BANCO CLAYON BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (319, 224, 'BANCO FIBRA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (320, 225, 'BANCO BRASCAN', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (321, 229, 'BANCO CRUZEIRO DO SUL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (322, 230, 'UNICARD BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (323, 233, 'BANCO GE CAPITAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (324, 237, 'BANCO BRADESCO', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (325, 241, 'BANCO CLASSICO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (326, 243, 'BANCO MAXIMA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (327, 246, 'BANCO ABC-BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (328, 248, 'BANCO BOAVISTA INTERATLANTICO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (329, 249, 'BANCO INVESTCRED UNIBANCO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (330, 250, 'BANCO SCHAHIN', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (331, 252, 'BANCO FININVEST', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (332, 254, 'PARANÁ BANCO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (333, 263, 'BANCO CACIQUE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (334, 265, 'BANCO FATOR', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (335, 266, 'BANCO CEDULA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (336, 300, 'BANCO DE LA NACION ARGENTINA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (337, 318, 'BANCO BMG', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (338, 320, 'BANCO INDUSTRIAL E COMERCIAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (339, 341, 'BANCO ITAU', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (340, 356, 'BANCO ABN AMRO REAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (341, 366, 'BANCO SOCIETE GENERALE BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (342, 370, 'BANCO WESTLB DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (343, 376, 'BANCO J.P. MORGAN', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (344, 389, 'BANCO MERCANTIL DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (345, 394, 'BANCO BMC', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (346, 399, 'HSBC BANK BRASIL -BANCO MULTIPLO', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (347, 409, 'UNIBANCO - UNIAO DE BANCOS BRASILEIROS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (348, 412, 'BANCO CAPITAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (349, 422, 'BANCO SAFRA', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (350, 453, 'BANCO RURAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (351, 456, 'BANCO DE TOKYO-MITSUBISHI UFJ BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (352, 464, 'BANCO SUMITOMO MITSUI BRASILEIRO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (353, 473, 'BANCO CAIXA GERAL - BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (354, 477, 'CITIBANK N.A.', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (355, 479, 'BANCO ITAUBANK', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (356, 487, 'DEUTSCHE BANK S. A. - BANCO ALEMAO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (357, 488, 'JPMORGAN CHASE BANK, NATIONAL ASSOCIATION', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (358, 492, 'ING BANK N.V.', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (359, 494, 'BANCO DE LA REPUBLICA ORIENTAL DEL URUGUAY', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (360, 495, 'BANCO DE LA PROVINCIA DE BUENOS AIRES', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (361, 505, 'BANCO CREDIT SUISSE (BRASIL)', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (362, 600, 'BANCO LUSO BRASILEIRO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (363, 604, 'BANCO INDUSTRIAL DO BRASIL S. A.', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (364, 610, 'BANCO VR', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (365, 611, 'BANCO PAULISTA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (366, 612, 'BANCO GUANABARA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (367, 613, 'BANCO PECUNIA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (368, 623, 'BANCO PANAMERICANO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (369, 626, 'BANCO FICSA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (370, 630, 'BANCO INTERCAP', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (371, 633, 'BANCO RENDIMENTO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (372, 634, 'BANCO TRIANGULO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (373, 637, 'BANCO SOFISA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (374, 638, 'BANCO PROSPER', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (375, 641, 'BANCO ALVORADA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (376, 643, 'BANCO PINE', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (377, 652, 'BANCO ITAÚ HOLDING FINANCEIRA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (378, 653, 'BANCO INDUSVAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (379, 654, 'BANCO A.J. RENNER', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (380, 655, 'BANCO VOTORANTIM', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (381, 707, 'BANCO DAYCOVAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (382, 719, 'BANIF - BANCO INTERNACIONAL DO FUNCHAL (BRASIL),', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (383, 721, 'BANCO CREDIBEL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (384, 734, 'BANCO GERDAU', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (385, 735, 'BANCO POTTENCIAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (386, 738, 'BANCO MORADA', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (387, 739, 'BANCO BGN', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (388, 740, 'BANCO BARCLAYS', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (389, 741, 'BANCO RIBEIRAO PRETO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (390, 743, 'BANCO SEMEAR', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (391, 744, 'BANKBOSTON N.A.', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (392, 745, 'BANCO CITIBANK', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (393, 746, 'BANCO MODAL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (394, 747, 'BANCO RABOBANK INTERNATIONAL BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (395, 748, 'BANCO COOPERATIVO SICREDI', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (396, 749, 'BANCO SIMPLES', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (397, 751, 'DRESDNER BANK BRASIL BANCO MULTIPLO.', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (398, 752, 'BANCO BNP PARIBAS BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (399, 753, 'BANCO COMERCIAL URUGUAI', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (400, 755, 'BANK OF AMERICA MERRILL LYNCH BANCO MÚLTIPLO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (402, 757, 'BANCO KEB DO BRASIL', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (403, 756, 'BANCO COOPERATIVO DO BRASIL - BANCOOB (SICOOB)', 1, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (404, 999, 'INDEFINIDO', 0, now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (NULL, 197, 'STONE PAGAMENTOS S.A.', true, now(), now(), 0, 1, 1, 1);






TRUNCATE TABLE fin_bandeira_cartao;

INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 'MASTER MAESTRO', 10, 'MC MAESTRO\r\nMASTERCARD\r\nMASTERCARD MAESTRO', now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (2, 'VISA ELECTRON', 10, 'VISA ELECTRON', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (3, 'ELO DÉBITO', 10, 'ELO DÉBITO\r\nELO', now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (4, 'VISA', 9, 'VISA\r\nVISA PARCELADO\r\nVISA CRÉDITO', now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (5, 'MASTERCARD', 9, 'MASTERCARD\r\nMC PARCELADO\r\nMC CRÉDITO', now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (6, 'HIPERCARD', 9, 'HIPERCARD', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (7, 'ELO CRÉDITO', 9, 'ELO PARCELADO\r\nELO', now(), now(), 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (8, 'AGIPLAN CRÉDITO', 9, 'AGIPLAN CRÉDITO', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (9, 'DINERS', 9, 'DINERS\r\nDINERS CLUB', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (10, 'SICREDI', 9, 'SICREDI', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (11, 'CABAL CRÉDITO', 9, 'CABAL CRÉDITO', now(), now(), 0, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (12, 'AMEX', 9, 'AMEX\r\nAMERICANEXPRESS', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (13, 'N INF CRÉD', 9, ' ', now(), now(), 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (14, 'N INF DÉB', 10, ' ', now(), now(), 3, 1, 1, 1);



REPLACE INTO `ven_plano_pagto` (`id`, `codigo`, `descricao`, `ativo`, `json_data`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (null, '001', 'A VISTA (ESPÉCIE)', 1, '{\"modo_id\": \"1\", \"tipo_carteiras\": \"caixa\", \"aceita_parcelas\": false}', now(), now(), 0, 1, 1, 1),
 (null, '002', 'A VISTA (CHEQUE)', 1, '{\"modo_id\": \"8\", \"tipo_carteiras\": \"caixa\", \"aceita_parcelas\": false}', now(), now(), 0, 1, 1, 1),
 (null, '003', 'CARTÃO DÉBITO', 1, '{\"modo_id\": \"10\", \"tipo_carteiras\": \"caixa\", \"aceita_parcelas\": false, \"tipo_carteiras_destino\": \"operadora_cartao\"}', now(), now(), 0, 1, 1, 1),
 (null, '010', 'CARTÃO DE CRÉDITO', 1, '{\"modo_id\": \"9\", \"tipo_carteiras\": \"caixa\", \"aceita_parcelas\": true, \"tipo_carteiras_destino\": \"operadora_cartao\"}', now(), now(), 0, 1, 1, 1),
 (null, '020', 'DEPÓSITO/TRANSFERÊNCIA', 1, '{\"modo_id\": \"5\", \"tipo_carteiras\": \"banco\", \"aceita_parcelas\": true}', now(), now(), 0, 1, 1, 1),
 (null, '030', 'BOLETO', 1, '{\"modo_id\": \"7\", \"tipo_carteiras\": \"banco\", \"aceita_parcelas\": true}', now(), now(), 0, 1, 1, 1),
 (null, '040', 'PIX', 1, '{\"modo_id\": \"7\", \"tipo_carteiras\": \"banco\", \"aceita_parcelas\": false}', now(), now(), 0, 1, 1, 1),
 (null, '999', 'NÃO INFORMADO', 1, NULL, now(), now(), 0, 1, 1, 1);


COMMIT;