START TRANSACTION;

SET FOREIGN_KEY_CHECKS=0;


INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_FINAN','ROLE_FINAN',1,1,1);
INSERT INTO sec_role(id,inserted,updated,role,descricao,estabelecimento_id,user_inserted_id,user_updated_id) VALUES(null,now(),now(),'ROLE_FINAN_ADMIN','ROLE_FINAN_ADMIN',1,1,1);


COMMIT;
