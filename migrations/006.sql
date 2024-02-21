ALTER TABLE fin_carteira
  ADD `caixa_status` enum ('','ABERTO','FECHADO') AFTER caixa;

ALTER TABLE fin_carteira
  ADD `caixa_responsavel_id` bigint(20) AFTER `caixa_status`;

ALTER TABLE fin_carteira
  ADD KEY `K_fin_carteira_caixa_responsavel` (`caixa_responsavel_id`);

ALTER TABLE fin_carteira
  ADD CONSTRAINT `FK_fin_carteira_caixa_responsavel` FOREIGN KEY (`caixa_responsavel_id`) REFERENCES `sec_user` (`id`);

ALTER TABLE fin_carteira
  ADD `caixa_dt_ultima_operacao` datetime AFTER `caixa_responsavel_id`;

