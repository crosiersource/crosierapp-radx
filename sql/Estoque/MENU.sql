START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

--
--
-- Entrada no menu do crosier-core
DELETE
FROM cfg_entmenu
WHERE uuid = '4e68aeba-0114-11ec-8a19-f37f6b86d6e2';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('4e68aeba-0114-11ec-8a19-f37f6b86d6e2', 'Estoque', 'fas fa-box-open', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est', '',
        '71d1456b-3a9f-4589-8f71-42bbf6c91a3e', 100, 'background-color: darkslateblue;', now(), now(), 1, 1, 1);


--
--
-- Entrada do meu PAI (NÃO É EXIBIDO)
DELETE
FROM cfg_entmenu
WHERE uuid = '75f3e22e-0114-11ec-b1db-47f2e75c14df';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('75f3e22e-0114-11ec-b1db-47f2e75c14df', 'Estoque (Menu Raíz)', '', 'PAI',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '', '', null, 0, null, now(), now(), 1, 1, 1);



-- Pedidos de Compra
DELETE
FROM cfg_entmenu
WHERE uuid = '84b8d57a-0115-11ec-bffe-2f0c538b334d';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('84b8d57a-0115-11ec-bffe-2f0c538b334d', 'Pedidos de Compra', 'far fa-file-powerpoint', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/pedidoCompra/list', '', '75f3e22e-0114-11ec-b1db-47f2e75c14df', 8,
        null, now(), now(), 1, 1, 1);

-- Entradas
DELETE
FROM cfg_entmenu
WHERE uuid = '0c7248de-0116-11ec-8afb-afc900220402';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('0c7248de-0116-11ec-8afb-afc900220402', 'Entradas', 'fas fa-sign-in-alt', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/entrada/list', '', '75f3e22e-0114-11ec-b1db-47f2e75c14df', 8,
        null, now(), now(), 1, 1, 1);


-- Cadastros
DELETE
FROM cfg_entmenu
WHERE uuid = 'a1c75606-0114-11ec-8e35-4bb6e339954f';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('a1c75606-0114-11ec-8e35-4bb6e339954f', 'Cadastros', 'fas fa-clipboard-list', 'DROPDOWN',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '', '', '75f3e22e-0114-11ec-b1db-47f2e75c14df', 99,
        'background-color: #0f2944;', now(), now(), 1, 1, 1);


-- Deptos/Grupos/Subgrupos
DELETE
FROM cfg_entmenu
WHERE uuid = 'ab546420-0114-11ec-9233-cb99970b5ca9';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('ab546420-0114-11ec-9233-cb99970b5ca9', 'Deptos', 'fas fa-boxes', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/deptoGrupoSubgrupo/form', '', 'a1c75606-0114-11ec-8e35-4bb6e339954f', 1, null,
        now(), now(), 1, 1, 1);


-- Unidades
DELETE
FROM cfg_entmenu
WHERE uuid = '58a1fa34-0115-11ec-9fba-07650e3b32cf';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('58a1fa34-0115-11ec-9fba-07650e3b32cf', 'Unidades', 'fas fa-ellipsis-v', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/unidade/list', '', 'a1c75606-0114-11ec-8e35-4bb6e339954f', 1, null,
        now(), now(), 1, 1, 1);


-- Produtos
DELETE
FROM cfg_entmenu
WHERE uuid = 'dd0f10be-0114-11ec-91a7-c362839db728';

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('dd0f10be-0114-11ec-91a7-c362839db728', 'Produtos', 'far fa-stop-circle', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/produto/list', '', 'a1c75606-0114-11ec-8e35-4bb6e339954f', 2, null,
        now(), now(), 1, 1, 1);

-- Fornecedores
DELETE
FROM cfg_entmenu
WHERE uuid = '03478c70-0115-11ec-bddf-8f2c061802e5';


INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated,
                        estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('03478c70-0115-11ec-bddf-8f2c061802e5', 'Fornecedores', 'fas fa-truck-loading', 'ENT',
        '9121ea11-dc5d-4a22-9596-187f5452f95a', '/est/fornecedor/list', '', 'a1c75606-0114-11ec-8e35-4bb6e339954f', 3,
        null, now(), now(), 1, 1, 1);



--
--
--
--
--
--
--
-- cfg_entmenu_locator
--

DELETE
FROM cfg_entmenu_locator
WHERE menu_uuid = '75f3e22e-0114-11ec-b1db-47f2e75c14df';

INSERT INTO cfg_entmenu_locator(menu_uuid, url_regexp, quem, inserted, updated, estabelecimento_id, user_inserted_id,
                                user_updated_id)
VALUES ('75f3e22e-0114-11ec-b1db-47f2e75c14df', '^https://radx\.(.)*/est/(.)*', '*', now(), now(), 1, 1, 1);



COMMIT;