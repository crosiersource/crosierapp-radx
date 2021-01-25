START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;


insert into est_lista_preco
values (1, 'VAREJO', '1900-01-01', null, now(), now(), 0, 1, 1, 1);


INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 1, 0, 15, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.981, 16, 30, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.969, 31, 45, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.958, 46, 60, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.946, 61, 75, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.935, 76, 90, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.924, 91, 105, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.914, 106, 120, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.904, 121, 135, now(), now(), 0, 1, 1, 1);
INSERT INTO est_depreciacao_preco(id, porcentagem, prazo_ini, prazo_fim, inserted, updated, version, estabelecimento_id, user_inserted_id, user_updated_id)
VALUES (null, 0.894, 136, 1999999999, now(), now(), 0, 1, 1, 1);



TRUNCATE TABLE fin_centrocusto;

INSERT INTO `fin_centrocusto` (`id`, `codigo`, `descricao`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'GLOBAL', '2019-03-18 16:48:46', '2019-03-18 16:48:46', NULL, 1, 1, 1);


TRUNCATE TABLE fin_carteira;

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (null, 99, 'INDEFINIDA', null, null, null, true, true, false, true, '1900-01-01', 0, null, '2019-03-18 16:48:46', '2019-03-18 16:48:46', 0, 1, 1, 1, true);
INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (null, 1, 'GERAL', null, null, null, true, false, false, true, '1900-01-01', 0, null, '2019-03-18 16:48:46', '2019-03-18 16:48:46', 0, 1, 1, 1, true);

INSERT INTO `fin_carteira` (id, codigo, descricao, banco_id, agencia, conta, abertas, caixa, cheque, concreta, dt_consolidado, limite, operadora_cartao_id, inserted, updated, version, estabelecimento_id,
                            user_inserted_id, user_updated_id, atual)
VALUES (null, 50, 'MOVIMENTAÇÕES AGRUPADAS', null, null, null, true, false, false, true, '1900-01-01', 0, null, '2019-03-18 16:48:46', '2019-03-18 16:48:46', 0, 1, 1, 1, true);



TRUNCATE TABLE fin_tipo_lancto;

