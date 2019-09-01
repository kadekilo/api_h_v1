#Developer Test by Karlos García

Welcome to the TEST developed by Karlos García for demonstrate a project development with Symfony 3.4, Doctrine ORM, and more.

##START UP

###Cloning and set up.

    # clone de project
    > cd projects/
    > git clone ...
    # make Composer install the project's dependencies into vendor/
    > cd my_project_name/
    > composer install
    # now Composer will ask you for the values of any undefined parameter
     ...

###Server

This configuration of apache virtualhost it's pretendend for dont't use .htaccess in order to serve the web directory.

    <VirtualHost *:*>
            DocumentRoot "C:/Program Files (x86)/EasyPHP-Devserver-17/eds-www/sss/symfony/api_h_v1/web"
            ServerName demohola.com
            DirectoryIndex /app.php
            <Directory "C:/Program Files (x86)/EasyPHP-Devserver-17/eds-www/sss/symfony/api_h_v1/web">
                            AllowOverride All
                            Require all granted
                            Allow from All

                    FallbackResource /app.php
            </Directory>
            <Directory /var/www/project/web/bundles>
                            FallbackResource disabled
            </Directory>
            RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

            ErrorLog "C:/Program Files (x86)/EasyPHP-Devserver-17/eds-www/sss/error_log_hola"
    </VirtualHost>