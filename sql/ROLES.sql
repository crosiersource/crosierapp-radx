START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;


INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_CRM', 'ROLE_CRM', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_CRM_ADMIN', 'ROLE_CRM_ADMIN', 1, 1, 1);


INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_CLIENTES', 'Permissão para alterar cadastro de clientes.', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_CLIENTES_ADMIN', 'Permissão para deletar registros de clientes.', 1, 1, 1);

INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FORNECEDORES', 'Permissão para alterar cadastro de fornecedores.', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FORNECEDORES_ADMIN', 'Permissão para deletar registros de fornecedores.', 1, 1, 1);


INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ESTOQUE', 'ROLE_ESTOQUE', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ESTOQUE_ADMIN', 'ROLE_ESTOQUE_ADMIN', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_PEDIDOCOMPRA', 'ROLE_PEDIDOCOMPRA', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_PEDIDOCOMPRA_ADMIN', 'ROLE_PEDIDOCOMPRA_ADMIN', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ROMANEIO', 'ROLE_ROMANEIO', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ROMANEIO_ADMIN', 'ROLE_ROMANEIO_ADMIN', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ESTOQUE_ECOMMERCE', 'ROLE_ESTOQUE_ECOMMERCE', 1, 1, 1);


INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FINAN', 'ROLE_FINAN', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FINAN_ADMIN', 'ROLE_FINAN_ADMIN', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FINAN_CAIXAOPERACAO', 'Abertura/Fechamento de Caixas', 1, 1, 1);


INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FISCAL', 'ROLE_FISCAL', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_FISCAL_ADMIN', 'ROLE_FISCAL_ADMIN', 1, 1, 1);



INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_RH', 'ROLE_RH', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_RH_ADMIN', 'ROLE_RH_ADMIN', 1, 1, 1);



INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_VENDAS', 'ROLE_VENDAS', 1, 1, 1);
INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_VENDAS_ADMIN', 'ROLE_VENDAS_ADMIN', 1, 1, 1);



INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ECOMM', 'ROLE_ECOMM', 1, 1, 1);

INSERT INTO sec_role(id, inserted, updated, role, descricao, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, now(), now(), 'ROLE_ECOMM_ADMIN', 'ROLE_ECOMM_ADMIN', 1, 1, 1);



COMMIT;