REPLACE INTO `fin_tipo_lancto` (`id`, `codigo`, `descricao`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (20, 20, 'MOVIMENTAÇÃO NORMAL', now(), now(), 0, 1, 1, 1),
       (60, 60, 'TRANSFERÊNCIA ENTRE CARTEIRAS', now(), now(), 0, 1, 1, 1),
       (61, 61, 'TRANSFERÊNCIA DE ENTRADA DE CAIXA', now(), now(), 0, 1, 1, 1),
       (62, 62, 'FATURA TRANSACIONAL', now(), now(), 0, 1, 1, 1);

TRUNCATE TABLE fin_categoria;

REPLACE INTO `fin_categoria` (`id`, `codigo`, `descricao`, `pai_id`, `centro_custo_dif`, `codigo_super`, `descricao_padrao_moviment`, `totalizavel`, `descricao_alternativa`, `roles_acess`, `codigo_ord`,
                              `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'ENTRADAS', NULL, 0, 1, NULL, 0, NULL, NULL, 100000000000, '2015-02-11 18:16:34', '2015-02-11 18:16:36', 0, 1, 1, 1),
       (2, 2, 'SAÍDAS', NULL, 0, 2, NULL, 0, NULL, NULL, 200000000000, '2015-02-11 18:16:47', '2015-02-11 18:16:49', 0, 1, 1, 1),
       (101, 101, 'RECEB. VENDAS INTERNAS', 1, 0, 1, '', 0, '', '', 101000000000, '2015-02-11 18:19:35', '2018-12-28 15:25:36', 3, 1, 1, 1),
       (102, 102, 'RECEB. VENDAS EXTERNAS', 1, 0, 1, NULL, 0, NULL, NULL, 102000000000, '2015-02-11 18:19:40', '2015-02-12 14:58:04', 1, 1, 1, 1),
       (103, 103, 'ENTRADA - EMPRÉSTIMO', 1, 0, 1, NULL, 0, NULL, NULL, 103000000000, '2015-03-06 15:31:56', '2015-03-06 15:31:56', 0, 1, 1, 1),
       (104, 104, 'VENDAS LICITAÇÕES', 1, 0, 1, NULL, 0, NULL, NULL, 104000000000, '2015-02-11 18:20:26', '2015-03-06 15:31:41', 3, 1, 1, 1),
       (150, 150, 'OUTROS RECEBIMENTOS', 1, 0, 1, NULL, 0, NULL, NULL, 150000000000, '2015-02-11 18:19:48', '2015-02-11 18:20:20', 1, 1, 1, 1),
       (151, 151, 'ENTRADA - AJUSTE DE CAIXA', 1, 0, 1, NULL, 0, NULL, NULL, 151000000000, '2015-03-02 09:49:24', '2015-03-02 09:49:24', 0, 1, 1, 1),
       (160, 160, 'RECEBIMENTOS PARA TERCEIROS', 1, 0, 1, NULL, 0, NULL, NULL, 160000000000, '2015-05-09 15:10:18', '2015-05-09 15:10:18', 0, 1, 1, 1),
       (170, 170, 'OUTRAS ENTRADAS', 1, 0, 1, '', 0, '', '', 170000000000, '2015-12-23 11:56:37', '2015-12-23 11:56:37', 0, 1, 1, 1),
       (179, 179, 'REAPRESENTAÇÃO DE CHEQUE (ENTRANDO)', 1, 0, 1, '', 0, '', '', 179000000000, '2016-03-21 17:18:02', '2016-03-21 17:18:02', 0, 1, 1, 1),
       (180, 180, 'ENTRADAS PESSOAIS', 1, 0, 1, '', 0, '', null, 180000000000, '2015-04-30 16:40:36', '2015-10-20 12:18:28', 2, 1, 1, 1),
       (188, 188, 'MOVIMENTAÇÃO ESTORNADA (ENTRANDO)', 1, 0, 1, '%s', 0, NULL, NULL, 188000000000, '2017-08-11 11:17:06', '2017-08-11 11:17:06', 0, 1, 1, 1),
       (189, 189, 'ESTORNO DE SAÍDA DIRECIONADA', 1, 0, 1, '', 0, '', '', 189000000000, '2015-08-06 17:54:57', '2015-12-07 18:31:38', 2, 1, 1, 1),
       (191, 191, 'ENTRADA EM FATURA', 1, 0, 1, '', 0, '', '', 191000000000, '2020-12-04 14:39:34', '2020-12-04 14:39:34', 0, 1, 1, 1),
       (192, 192, 'CRÉDITO EM FATURA', 1, 0, 1, '', 0, '', '', 192000000000, '2020-12-04 14:39:34', '2020-12-04 14:39:34', 0, 1, 1, 1),
       (195, 195, 'ENTRADAS - A CONFERIR', 1, 0, 1, '', 0, '', NULL, 195000000000, '2017-10-05 09:21:22', '2017-10-05 09:21:22', 0, 1, 1, 1),
       (199, 199, 'TRANSFERÊNCIA DE CONTA', 1, 0, 1, '', 0, '', '', 199000000000, '2015-02-11 18:19:56', '2018-10-11 17:56:52', 0, 1, 1, 1),
       (201, 201, 'CUSTOS FIXOS', 2, 0, 2, NULL, 0, NULL, NULL, 201000000000, '2015-02-11 18:20:50', '2015-02-11 18:20:50', 0, 1, 1, 1),
       (202, 202, 'CUSTOS VARIÁVEIS', 2, 0, 2, NULL, 0, NULL, NULL, 202000000000, '2015-02-11 18:21:06', '2015-02-11 18:21:06', 0, 1, 1, 1),
       (203, 203, 'EMPRÉSTIMOS', 2, 0, 2, NULL, 0, NULL, NULL, 203000000000, '2015-02-24 08:44:03', '2015-03-02 09:52:08', 1, 1, 1, 1),
       (204, 204, 'INVESTIMENTOS', 2, 0, 2, '', 0, '', '', 204000000000, '2015-12-23 11:27:45', '2015-12-23 11:27:45', 0, 1, 1, 1),
       (251, 251, 'SAÍDA - AJUSTE DE CAIXA', 2, 0, 2, NULL, 0, NULL, NULL, 251000000000, '2015-03-02 09:52:35', '2015-03-02 09:52:35', 0, 1, 1, 1),
       (252, 252, 'AJUSTE COBRANÇA FATURA', 2, 0, 2, '', 0, '', '', 252000000000, '2016-02-06 15:29:19', '2016-02-06 15:29:19', 0, 1, 1, 1),
       (260, 260, 'PAGTO TERCEIROS', 2, 0, 2, NULL, 0, NULL, NULL, 260000000000, '2015-03-13 12:05:22', '2015-03-13 12:05:22', 0, 1, 1, 1),
       (270, 270, 'MOVIMENTAÇÃO ESTORNADA (SAINDO)', 2, 0, 2, NULL, 0, NULL, NULL, 270000000000, '2015-07-18 10:33:52', '2015-07-18 10:33:52', 0, 1, 1, 1),
       (279, 279, 'REAPRESENTAÇÃO DE CHEQUE (SAINDO)', 2, 0, 2, NULL, 0, NULL, NULL, 279000000000, '2017-09-21 12:09:25', '2017-09-21 12:09:25', 0, 1, 1, 1),
       (280, 280, 'SAÍDAS PESSOAIS', 2, 0, 2, '', 0, '', 'ROLE_FINANCEIRO_PROPRIET', 280000000000, '2015-04-30 16:40:45', '2015-10-20 12:18:42', 2, 1, 1, 1),
       (290, 290, 'PAGTO. GRUPO MOVIMENT.', 2, 0, 2, NULL, 0, NULL, NULL, 290000000000, '2015-02-12 16:31:02', '2015-03-02 09:52:18', 1, 1, 1, 1),
       (291, 291, 'SAÍDA EM FATURA', 2, 0, 2, '', 0, '', '', 291000000000, '2020-12-04 14:39:45', '2020-12-04 14:39:45', 0, 1, 1, 1),
       (292, 292, 'QUITAMENTO DE FATURA', 2, 0, 2, '', 0, '', '', 292000000000, '2020-12-04 14:39:45', '2020-12-04 14:39:45', 0, 1, 1, 1),
       (295, 295, 'SAIDAS - A CONFERIR', 1, 0, 2, '', 0, '', NULL, 295000000000, '2017-10-05 09:21:39', '2017-10-05 09:21:39', 0, 1, 1, 1),
       (299, 299, 'TRANSFERÊNCIA PARA CONTA', 2, 0, 2, '', 0, '', '', 299000000000, '2015-02-11 18:20:02', '2019-01-02 11:37:58', 0, 1, 1, 1),
       (103001, 103001, 'ENTRADA GIRO RÁPIDO', 45, 0, 1, NULL, 0, NULL, NULL, 103001000000, '2015-06-10 09:17:17', '2015-06-10 09:17:17', 0, 1, 1, 1),
       (103002, 103002, 'ENTRADA CAPITAL DE GIRO', 45, 0, 1, NULL, 0, NULL, NULL, 103002000000, '2015-06-10 09:17:50', '2015-08-17 22:20:51', 1, 1, 1, 1),
       (201001, 201001, 'LUZ', 9, 0, 2, NULL, 0, NULL, NULL, 201001000000, '2015-02-11 18:21:48', '2015-02-11 18:21:48', 0, 1, 1, 1),
       (201002, 201002, 'TELEFONE FIXO / INTERNET', 9, 0, 2, NULL, 0, NULL, NULL, 201002000000, '2015-02-11 18:21:53', '2015-03-18 19:22:34', 1, 1, 1, 1),
       (201003, 201003, 'TELEFONE CELULAR', 9, 0, 2, NULL, 0, NULL, NULL, 201003000000, '2015-02-11 18:21:59', '2015-02-11 18:21:59', 0, 1, 1, 1),
       (201004, 201004, 'ÁGUA', 9, 0, 2, NULL, 0, NULL, NULL, 201004000000, '2015-02-11 18:22:07', '2015-02-11 18:22:07', 0, 1, 1, 1),
       (201005, 201005, 'SEGUROS', 9, 0, 2, NULL, 0, NULL, NULL, 201005000000, '2015-02-11 18:22:14', '2015-02-11 18:22:14', 0, 1, 1, 1),
       (201006, 201006, 'MATERIAIS DE EXPEDIENTE', 9, 0, 2, NULL, 0, NULL, NULL, 201006000000, '2015-02-11 18:22:20', '2015-02-11 18:22:20', 0, 1, 1, 1),
       (201007, 201007, 'COMBUSTÍVEIS E DESP. VIAGENS', 9, 0, 2, NULL, 0, NULL, NULL, 201007000000, '2015-02-11 18:22:32', '2015-02-11 18:22:32', 0, 1, 1, 1),
       (201008, 201008, 'MARKETING/PUBLICIDADE', 9, 0, 2, NULL, 0, NULL, NULL, 201008000000, '2015-02-11 18:22:41', '2015-02-11 18:22:41', 0, 1, 1, 1),
       (201009, 201009, 'FRETES', 9, 0, 2, 'PAGTO DE FRETE', 0, NULL, NULL, 201009000000, '2015-02-11 18:22:50', '2015-09-09 18:24:33', 1, 1, 1, 1),
       (201010, 201010, 'EMBALAGENS', 9, 0, 2, NULL, 0, NULL, NULL, 201010000000, '2015-02-11 18:22:55', '2015-02-11 18:22:55', 0, 1, 1, 1),
       (201011, 201011, 'TAXAS, ENCARGOS, DESP. BANCÁRIAS', 9, 0, 2, '', 0, '', '', 201011000000, '2015-02-11 18:23:06', '2018-10-11 17:56:50', 1, 1, 1, 1),
       (201012, 201012, 'IRFF', 9, 0, 2, NULL, 0, NULL, NULL, 201012000000, '2015-02-11 18:23:18', '2015-02-11 18:23:23', 1, 1, 1, 1),
       (201013, 201013, 'TAXAS E IMPOSTOS DIVERSOS', 9, 0, 2, NULL, 0, NULL, NULL, 201013000000, '2015-02-11 18:23:34', '2015-02-11 18:23:34', 0, 1, 1, 1),
       (201014, 201014, 'ACIPG/CDL', 9, 0, 2, NULL, 0, NULL, NULL, 201014000000, '2015-02-11 18:23:40', '2015-02-11 18:23:40', 0, 1, 1, 1),
       (201015, 201015, 'DESP. LICITAÇÕES', 9, 0, 2, NULL, 0, NULL, NULL, 201015000000, '2015-02-11 18:23:47', '2015-02-11 18:23:47', 0, 1, 1, 1),
       (201016, 201016, 'MÁQUINAS E EQUIPAMENTOS', 9, 0, 2, NULL, 0, NULL, NULL, 201016000000, '2015-03-27 10:27:00', '2015-03-27 10:27:00', 0, 1, 1, 1),
       (201017, 201017, 'IPTU', 9, 0, 2, '', 0, '', '', 201017000000, '2016-01-06 15:40:05', '2016-01-06 15:40:05', 0, 1, 1, 1),
       (201018, 201018, 'MANUTENÇÕES EM VEÍCULOS', 9, 0, 2, '', 0, '', '', 201018000000, '2016-01-15 15:54:29', '2016-01-15 15:54:29', 0, 1, 1, 1),
       (201019, 201019, 'ALUGUEL', 9, 0, 2, '%S', 0, '', '', 201019000000, '2016-05-11 18:04:35', '2016-05-11 18:04:35', 0, 1, 1, 1),
       (201099, 201099, 'CUSTOS DIVERSOS', 9, 0, 2, '', 0, '', '', 201099000000, '2015-02-11 18:23:57', '2018-11-02 00:45:32', 0, 1, 1, 1),
       (201100, 201100, 'DEPTO. PESSOAL', 9, 0, 2, NULL, 0, NULL, NULL, 201100000000, '2015-02-11 18:24:15', '2015-02-11 18:24:15', 0, 1, 1, 1),
       (202001, 202001, 'CUSTOS DE MERCADORIAS', 10, 0, 2, 'PAGTO A FORNECEDOR', 0, '', '', 202001000000, '2015-02-11 18:25:26', '2018-10-11 17:56:55', 1, 1, 1, 1),
       (202002, 202002, 'IMPOSTO SUPER-SIMPLES', 10, 0, 2, NULL, 0, NULL, NULL, 202002000000, '2015-02-11 18:25:36', '2015-02-11 18:25:36', 0, 1, 1, 1),
       (202003, 202003, 'SAÍDA DIRECIONADA', 10, 0, 2, '', 0, '', 'ROLE_FINANCEIRO_PROPRIET', 202003000000, '2015-02-11 18:25:44', '2015-10-20 12:18:10', 3, 1, 1, 1),
       (202004, 202004, 'TAXA ADMIN. CREDIÁRIO', 10, 0, 2, NULL, 0, NULL, NULL, 202004000000, '2015-03-19 15:59:32', '2015-03-19 15:59:32', 0, 1, 1, 1),
       (202005, 202005, 'CUSTO FINANCEIRO CARTÕES', 10, 0, 2, NULL, 0, NULL, NULL, 202005000000, '2015-04-29 17:58:09', '2015-04-29 17:58:09', 0, 1, 1, 1),
       (202006, 202006, 'DOAÇÕES, AJUDAS, CONTRIBUIÇÕES', 10, 0, 2, NULL, 0, NULL, NULL, 202006000000, '2015-07-20 15:13:26', '2015-07-20 15:13:26', 0, 1, 1, 1),
       (203001, 203001, 'PAGTO. JUROS/TAXAS', 39, 0, 2, NULL, 0, NULL, NULL, 203001000000, '2015-03-06 15:32:28', '2015-03-06 15:32:28', 0, 1, 1, 1),
       (203002, 203002, 'PAGTO. CAPITAL', 39, 0, 2, NULL, 0, NULL, NULL, 203002000000, '2015-03-06 15:32:35', '2015-03-06 15:32:35', 0, 1, 1, 1),
       (203003, 203003, 'PAGTO. GIRO RÁPIDO', 39, 0, 2, NULL, 0, NULL, NULL, 203003000000, '2015-06-09 18:20:45', '2015-06-10 09:16:12', 2, 1, 1, 1),
       (201100001, 201100001, 'FOLHA DE PAGAMENTO', 28, 0, 2, NULL, 0, NULL, NULL, 201100001000, '2015-02-11 18:24:27', '2015-02-11 18:24:27', 0, 1, 1, 1),
       (201100002, 201100002, 'INSS', 28, 0, 2, NULL, 0, NULL, NULL, 201100002000, '2015-02-11 18:24:39', '2015-02-11 18:24:39', 0, 1, 1, 1),
       (201100003, 201100003, 'FGTS', 28, 0, 2, NULL, 0, NULL, NULL, 201100003000, '2015-02-11 18:24:50', '2015-02-11 18:24:50', 0, 1, 1, 1),
       (201100004, 201100004, 'VALE TRANSPORTE', 28, 0, 2, NULL, 0, NULL, NULL, 201100004000, '2015-02-11 18:24:58', '2015-02-11 18:24:58', 0, 1, 1, 1),
       (201100005, 201100005, 'SINDICATO', 28, 0, 2, NULL, 0, NULL, NULL, 201100005000, '2015-02-11 18:25:05', '2015-02-11 18:25:05', 0, 1, 1, 1),
       (201100006, 201100006, 'HONORÁRIOS CONTÁBEIS', 28, 0, 2, NULL, 0, NULL, NULL, 201100006000, '2015-02-11 18:25:17', '2015-02-11 18:25:17', 0, 1, 1, 1),
       (201100007, 201100007, 'MÃO-DE-OBRA', 28, 0, 2, NULL, 0, NULL, NULL, 201100007000, '2015-02-24 08:48:51', '2015-02-24 08:48:51', 0, 1, 1, 1),
       (201100008, 201100008, 'CONVÊNIOS', 28, 0, 2, NULL, 0, NULL, NULL, 201100008000, '2015-02-24 08:49:54', '2015-02-24 08:49:54', 0, 1, 1, 1),
       (201100999, 201100999, 'ENCARGOS SOCIAIS (GERAIS)', 28, 0, 2, NULL, 0, NULL, NULL, 201100999000, '2015-02-24 08:47:11', '2015-02-24 08:47:11', 0, 1, 1, 1),
       (202005001, 202005001, 'CUSTO FINANCEIRO CARTÕES DE CRÉDITO', 51, 0, 2, 'CUSTO FINANCEIRO CARTÕES DE CRÉDITO', 0, NULL, NULL, 202005001000, '2015-06-10 09:48:20', '2015-07-16 09:52:06', 1, 1, 1, 1),
       (202005002, 202005002, 'CUSTO FINANCEIRO CARTÕES DE DÉBITO', 51, 0, 2, 'CUSTO FINANCEIRO CARTÕES DE DÉBITO', 0, NULL, NULL, 202005002000, '2015-06-10 09:48:31', '2015-07-16 09:52:11', 1, 1, 1, 1),
       (203002001, 203002001, 'PAGTO. CAPITAL LONGO PRAZO', 47, 0, 2, NULL, 0, NULL, NULL, 203002001000, '2017-12-13 15:14:23', '2017-12-13 15:14:23', 0, 1, 1, 1),
       (203002002, 203002002, 'PAGTO. CAPITAL CURTO PRAZO', 47, 0, 2, NULL, 0, NULL, NULL, 203002002000, '2017-12-13 15:16:13', '2017-12-13 15:16:13', 0, 1, 1, 1),
       (203002003, 203002003, 'PAGTO. DDPCG', 47, 0, 2, NULL, 0, NULL, NULL, 203002003000, '2017-12-13 15:16:26', '2017-12-13 15:16:26', 0, 1, 1, 1);


TRUNCATE TABLE fin_modo;


INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (1, 1, 'EM ESPÉCIE', 0, 0, 0, 1, 0, 0, '2015-02-11 18:06:03', '2018-11-02 00:45:32', 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (2, 2, 'DÉBITO AUTOMÁTICO', 1, 0, 0, 0, 0, 0, '2015-02-11 18:06:07', '2018-10-11 17:56:50', 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (3, 3, 'CHEQUE PRÓPRIO', 0, 0, 0, 1, 0, 1, '2015-02-11 18:06:09', '2018-10-11 17:56:55', 2, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (4, 7, 'TRANSF. BANCÁRIA', 1, 0, 1, 1, 0, 0, '2015-02-11 18:06:14', '2018-10-11 17:56:57', 4, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (5, 5, 'DEPÓSITO BANCÁRIO', 1, 0, 0, 1, 0, 0, '2015-02-11 18:06:18', '2015-02-11 18:06:18', 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (6, 99, 'INDEFINIDO', 0, 0, 0, 0, 0, 0, '2015-02-12 11:55:31', '2015-02-12 11:55:31', 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (7, 6, 'BOLETO/GUIA/DDA', 1, 0, 0, 0, 0, 0, '2015-02-12 16:58:04', '2015-02-12 16:58:27', 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (8, 4, 'CHEQUE TERCEIROS', 0, 0, 1, 1, 0, 1, '2015-02-19 12:11:56', '2015-10-21 12:23:32', 2, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (9, 9, 'RECEB. CARTÃO CRÉDITO', 0, 0, 0, 1, 1, 0, '2015-04-14 16:02:45', '2018-12-28 15:25:36', 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (10, 10, 'RECEB. CARTÃO DÉBITO', 0, 0, 0, 1, 1, 0, '2015-04-15 11:56:59', '2019-01-02 11:37:58', 1, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (11, 11, 'TRANSF. ENTRE CONTAS', 0, 0, 1, 1, 0, 0, '2015-04-15 17:18:43', '2015-04-15 17:18:45', 0, 1, 1, 1);
INSERT INTO `fin_modo` (`id`, `codigo`, `descricao`, `com_banco_origem`, `moviment_agrup`, `transf_caixa`, `transf_propria`, `modo_cartao`, `modo_cheque`, `inserted`, `updated`, `version`, `estabelecimento_id`,
                        `user_inserted_id`, `user_updated_id`)
VALUES (12, 50, 'MOVIMENTAÇÃO AGRUPADA', 0, 1, 0, 0, 0, 0, '2015-05-18 17:11:23', '2015-05-18 17:11:23', 0, 1, 1, 1);



TRUNCATE TABLE fin_banco;

INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (266, 1, 'BANCO DO BRASIL', 1, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (267, 3, 'BANCO DA AMAZONIA', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (268, 4, 'BANCO DO NORDESTE DO BRASIL', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (269, 12, 'BANCO STANDARD DE INVESTIMENTOS', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (270, 21, 'BANESTES  BANCO DO ESTADO DO ESPIRITO SANTO', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (271, 24, 'BANCO DE PERNAMBUCO -BANDEPE', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (272, 25, 'BANCO ALFA', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (273, 27, 'BANCO DO ESTADO DE SANTA CATARINA', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (274, 29, 'BANCO BANERJ', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (275, 31, 'BANCO BEG', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (276, 33, 'BANCO SANTANDER', 1, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (277, 34, 'BANCO DO ESTADO DO AMAZONAS', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (278, 36, 'BANCO BRADESCO BBI', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (279, 37, 'BANCO DO ESTADO DO PARA', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (280, 38, 'BANCO BANESTADO', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (281, 39, 'BANCO DO ESTADO DO PIAUI  - BEP', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (282, 40, 'BANCO CARGILL', 0, '2015-02-24 10:05:09', '2015-02-24 10:05:09', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (283, 41, 'BANCO DO ESTADO DO RIO GRANDE DO SUL', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (284, 44, 'BANCO BVA SA', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (285, 45, 'BANCO OPPORTUNITY', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (286, 47, 'BANCO DO ESTADO DE SERGIPE', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (287, 62, 'HIPERCARD BANCO MÚLTIPLO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (288, 63, 'BANCO IBI  - BANCO MULTIPLO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (289, 64, 'GOLDMAN SACHS DO BRASIL BANCO MÚLTIPLO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (290, 65, 'LEMON BANK BANCO MÚLTIPLO S..A', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (291, 66, 'BANCO MORGAN STANLEY', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (292, 69, 'BPN BRASIL BANCO MÚLTIPLO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (293, 70, 'BRB - BANCO DE BRASILIA', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (294, 72, 'BANCO RURAL MAIS', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (295, 73, 'BB BANCO POPULAR DO BRASL', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (296, 74, 'BANCO J.SAFRA', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (297, 75, 'BANCO CR2', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (298, 76, 'BANCO KDB DO BRASIL', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (299, 78, 'BES INVESTIMENTO DO BRASIL - BANCO DE INVESTIMENTO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (300, 95, 'BANCO CONFIDENCE DE CÂMBIO', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (301, 96, 'BANCO BM&F DE SERVIÇOS DE LIQUIDAÇÃO E CUSTÓDIA', 0, '2015-02-24 10:05:10', '2015-02-24 10:05:10', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (302, 104, 'CAIXA ECONÔMICA FEDERAL', 1, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (303, 107, 'BANCO BBM', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (304, 116, 'BANCO ÚNICO', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (305, 119, 'BANCO WESTERN UNION DO BRASIL', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (306, 125, 'BRASIL PLURAL - BANCO MÚLTIPLO', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (307, 151, 'BANCO NOSSA CAIXA', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (308, 175, 'BANCO FINASA', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (309, 184, 'BANCO ITAÚ - BBA', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (310, 204, 'BANCO BRADESCO CARTÕES', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (311, 208, 'BANCO UBS PACTUAL', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (312, 212, 'BANCO MATONE', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (313, 213, 'BANCO ARBI', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (314, 214, 'BANCO DIBENS', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (315, 215, 'BANCO ACOMERCIAL E DE INVESTIMENTO SUDAMERIS', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (316, 217, 'BANCO JOHN DEERE', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (317, 218, 'BANCO BONSUCESSO', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (318, 222, 'BANCO CLAYON BRASIL', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (319, 224, 'BANCO FIBRA', 0, '2015-02-24 10:05:11', '2015-02-24 10:05:11', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (320, 225, 'BANCO BRASCAN', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (321, 229, 'BANCO CRUZEIRO DO SUL', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (322, 230, 'UNICARD BANCO MÚLTIPLO', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (323, 233, 'BANCO GE CAPITAL', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (324, 237, 'BANCO BRADESCO', 1, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (325, 241, 'BANCO CLASSICO', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (326, 243, 'BANCO MAXIMA', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (327, 246, 'BANCO ABC-BRASIL', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (328, 248, 'BANCO BOAVISTA INTERATLANTICO', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (329, 249, 'BANCO INVESTCRED UNIBANCO', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (330, 250, 'BANCO SCHAHIN', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (331, 252, 'BANCO FININVEST', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (332, 254, 'PARANÁ BANCO', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (333, 263, 'BANCO CACIQUE', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (334, 265, 'BANCO FATOR', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (335, 266, 'BANCO CEDULA', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (336, 300, 'BANCO DE LA NACION ARGENTINA', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (337, 318, 'BANCO BMG', 0, '2015-02-24 10:05:12', '2015-02-24 10:05:12', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (338, 320, 'BANCO INDUSTRIAL E COMERCIAL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (339, 341, 'BANCO ITAU', 1, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (340, 356, 'BANCO ABN AMRO REAL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (341, 366, 'BANCO SOCIETE GENERALE BRASIL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (342, 370, 'BANCO WESTLB DO BRASIL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (343, 376, 'BANCO J.P. MORGAN', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (344, 389, 'BANCO MERCANTIL DO BRASIL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (345, 394, 'BANCO BMC', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (346, 399, 'HSBC BANK BRASIL -BANCO MULTIPLO', 1, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (347, 409, 'UNIBANCO - UNIAO DE BANCOS BRASILEIROS', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (348, 412, 'BANCO CAPITAL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (349, 422, 'BANCO SAFRA', 1, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (350, 453, 'BANCO RURAL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (351, 456, 'BANCO DE TOKYO-MITSUBISHI UFJ BRASIL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (352, 464, 'BANCO SUMITOMO MITSUI BRASILEIRO', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (353, 473, 'BANCO CAIXA GERAL - BRASIL', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (354, 477, 'CITIBANK N.A.', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (355, 479, 'BANCO ITAUBANK', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (356, 487, 'DEUTSCHE BANK S. A. - BANCO ALEMAO', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (357, 488, 'JPMORGAN CHASE BANK, NATIONAL ASSOCIATION', 0, '2015-02-24 10:05:13', '2015-02-24 10:05:13', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (358, 492, 'ING BANK N.V.', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (359, 494, 'BANCO DE LA REPUBLICA ORIENTAL DEL URUGUAY', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (360, 495, 'BANCO DE LA PROVINCIA DE BUENOS AIRES', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (361, 505, 'BANCO CREDIT SUISSE (BRASIL)', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (362, 600, 'BANCO LUSO BRASILEIRO', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (363, 604, 'BANCO INDUSTRIAL DO BRASIL S. A.', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (364, 610, 'BANCO VR', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (365, 611, 'BANCO PAULISTA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (366, 612, 'BANCO GUANABARA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (367, 613, 'BANCO PECUNIA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (368, 623, 'BANCO PANAMERICANO', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (369, 626, 'BANCO FICSA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (370, 630, 'BANCO INTERCAP', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (371, 633, 'BANCO RENDIMENTO', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (372, 634, 'BANCO TRIANGULO', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (373, 637, 'BANCO SOFISA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (374, 638, 'BANCO PROSPER', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (375, 641, 'BANCO ALVORADA', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (376, 643, 'BANCO PINE', 0, '2015-02-24 10:05:14', '2015-02-24 10:05:14', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (377, 652, 'BANCO ITAÚ HOLDING FINANCEIRA', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (378, 653, 'BANCO INDUSVAL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (379, 654, 'BANCO A.J. RENNER', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (380, 655, 'BANCO VOTORANTIM', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (381, 707, 'BANCO DAYCOVAL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (382, 719, 'BANIF - BANCO INTERNACIONAL DO FUNCHAL (BRASIL),', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (383, 721, 'BANCO CREDIBEL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (384, 734, 'BANCO GERDAU', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (385, 735, 'BANCO POTTENCIAL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (386, 738, 'BANCO MORADA', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (387, 739, 'BANCO BGN', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (388, 740, 'BANCO BARCLAYS', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (389, 741, 'BANCO RIBEIRAO PRETO', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (390, 743, 'BANCO SEMEAR', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (391, 744, 'BANKBOSTON N.A.', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (392, 745, 'BANCO CITIBANK', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (393, 746, 'BANCO MODAL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (394, 747, 'BANCO RABOBANK INTERNATIONAL BRASIL', 0, '2015-02-24 10:05:15', '2015-02-24 10:05:15', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (395, 748, 'BANCO COOPERATIVO SICREDI', 1, '2015-02-24 10:05:15', '2018-10-11 17:56:55', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (396, 749, 'BANCO SIMPLES', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (397, 751, 'DRESDNER BANK BRASIL  BANCO MULTIPLO.', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (398, 752, 'BANCO BNP PARIBAS BRASIL', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (399, 753, 'BANCO COMERCIAL URUGUAI', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (400, 755, 'BANK OF AMERICA MERRILL LYNCH BANCO MÚLTIPLO', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (402, 757, 'BANCO KEB DO BRASIL', 0, '2015-02-24 10:05:16', '2015-02-24 10:05:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (403, 756, 'BANCO COOPERATIVO DO BRASIL - BANCOOB (SICOOB)', 1, '2015-02-24 10:08:58', '2015-02-24 10:08:58', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (404, 999, 'INDEFINIDO', 0, '2015-03-19 12:29:16', '2015-03-19 12:29:16', 0, 1, 1, 1);
INSERT INTO `fin_banco` (`id`, `codigo_banco`, `nome`, `utilizado`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (NULL, 197, 'STONE PAGAMENTOS S.A.', true, '2015-03-19 12:29:16', '2015-03-19 12:29:16', 0, 1, 1, 1);


TRUNCATE TABLE fin_operadora_cartao;

INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 'CIELO', 14, '2015-05-04 12:18:06', '2015-05-04 17:07:22', 1, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (2, 'REDECARD', 15, '2015-05-04 12:18:09', '2015-05-04 17:07:37', 1, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (3, 'CIELO MSP', 16, '2015-05-04 12:18:14', '2015-05-04 17:07:27', 2, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (4, 'STONE', 22, '2017-09-19 12:21:55', '2019-01-02 01:32:12', 0, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (5, 'STONE MSP', 24, '2018-03-01 09:58:29', '2019-01-02 11:37:58', 0, 1, 1, 1);
INSERT INTO `fin_operadora_cartao` (`id`, `descricao`, `carteira_id`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (6, 'MODERNINHA', 21, '2018-03-02 16:50:24', '2018-03-02 16:50:24', 0, 1, 1, 1);



TRUNCATE TABLE fin_bandeira_cartao;

INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (1, 'MASTER MAESTRO', 10, 'MC MAESTRO\r\nMASTERCARD\r\nMASTERCARD MAESTRO', '2015-04-25 12:10:58', '2019-01-02 01:32:12', 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (2, 'VISA ELECTRON', 10, 'VISA ELECTRON', '2015-04-25 12:13:22', '2019-01-02 11:37:58', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (3, 'ELO DÉBITO', 10, 'ELO DÉBITO\r\nELO', '2015-04-25 12:14:14', '2019-01-02 00:49:20', 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (4, 'VISA', 9, 'VISA\r\nVISA PARCELADO\r\nVISA CRÉDITO', '2015-04-28 18:45:48', '2018-12-28 15:25:36', 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (5, 'MASTERCARD', 9, 'MASTERCARD\r\nMC PARCELADO\r\nMC CRÉDITO', '2015-04-28 18:46:25', '2018-12-28 15:25:36', 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (6, 'HIPERCARD', 9, 'HIPERCARD', '2015-04-28 18:46:36', '2018-12-28 15:25:36', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (7, 'ELO CRÉDITO', 9, 'ELO PARCELADO\r\nELO', '2015-04-28 18:52:00', '2018-12-28 15:25:35', 2, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (8, 'AGIPLAN CRÉDITO', 9, 'AGIPLAN CRÉDITO', '2015-04-28 18:52:41', '2015-10-20 15:00:14', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (9, 'DINERS', 9, 'DINERS\r\nDINERS CLUB', '2015-04-28 18:52:55', '2015-10-20 15:00:22', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (10, 'SICREDI', 9, 'SICREDI', '2015-04-29 18:10:14', '2015-10-20 15:01:18', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (11, 'CABAL CRÉDITO', 9, 'CABAL CRÉDITO', '2016-03-09 11:21:48', '2016-03-09 11:21:50', 0, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (12, 'AMEX', 9, 'AMEX\r\nAMERICANEXPRESS', '2018-01-12 09:28:28', '2018-09-04 15:27:15', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (13, 'N INF CRÉD', 9, ' ', '2018-03-02 16:49:10', '2018-03-02 16:50:09', 1, 1, 1, 1);
INSERT INTO `fin_bandeira_cartao` (`id`, `descricao`, `modo_id`, `labels`, `inserted`, `updated`, `version`, `estabelecimento_id`, `user_inserted_id`, `user_updated_id`)
VALUES (14, 'N INF DÉB', 10, ' ', '2018-03-02 16:49:23', '2018-03-02 16:50:03', 3, 1, 1, 1);


COMMIT;