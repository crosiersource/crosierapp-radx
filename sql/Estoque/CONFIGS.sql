START TRANSACTION;

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM cfg_app_config WHERE app_uuid = '440e429c-b711-4411-87ed-d95f7281cd43' AND chave = 'produto_form.ordem_abas';

INSERT INTO cfg_app_config
    (id,inserted,updated,estabelecimento_id,user_inserted_id,user_updated_id,app_uuid,chave,valor)
    VALUES (null, now(), now(), 1, 1, 1, '440e429c-b711-4411-87ed-d95f7281cd43', 'produto_form.ordem_abas', 'Produto,Descritivos,Complementos,Fotos,Preços,ERP,Fiscal');


COMMIT;
