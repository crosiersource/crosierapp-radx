select distinct(json_data->>"$.dados_importados.vendedor") as vendedores from ven_venda;




select json_data->>"$.dados_importados.vendedor" as v_import, json_data->>"$.vendedor_nome" as vendedor_nome from ven_venda limit 10;




update est_produtoO set  where json_data->"$.deficit_estoque_matriz" = CAST('null' AS JSON);

update ven_venda set json_data = json_set(json_data, '$.vendedor_nome', substr(
            json_data->>"$.dados_importados.vendedor",
            10,
            if(
                        position('</li>' in json_data->>"$.dados_importados.vendedor") > 0,
                        position('</li>' in json_data->>"$.dados_importados.vendedor") - 10,
                        position('</ul>' in json_data->>"$.dados_importados.vendedor") - 10
                )

    ));