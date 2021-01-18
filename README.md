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

git clone git@github.com:crosiersource/crosier-core.git
cp crosier-core/atualiza*sh .
cd crosier-core
composer -v install
yarn install

git clone git@github.com:crosiersource/crosierlib-base.git
cd crosierlib-base
composer -v install

git clone git@github.com:crosiersource/crosierlib-radx.git
cd crosierlib-radx
composer -v install

git clone git@github.com:crosiersource/crosierapp-radx.git
cd crosierapp-radx
composer -v install
yarn install


cd /altere/aqui/sua/pasta/do/projeto/

./atualiza.sh crosier-core prod
./atualiza.sh crosierapp-radx prod

```

<br><br>
Para facilitar a atualização dos módulos, criar o script `/altere/aqui/sua/pasta/do/projeto/`

```
./atualizaGit.sh crosierlib-base
./atualizaGit.sh crosier-core
./atualizaGit.sh crosierapp-radx
./atualizaGit.sh crosierlib-radx

```
<br><br><br>

### Configurando variávies de ambiente para os projetos

Em `crosier-core/.env.local`

```
###> symfony/framework-bundle ###
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=175bd6d3-6c29-438a-9520-47fcee653cc5
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL=mysql://crosier_dev:crosier_dev@localhost:3306/crosier_dev

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN=^http(s){0,1}?://(.)+(:[0-9]+)?$
###< nelmio/cors-bundle ###

CROSIER_MAINDOMAIN=nome-do-projeto.dev.crosier
CROSIER_SESSIONS_FOLDER=/altere/aqui/sua/pasta/do/projeto/crosier-core/var/session/

CROSIERAPP_UUID=175bd6d3-6c29-438a-9520-47fcee653cc5
CROSIERCORE_URL=https://core.nome-do-projeto.dev.crosier
CROSIER_ENV=devlocal

CROSIERAPP_ID=crosier-core

# Caso esteja rodando em localhost com self-signed certificate
CROSIERCORE_SELFSIGNEDCERT=/home/carlos/_.dev.crosier

CROSIER_LOGO=https://.........logo.png
```

<br><br>
Em `crosierapp-radx/.env.local`

```
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=9121ea11-dc5d-4a22-9596-187f5452f95a

DATABASE_URL=mysql://crosier_dev:crosier_dev@localhost:3306/crosier_dev

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN=^http(s){0,1}?://(.)+(:[0-9]+)?$
###< nelmio/cors-bundle ###

CROSIER_MAINDOMAIN=nome-do-projeto.dev.crosier
CROSIER_SESSIONS_FOLDER=/altere/aqui/sua/pasta/do/projeto/crosier-core/var/session/
CROSIERCORE_APPSECRET=*******DEVE SER O MESMO DA VARIÁVEL APP_SECRET no crosier-core/.env.local*******
CROSIER_ENV=devlocal

CROSIERAPP_UUID=9121ea11-dc5d-4a22-9596-187f5452f95a
CROSIERAPPRADX_UUID=9121ea11-dc5d-4a22-9596-187f5452f95a
CROSIERAPP_ID=crosierappradx
CROSIERCORE_URL=https://core.nome-do-projeto.dev.crosier

# Caso esteja rodando em localhost com self-signed certificate
CROSIERCORE_SELFSIGNEDCERT=/home/carlos/_.dev.crosier



CROSIER_LOGO=https://.........logo.png
```



<br><br>

### Configurando sites no Apache 2.4

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
<br><br>
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
SSLCipherSuite          ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256
-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA:ECDHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-S
HA:DHE-RSA-AES256-SHA256:DHE-RSA-AES256-SHA:ECDHE-ECDSA-DES-CBC3-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:DES-CBC3-SHA:!DSS
SSLHonorCipherOrder     on
SSLCompression          off

SSLOptions +StrictRequire

</VirtualHost>
</IfModule>
```


<br><br>
##### crosierapp-radx

Em `/etc/apache2/sites-available/radx.nome-do-projeto.dev.crosier.conf`

