START TRANSACTION;

SET FOREIGN_KEY_CHECKS=0;


INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_ESTOQUE','ROLE_ESTOQUE',1,1,1);
INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_ESTOQUE_ADMIN','ROLE_ESTOQUE_ADMIN',1,1,1);


INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_PEDIDOCOMPRA','ROLE_PEDIDOCOMPRA',1,1,1);
INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_PEDIDOCOMPRA_ADMIN','ROLE_PEDIDOCOMPRA_ADMIN',1,1,1);


INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_ROMANEIO','ROLE_ROMANEIO',1,1,1);
INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_ROMANEIO_ADMIN','ROLE_ROMANEIO_ADMIN',1,1,1);


INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_ESTOQUE_ECOMMERCE','ROLE_ESTOQUE_ECOMMERCE',1,1,1);


COMMIT;
