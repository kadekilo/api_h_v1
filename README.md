# Developer Test by Karlos García

Welcome to the TEST developed by Karlos García for demonstrate a project development with Symfony 3.4, Doctrine ORM, and more.

## START UP

### Cloning and set up.

    # clone de project
    > cd projects/
    > git clone ...
    # make Composer install the project's dependencies into vendor/
    > cd my_project_name/
    > composer install
    # now Composer will ask you for the values of any undefined parameter
     ...

### Server

This configuration of apache virtualhost it's pretendend for dont't use .htaccess in order to serve the web directory.

    <VirtualHost *:*>
            DocumentRoot "path_to/api_h_v1/web"
            ServerName demohola.com
            DirectoryIndex /app.php
            <Directory "path_to/api_h_v1/web">
                            AllowOverride All
                            Require all granted
                            Allow from All
                    FallbackResource /app.php
            </Directory>
            <Directory path_to/api_h_v1/web/bundles>
                            FallbackResource disabled
            </Directory>
            RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    </VirtualHost>

### DataBase

3 method for this. 

First using Doctrine after install with Composer:
    
    > php bin/console doctrine:schema:update --force
    
Second method, using Doctrine Migrations:
    
    > php bin/console doctrine:migrations:status
    
Third method, with this SQL:

	CREATE DATABASE IF NOT EXISTS `hola_mundo` /*!40100 DEFAULT CHARACTER SET utf8 */;
	USE `hola_mundo`;
	DROP TABLE IF EXISTS `user`;
	CREATE TABLE IF NOT EXISTS `user` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`)
	) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	DELETE FROM `user`;
	INSERT INTO `user` (`id`, `name`, `username`, `password`, `roles`) VALUES
		(1, 'admin', 'admin', '3b3b4904477b0d47a5627249ff5f1212', 'ADMIN');
    
## More information

