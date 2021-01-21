# crosierapp-radx
App com Módulos "raíz" do Crosier: CRM, RH, Financeiro, Vendas, Estoque, Fiscal


## Montagem do Ambiente de Desenvolvimento

> Este tutorial foi realizado em uma máquina com Linux Mint 20.

Dependências

- PHP 7.4
- git
- composer 2
- nodejs e yarn
- MySQL 8
- Apache 2.4
- openssl e certbot
  <br><br>
### Passo-a-passo


```
mkdir -p /altere/aqui/sua/pasta/do/projeto/

cd /altere/aqui/sua/pasta/do/projeto/

git clone https://www.github.com/crosiersource/crosier-core.git
cp crosier-core/sh/* .
cd crosier-core
composer install
yarn install
cd ..

git clone https://www.github.com/crosiersource/crosierlib-base.git
cd crosierlib-base
composer install
cd ..

git clone https://www.github.com/crosiersource/crosierlib-radx.git
cd crosierlib-radx
composer install
cd ..

git clone https://www.github.com/crosiersource/crosierapp-radx.git
cd crosierapp-radx
composer install
yarn install
cd ..


```

<br><br>

### Banco de Dados

Aqui estamos considerando que o nome do banco de dados será `crosier_dev`

```
cd /altere/aqui/sua/pasta/do/projeto/

sudo mysql < crosier-core/sql/CREATE_DATABASE-[sua-versao-do-mysql].sql

sudo mysql crosier_dev < crosier-core/sql/METADADOS.sql 
sudo mysql crosier_dev < crosier-core/sql/DADOS.sql 
sudo mysql crosier_dev < crosier-core/sql/DADOS_bse_diautil.sql 
sudo mysql crosier_dev < crosier-core/sql/DADOS_bse_uf_municipio.sql 

sudo mysql crosier_dev < crosierapp-radx/sql/METADADOS.sql
sudo mysql crosier_dev < crosierapp-radx/sql/DADOS.sql
sudo mysql crosier_dev < crosierapp-radx/sql/APP.sql
sudo mysql crosier_dev < crosierapp-radx/sql/ROLES.sql

sudo mysql crosier_dev < crosierapp-radx/sql/Fiscal/ncms.sql
```


### Configurando variáveis de ambiente para os projetos

Criar uma cópia de `crosier-core/.env` para `crosier-core/.env.local` e alterar as variáveis conforme o projeto e sua instalação. 

Fazer o mesmo para `crosierapp-radx`.
Atenção: 

```
CROSIERCORE_APPSECRET=*******DEVE SER O MESMO DA VARIÁVEL APP_SECRET no crosier-core/.env.local*******
```

### Montando os projetos

```
cd /altere/aqui/sua/pasta/do/projeto/

./atualiza.sh crosier-core prod
./atualiza.sh crosierapp-radx prod

```


<br><br>

### Configurando sites no Apache 2.4

> Atenção: **Os projetos só rodam com HTTPS**!


> Devem estar habilitados os módulos **rewrite** e **ssl**
> Ativar com:
>
> `sudo a2enmod rewrite`
>
> `sudo a2enmod ssl`

Criar o certificado auto-assinado:

```
export arquivo=dev.crosier

sudo openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 -nodes -keyout /etc/ssl/private/$arquivo.key -out /etc/ssl/certs/$arquivo.crt \
-subj /CN=*.dev.crosier -addext subjectAltName=DNS:*.dev.crosier,\
DNS:*.nome-do-projeto.dev.crosier
```

Criar os sites para os projetos **crosier-core** e **crosierapp-radx** no Apache 2.4.


<br><br>
##### crosier-core

Em `/etc/apache2/sites-available/core.nome-do-projeto.dev.crosier.conf`

