ALTER TABLE fin_categoria ADD `json_data` json AFTER `codigo_ord`;

UPDATE fin_categoria SET codigo_ord = CONCAT(codigo, LPAD('', 18 - LENGTH(codigo), '0'));
