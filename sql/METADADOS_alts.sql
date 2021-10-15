alter table fin_carteira
  add `caixa_status`         enum ('','ABERTO','FECHADO'),
  add `caixa_responsavel_id` bigint(20),
  add KEY `K_fin_carteira_caixa_responsavel` (`caixa_responsavel_id`),
  add CONSTRAINT `FK_fin_carteira_caixa_responsavel` FOREIGN KEY (`caixa_responsavel_id`) REFERENCES `sec_user` (`id`);