```
<VirtualHost *:80>
        ServerName core.nome-do-projeto.dev.crosier
        DocumentRoot /altere/aqui/sua/pasta/do/projeto/crosier-core/public

        <Directory "/altere/aqui/sua/pasta/do/projeto/crosier-core/public">
                AllowOverride None
                Require all granted
                FallbackResource /index.php
        </Directory>

        <Directory "/altere/aqui/sua/pasta/do/projeto/crosier-core/public/bundles">
                FallbackResource disabled
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/core.nome-do-projeto.dev.crosier_error.log
        CustomLog ${APACHE_LOG_DIR}/core.nome-do-projeto.dev.crosier_access.log combined
RewriteEngine on
RewriteCond %{SERVER_NAME} =core.nome-do-projeto.dev.crosier
RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

<br>

Em `/etc/apache2/sites-available/core.nome-do-projeto.dev.crosier-ssl.conf`

```
<IfModule mod_ssl.c>
<VirtualHost *:443>
        ServerName core.nome-do-projeto.dev.crosier

	DocumentRoot /altere/aqui/sua/pasta/do/projeto/crosier-core/public

	<Directory "/altere/aqui/sua/pasta/do/projeto/crosier-core/public">
        	AllowOverride None
		Require all granted
		FallbackResource /index.php
	</Directory>

	<Directory "/altere/aqui/sua/pasta/do/projeto/crosier-core/public/bundles">
        	FallbackResource disabled
	</Directory>

        ErrorLog ${APACHE_LOG_DIR}/core.nome-do-projeto.dev.crosier-ssl_error.log
        CustomLog ${APACHE_LOG_DIR}/core.nome-do-projeto.dev.crosier-ssl_access.log combined

	SSLCertificateFile /etc/ssl/certs/dev.crosier.crt
	SSLCertificateKeyFile /etc/ssl/private/dev.crosier.key

SSLEngine on

# Intermediate configuration, tweak to your needs
SSLProtocol             all -SSLv2 -SSLv3
SSLCipherSuite          ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA:ECDHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-RSA-AES256-SHA256:DHE-RSA-AES256-SHA:ECDHE-ECDSA-DES-CBC3-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:DES-CBC3-SHA:!DSS
SSLHonorCipherOrder     on
SSLCompression          off

SSLOptions +StrictRequire

</VirtualHost>
</IfModule>
```

<br> 
Copie os arquivos:

`sudo cp /etc/apache2/sites-available/core.nome-do-projeto.dev.crosier.conf /etc/apache2/sites-available/radx.nome-do-projeto.dev.crosier.conf`

`sudo cp /etc/apache2/sites-available/core.nome-do-projeto.dev.crosier-ssl.conf /etc/apache2/sites-available/radx.nome-do-projeto.dev.crosier-ssl.conf`

... e edite-os alterando "**core**" por "**radx**" e "**crosier-core**" por "**crosierapp-radx**".

<br>
<br>

> **Não esquecer de ativar os sites** com `a2ensite` !!!

<br>
<br>


> Não esquecer de adicionar os domínios no arquivo de *hosts*
>
> `127.0.0.1 core.nome-do-projeto.dev.crosier`<br>
> `127.0.0.1 radx.nome-do-projeto.dev.crosier`<br>

<br>

> Na primeira vez que for acessar pelo browser, irá dar erro de certificado, por se tratar de um certificado auto-assinado (self-signed)
>
> Será necessário exportar o certificado e adicioná-lo **manualmente** em seu repositório de certificados confiáveis.









<br><br>
### Outros

1) Talvez seja preciso criar manualmente o diretório `/altere/aqui/sua/pasta/do/projeto/crosier-core/var/session/` com permissão de escrita para o usuário executor do webserver (Apache).

2) Acessar https://core.nome-do-projeto.dev.crosier/cfg/app/form/1 e adicionar uma nova configuração:

    - Chave: URL_devlocal
    - Valor: https://core.nome-do-projeto.dev.crosier
<br>Depois sair e logar novamente.

<br><br>

### Problemas/Soluções

<br>    

**PROBLEMA:** `entrypoints.json" does not exist.`
**SOLUÇÃO:** Verificar se o comando `./atualiza.sh crosier-core prod` ou `./atualiza.sh crosierapp-radx prod` foi executado com sucesso 

<br>

**PROBLEMA:** Login não funciona <br>
**SOLUÇÃO:** Verificar se está digitando certo o usuário `admin` e a senha `admin@123` <br>
ou <br>
Verificar novamente as variáveis dos arquivos `crosier-core/.env.local` e `crosierapp-radx/.env.local`<br>
 