```
<VirtualHost *:80>
        ServerName radx.nome-do-projeto.dev.crosier
        DocumentRoot /altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public

        <Directory "/altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public">
                AllowOverride None
                Require all granted
                FallbackResource /index.php
        </Directory>

        <Directory "/altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public/bundles">
                FallbackResource disabled
        </Directory>

        ErrorLog ${APACHE_LOG_DIR}/radx.nome-do-projeto.dev.crosier_error.log
        CustomLog ${APACHE_LOG_DIR}/radx.nome-do-projeto.dev.crosier_access.log combined
RewriteEngine on
RewriteCond %{SERVER_NAME} =radx.nome-do-projeto.dev.crosier
RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

<br><br>
Em `/etc/apache2/sites-available/radx.nome-do-projeto.dev.crosier-ssl.conf`

```
<IfModule mod_ssl.c>
<VirtualHost *:443>
        ServerName radx.nome-do-projeto.dev.crosier

	DocumentRoot /altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public

	<Directory "/altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public">
        	AllowOverride None
		Require all granted
		FallbackResource /index.php
	</Directory>

	<Directory "/altere/aqui/sua/pasta/do/projeto/crosierapp-radx/public/bundles">
        	FallbackResource disabled
	</Directory>

        ErrorLog ${APACHE_LOG_DIR}/radx.nome-do-projeto.dev.crosier-ssl_error.log
        CustomLog ${APACHE_LOG_DIR}/radx.nome-do-projeto.dev.crosier-ssl_access.log combined

	SSLCertificateFile /etc/ssl/certs/dev.crosier.crt
	SSLCertificateKeyFile /etc/ssl/private/dev.crosier.key

SSLEngine on

# Intermediate configuration, tweak to your needs
SSLProtocol             all -SSLv2 -SSLv3
SSLCipherSuite          ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256
-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA:ECDHE-RSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-S
HA:DHE-RSA-AES256-SHA256:DHE-RSA-AES256-SHA:ECDHE-ECDSA-DES-CBC3-SHA:ECDHE-RSA-DES-CBC3-SHA:EDH-RSA-DES-CBC3-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:DES-CBC3-SHA:!DSS
SSLHonorCipherOrder     on
SSLCompression          off

SSLOptions +StrictRequire

</VirtualHost>
</IfModule>
```




> Não esquecer de adicionar os domínios no arquivo de *hosts*
>
> `127.0.0.1 core.nome-do-projeto.dev.crosier`<br>
> `127.0.0.1 radx.nome-do-projeto.dev.crosier`<br>




> Não esquecer de ativar os sites com `a2ensite`






<br><br>
### Banco de Dados

```
cd /altere/aqui/sua/pasta/do/projeto/

sudo mysql < crosier-core/sql/00-CREATE_DATABASE.sql

sudo mysql crosier_dev < crosier-core/sql/01-METADADOS_cfg_sec.sql 
sudo mysql crosier_dev < crosier-core/sql/02-APP.sql 
sudo mysql crosier_dev < crosier-core/sql/03-DADOS_cfg_sec.sql 
sudo mysql crosier_dev < crosier-core/sql/04-METADADOS_bse.sql 
sudo mysql crosier_dev < crosier-core/sql/05-DADOS_bse.sql 
sudo mysql crosier_dev < crosier-core/sql/cfg_entmenu.sql 
sudo mysql crosier_dev < crosier-core/sql/DADOS_bse_diautil.sql 
sudo mysql crosier_dev < crosier-core/sql/DADOS_bse_uf_municipio.sql 
sudo mysql crosier_dev < crosier-core/sql/functions.sql 




```

<br><br>
### Outros

1) Criar manualmente o diretório `/altere/aqui/sua/pasta/do/projeto/crosier-core/var/session/` com permissão de escrita para o usuário executor do webserver.


2) Acessar https://core.nome-do-projeto.dev.crosier/cfg/app/form/1 e adicionar uma nova configuração:

    - Chave: URL_devlocal
    - Valor: https://core.nome-do-projeto.dev.crosier

Sair e logar novamente.

3) 


