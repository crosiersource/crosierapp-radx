START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

SET @ordem = 1;


-- Entrada no menu do crosier-core
DELETE
FROM cfg_entmenu
WHERE uuid = '189dd86e-f806-4f96-8d5e-2a3e388c6976';
INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('189dd86e-f806-4f96-8d5e-2a3e388c6976', 'Financeiro', 'fas fa-dollar-sign', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/fin', 'ROLE_FINAN',
        '71d1456b-3a9f-4589-8f71-42bbf6c91a3e', 100, 'background-color: darkslateblue;', now(), now(), 1, 1, 1);

-- Entrada do meu PAI (NÃO É EXIBIDO)
DELETE
FROM cfg_entmenu
WHERE uuid = 'b7a5f134-ea80-40e4-822e-e04cdac70258';
INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('b7a5f134-ea80-40e4-822e-e04cdac70258', 'Financeiro (Menu Raíz)', '', 'PAI', '9121ea11-dc5d-4a22-9596-187f5452f95a', '', 'ROLE_FINAN', null, 0, null,
        now(), now(), 1, 1, 1);


--
-- Menu "Lançamentos"
--
DELETE
FROM cfg_entmenu
WHERE uuid = '3984a4f5-cd55-4525-87b9-01212fb1952c';
INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('3984a4f5-cd55-4525-87b9-01212fb1952c', 'Lançamentos', 'fas fa-sign-out-alt', 'DROPDOWN', '9121ea11-dc5d-4a22-9596-187f5452f95a', null, 'ROLE_FINAN',
        'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);

SET @ordem = @ordem + 1;

-- apaga todos os filhos do "Lançamentos"
DELETE
FROM cfg_entmenu
WHERE pai_uuid = '3984a4f5-cd55-4525-87b9-01212fb1952c';



INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '58e75b9e-9bc8-11ec-afd2-c304c19f57c3', 'Lancto Rápido', 'fas fa-bolt', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem, NULL, now(), now(),
        1, 1, 1, '/v/fin/movimentacao/rapida', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');



SET @ordem = @ordem + 1;


INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '52b3799e-f70e-4adf-8052-88f41ec1834e', 'Conta a Pagar/Receber', 'fas fa-file-invoice-dollar', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c',
        @ordem, NULL, now(), now(), 1, 1, 1, '/v/fin/movimentacao/aPagarReceber/form', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');


SET @ordem = @ordem + 1;

DELETE
FROM cfg_entmenu
WHERE pai_uuid = '90f8f184-82c1-11ec-89e9-ff3a06779272';

INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '90f8f184-82c1-11ec-89e9-ff3a06779272', 'Parcelamento', 'fas fa-coins', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem, NULL, now(), now(),
        1, 1, 1, '/v/fin/movimentacao/aPagarReceber/formParcelamento', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');


SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, 'fadc7b3e-f2f3-42cd-9ebc-0652cdade206', 'Transf entre Carteiras', 'fas fa-exchange-alt', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem,
        NULL, now(), now(), 1, 1, 1, '/v/fin/movimentacao/transfEntreCarteiras/form', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '88281506-754e-4d54-bd68-299593918e03', 'Para Grupo', 'far fa-object-group', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem, NULL, now(),
        now(), 1, 1, 1, '/v/fin/movimentacao/grupo/form', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '1e5d6a55-a507-401a-aa02-91602081bae1', 'Estorno', 'fas fa-eraser', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem, NULL, now(), now(), 1,
        1, 1, '/v/fin/movimentacao/estorno/form', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu (id, uuid, label, icon, tipo, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id, url,
                         app_uuid, roles)
VALUES (NULL, '0558f998-b936-415b-a1b9-5f1ed6b54395', 'Recorrente', 'fas fa-redo-alt', 'ENT', '3984a4f5-cd55-4525-87b9-01212fb1952c', @ordem, NULL, now(),
        now(), 1, 1, 1, '/v/fin/movimentacao/recorrente/form', '9121ea11-dc5d-4a22-9596-187f5452f95a', 'ROLE_FINAN');


-- Faturas
DELETE
FROM cfg_entmenu
WHERE uuid = '68d6193c-2df4-11ed-b9d1-57e3b2d94ab2';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('68d6193c-2df4-11ed-b9d1-57e3b2d94ab2', 'Faturas', 'fas fa-file-invoice', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/fatura/list',
        'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Extrato de Contas a Pagar/Receber
DELETE
FROM cfg_entmenu
WHERE uuid = 'e8d385b5-5fe2-41f0-b8e2-f1d1ad7bd097';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('e8d385b5-5fe2-41f0-b8e2-f1d1ad7bd097', 'Contas a Pagar', 'fas fa-file-invoice-dollar', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/movimentacao/aPagar/list', 'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);

-- Pesquisa de Movimentações
DELETE
FROM cfg_entmenu
WHERE uuid = '61d20df0-d26b-4e29-a304-9c56121d7fd0';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('61d20df0-d26b-4e29-a304-9c56121d7fd0', 'Pesquisar', 'fas fa-search-dollar', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/movimentacao/list',
        'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Extrato de Movimentações
DELETE
FROM cfg_entmenu
WHERE uuid = '0bc57722-bd1c-44fe-911e-2657d49c2965';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('0bc57722-bd1c-44fe-911e-2657d49c2965', 'Extrato', 'fas fa-list-ol', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/movimentacao/extrato',
        'ROLE_FINAN',
        'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Importação de Movimentações
DELETE
FROM cfg_entmenu
WHERE uuid = 'fdabfbb2-197f-4cd4-87dd-f3ea02ef7a06';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('fdabfbb2-197f-4cd4-87dd-f3ea02ef7a06', 'Importação', 'fas fa-file-import', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/movimentacao/importador', 'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Tela para Caixa
DELETE
FROM cfg_entmenu
WHERE uuid = '9148c62e-09e7-11ee-8cb3-133f08722d14';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('9148c62e-09e7-11ee-8cb3-133f08722d14', 'Caixa', 'fas fa-hand-holding-usd', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/caixa', 'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Recorrentes
DELETE
FROM cfg_entmenu
WHERE uuid = '31b009a5-758b-4ee4-b47c-f42df678868d';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('31b009a5-758b-4ee4-b47c-f42df678868d', 'Recorrentes', 'fas fa-undo', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/movimentacao/recorrente/list', 'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Agrupadas
DELETE
FROM cfg_entmenu
WHERE uuid = 'd75b1eb9-3f15-4eb9-a87d-d1ebb33172ff';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('d75b1eb9-3f15-4eb9-a87d-d1ebb33172ff', 'Agrupadas', 'fas fa-layer-group', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/movimentacao/grupo/list', 'ROLE_FINAN', 'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, null, now(), now(), 1, 1, 1);


-- Cadastros
DELETE
FROM cfg_entmenu
WHERE uuid = 'a2f6c378-71c8-426b-8974-804187e8776a';

SET @ordem = @ordem + 1000;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('a2f6c378-71c8-426b-8974-804187e8776a', 'Cadastros', 'fas fa-clipboard-list', 'DROPDOWN', '9121ea11-dc5d-4a22-9596-187f5452f95a', '',
        'ROLE_FINAN_ADMIN',
        'b7a5f134-ea80-40e4-822e-e04cdac70258', @ordem, 'background-color: forestgreen', now(), now(), 1, 1, 1);


-- Modos de Movimentação
DELETE
FROM cfg_entmenu
WHERE uuid = 'b1dcae54-08ce-4b25-b38b-a8f7d56218e8';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('b1dcae54-08ce-4b25-b38b-a8f7d56218e8', 'Modos', 'fas fa-sitemap', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/modo/list',
        'ROLE_FINAN_MASTER', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Categorias
DELETE
FROM cfg_entmenu
WHERE uuid = 'b41a78d8-14ec-4ba4-8ec6-453240719321';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('b41a78d8-14ec-4ba4-8ec6-453240719321', 'Categorias', 'fas fa-stream', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/categoria/form',
        'ROLE_FINAN_MASTER',
        'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Bandeiras de Cartão
DELETE
FROM cfg_entmenu
WHERE uuid = 'ced83376-84a7-4c89-a185-e252a9ae3936';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('ced83376-84a7-4c89-a185-e252a9ae3936', 'Bandeiras de Cartão', 'fab fa-cc-visa', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/bandeiraCartao/list', 'ROLE_FINAN_MASTER', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Bancos
DELETE
FROM cfg_entmenu
WHERE uuid = '5cf74ef9-4d4d-4283-98d0-120d4617a7ea';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('5cf74ef9-4d4d-4283-98d0-120d4617a7ea', 'Bancos', 'fas fa-university', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/banco/list',
        'ROLE_FINAN_MASTER',
        'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);

-- Carteiras
DELETE
FROM cfg_entmenu
WHERE uuid = '8b1a74a8-fb70-4a05-a1e1-150e2561ce70';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('8b1a74a8-fb70-4a05-a1e1-150e2561ce70', 'Carteiras', 'fas fa-piggy-bank', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/carteira/list',
        'ROLE_FINAN_ADMIN',
        'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Centros de Custos
DELETE
FROM cfg_entmenu
WHERE uuid = '6f68649c-516c-4b1f-a735-040d9f7125a9';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('6f68649c-516c-4b1f-a735-040d9f7125a9', 'Centros de Custos', 'fas fa-location-arrow', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/centroCusto/list', 'ROLE_FINAN_ADMIN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Grupos
DELETE
FROM cfg_entmenu
WHERE uuid = 'db8eea41-44c2-49dc-b850-fb0b0a8631bd';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('db8eea41-44c2-49dc-b850-fb0b0a8631bd', 'Grupos', 'fas fa-object-group', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/grupo/list',
        'ROLE_FINAN_ADMIN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Operadoras de Cartão
DELETE
FROM cfg_entmenu
WHERE uuid = '5fe09111-9fce-4eaf-b65a-060a09d2f8e7';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('5fe09111-9fce-4eaf-b65a-060a09d2f8e7', 'Operadoras de Cartão', 'fas fa-building', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/operadoraCartao/list', 'ROLE_FINAN_ADMIN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Regras de Importação
DELETE
FROM cfg_entmenu
WHERE uuid = 'cb7e4d97-59fe-4666-880f-6f8eecd806f3';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('cb7e4d97-59fe-4666-880f-6f8eecd806f3', 'Regras de Importação', 'fas fa-file-excel', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/regraImportacaoLinha/list', 'ROLE_FINAN_ADMIN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Registros para Conferências
DELETE
FROM cfg_entmenu
WHERE uuid = 'e2ffd6b9-81d6-4ff1-84ab-ada9670484ce';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('e2ffd6b9-81d6-4ff1-84ab-ada9670484ce', 'Registros para Conferências', 'fas fa-check-circle', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fin/registroConferencia/list', 'ROLE_FINAN_ADMIN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Fornecedores
DELETE
FROM cfg_entmenu
WHERE uuid = '49ca2892-7a0f-11ec-8038-5f519df769f7';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('49ca2892-7a0f-11ec-8038-5f519df769f7', 'Fornecedores', 'fas fa-truck-loading', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/fornecedor/list',
        'ROLE_FINAN', 'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);


-- Clientes
DELETE
FROM cfg_entmenu
WHERE uuid = 'dfb4137a-7d4f-11ec-8acd-3faa558a49a0';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('dfb4137a-7d4f-11ec-8acd-3faa558a49a0', 'Clientes', 'fas fa-user-circle', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/v/fin/cliente/list',
        'ROLE_FINAN',
        'a2f6c378-71c8-426b-8974-804187e8776a', @ordem, null, now(), now(), 1, 1, 1);



-- 
-- cfg_entmenu_locator
--
DELETE
FROM cfg_entmenu_locator
WHERE menu_uuid = 'b7a5f134-ea80-40e4-822e-e04cdac70258';

INSERT INTO cfg_entmenu_locator(menu_uuid, url_regexp, quem, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('b7a5f134-ea80-40e4-822e-e04cdac70258', '^https://radx\.(.)*/fin/(.)*', 'r:ROLE_FINAN', now(), now(), 1, 1, 1);
COMMIT;
