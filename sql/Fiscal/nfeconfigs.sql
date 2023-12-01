begin;

INSERT INTO cfg_app_config(id, chave, app_uuid, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, is_json, valor)
VALUES (null, 'nfeconfigs_99999999999999', '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 1, 1, 1, 1,
        '{
          "id": "1",
          "atualizacao": "2017-02-20 09:11:21.000000",
          "CSOSN_padrao": 103,
          "tpAmb": 2,
          "razaosocial": "",
          "infRespTec_xContato": "",
          "infRespTec_email": "",
          "logo_fiscal": "https://xxx/build/static/images/logo.jpg",
          "siglaUF": "PR",
          "cnpj": "",
          "schemes": "PL_009_V4",
          "versao": "4.00",
          "tokenIBPT": "AAAAAAA",
          "aProxyConf": {
            "proxyIp": "",
            "proxyPort": "",
            "proxyUser": "",
            "proxyPass": ""
          },
          "ie": "",
          "CSC_prod": "PLHKUZWAULVXEMAZXKIT4AVP2FBJAYZFII7U",
          "CSCid_prod": "000001",
          "CSC_hom": "ZZYBA8QXJFIH6SMGW3YR9CVONAJIUULWKJ4Z",
          "CSCid_hom": "000001",
          "serie_NFE_PROD": "0",
          "serie_NFCE_PROD": "0",
          "serie_NFE_HOM": "88",
          "serie_NFCE_HOM": "8",
          "enderecoCompleto": "Rua xxxxx, 999. xxxxx 99999-999 - Xxx Xxx - PR",
          "telefone": "(99) 9999-9999",
          "enderEmit_xLgr": "RUA XXXXXX XXXX",
          "enderEmit_nro": "999",
          "enderEmit_xBairro": "XXXXX",
          "enderEmit_xMun": "XXXXXXX",
          "enderEmit_uf": "XX",
          "enderEmit_cep": "99999-999",
          "fone1": "(99) 9999-9999",
          "certificado": "qwertyqwerty0987654321"
          "certificadoPwd": "123456789"
        }');

select LAST_INSERT_ID()
into @lastInsertId;


INSERT INTO cfg_app_config(id, chave, app_uuid, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, is_json, valor)
VALUES (null, 'nfeconfigs_padrao', '9121ea11-dc5d-4a22-9596-187f5452f95a', now(), now(), 1, 1, 1, 1, @lastInsertId);


commit;