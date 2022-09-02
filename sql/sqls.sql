
begin;

UPDATE fin_movimentacao
  set cedente_documento = substr(
    REGEXP_REPLACE(cedente_nome, '[^0-9]', ''), 
    1, 14
    ), cedente_nome = trim(substr(cedente_nome, 21)) where cedente_nome LIKE '__.___.___/____-__%';

UPDATE fin_movimentacao
set cedente_documento = substr(
  REGEXP_REPLACE(cedente_nome, '[^0-9]', ''),
  1, 11
  ), cedente_nome = trim(substr(cedente_nome, 17)) where cedente_nome LIKE '___.___.___-__%';


select cedente_documento, cedente_nome from fin_movimentacao group by cedente_documento, cedente_nome order by cedente_nome;




UPDATE fin_movimentacao
set sacado_documento = substr(
  REGEXP_REPLACE(sacado_nome, '[^0-9]', ''),
  1, 14
  ), sacado_nome = trim(substr(sacado_nome, 21)) where sacado_nome LIKE '__.___.___/____-__%';

UPDATE fin_movimentacao
set sacado_documento = substr(
  REGEXP_REPLACE(sacado_nome, '[^0-9]', ''),
  1, 11
  ), sacado_nome = trim(substr(sacado_nome, 17)) where sacado_nome LIKE '___.___.___-__%';


select sacado_documento, sacado_nome from fin_movimentacao group by sacado_documento, sacado_nome order by sacado_nome;