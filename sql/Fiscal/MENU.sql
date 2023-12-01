START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;

SET @ordem = 1;


-- Entrada no menu do crosier-core
DELETE
FROM cfg_entmenu
WHERE uuid = '0b9a88b4-8da1-11ec-8e44-53d13b8e20ad';
INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('0b9a88b4-8da1-11ec-8e44-53d13b8e20ad', 'Fiscal', 'fas fa-university', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fis/notaFiscal/recebidas/list', 'ROLE_FISCAL', '71d1456b-3a9f-4589-8f71-42bbf6c91a3e', 100, 'background-color: darkslateblue;
', now(), now(), 1, 1, 1);

-- Entrada do meu PAI (NÃO É EXIBIDO)
DELETE
FROM cfg_entmenu
WHERE uuid = '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26';
INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', 'Fiscal (Menu Raíz)', '', 'PAI', '9121ea11-dc5d-4a22-9596-187f5452f95a', '', 'ROLE_FISCAL', null, 0, null,
        now(), now(), 1, 1, 1);


-- DistDFes
DELETE
FROM cfg_entmenu
WHERE uuid = '31e8233c-8da1-11ec-a816-f38c985c8bf4';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('31e8233c-8da1-11ec-a816-f38c985c8bf4', 'DistDFes', 'fas fa-cloud-download-alt', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fis/notaFiscal/distdfe/list', 'ROLE_FISCAL_ADMIN', '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', @ordem, null, now(), now(), 1, 1, 1);


-- Recebidas
DELETE
FROM cfg_entmenu
WHERE uuid = '68d3a006-8da1-11ec-81de-7f3914e76331';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('68d3a006-8da1-11ec-81de-7f3914e76331', 'Recebidas', 'fas fa-sign-in-alt', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fis/notaFiscal/recebidas/list', 'ROLE_FISCAL', '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', @ordem, null, now(), now(), 1, 1, 1);


-- Emitidas
DELETE
FROM cfg_entmenu
WHERE uuid = '81d6f83c-8da1-11ec-81c9-b742a48d271d';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('81d6f83c-8da1-11ec-81c9-b742a48d271d', 'Emitidas', 'fas fa-sign-out-alt', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fis/notaFiscal/emitidas/list', 'ROLE_FISCAL', '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', @ordem, null, now(), now(), 1, 1, 1);


-- Seleção Contribuinte
DELETE
FROM cfg_entmenu
WHERE uuid = '950ae33c-8da1-11ec-aa97-ffb187ec13be';

SET @ordem = @ordem + 1;

INSERT INTO cfg_entmenu(uuid, label, icon, tipo, app_uuid, url, roles, pai_uuid, ordem, css_style, inserted, updated, estabelecimento_id, user_inserted_id,
                        user_updated_id)
VALUES ('950ae33c-8da1-11ec-aa97-ffb187ec13be', 'Seleção Contribuinte', 'fas fa-ellipsis-v', 'ENT', '9121ea11-dc5d-4a22-9596-187f5452f95a',
        '/v/fis/selecaoContribuinte', 'ROLE_FISCAL', '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', @ordem, null, now(), now(), 1, 1, 1);



-- 
-- cfg_entmenu_locator
--
DELETE
FROM cfg_entmenu_locator
WHERE menu_uuid = '24dc7e0e-8da1-11ec-805c-ab7a3ec85d26';

INSERT INTO cfg_entmenu_locator(menu_uuid, url_regexp, quem, inserted, updated, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES ('24dc7e0e-8da1-11ec-805c-ab7a3ec85d26', '^https://radx\.(.)*/v/fis/(.)*', '*', now(), now(), 1, 1, 1);
COMMIT;
