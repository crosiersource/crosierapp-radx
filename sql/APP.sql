SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM cfg_app WHERE uuid = '9121ea11-dc5d-4a22-9596-187f5452f95a';

INSERT INTO `cfg_app` (`id`, `uuid`, `inserted`, `updated`, `nome`, `obs`, `estabelecimento_id`, `user_inserted_id`,
                       `user_updated_id`)
VALUES (null, '9121ea11-dc5d-4a22-9596-187f5452f95a', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 'crosierapp-radx', 'Módulos "raíz" do Crosier: CRM, RH, Financeiro, Vendas, Estoque, Fiscal', 1,
        1, 1);

COMMIT;
