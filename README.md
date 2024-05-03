# CMS Backpack 2.0
 
 # System Requirements
 Before going to **CMS Backpack** Installation, you have to make sure of these following configuration.
 
 - PHP & Server Configuration
 - Composer Installation
   
 ## PHP & It’s Exentions
 Here are some needed PHP and it’s extension needed configuration that we must need before go with **CMS Backpack**.
 
 - PHP >= 7.3
 - BCMath PHP Extension
 - Ctype PHP Extension
 - Fileinfo PHP Extension
 - JSON PHP Extension
 - Mbstring PHP Extension
 - OpenSSL PHP Extension
 - PDO PHP Extension
 - Tokenizer PHP Extension
 - XML PHP Extension
 
 ## Server Requirement
 You should have Apache or Nginx a web server in your system. According to your available operating system you need to install Either WAMP (Windows bases OS), XAMPP (Cross platoform OS), LAMP (Linux bases OS) servers to run PHP applications.
 
 # Installation
 You can install this project in a production environnement, or in a development environnement and choose which modules to install.
 
 ## Development
  * Create a database.
   
     > The project expects a **MYSQL** database as described in the [.env.development](.env.development) file.
 
  * Run:
   ```properties
   bin/install.sh
   ```
 
 ## Production
 
 Please follow those steps in order:
 
 - Run `cp .env.production .env`
 - Create a database and configured in the database section of the `.env` file.
 
     ``` properties
     # Example:
     
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=cms-backpack
     DB_USERNAME=root
     DB_PASSWORD=root
     ```
 
 
  * Set up the **SMTP** configuration in the `.env` file.
 
     ```properties
     # Example:
     
     MAIL_MAILER=smtp
     MAIL_HOST=mailhog
     MAIL_PORT=1025
     MAIL_USERNAME=null
     MAIL_PASSWORD=null
     MAIL_ENCRYPTION=null
     MAIL_FROM_ADDRESS=null
     MAIL_FROM_NAME="${APP_NAME}"
     ```
 
  * Set up the **APP_URL** in the `.env` file.
 
     ```properties
     # Example:
 
     APP_URL="http://cms-backpack.com"
     ```
 
    
  * Execute the following commands in order:
  
     ```properties
     composer install
     php artisan key:generate
     php artisan storage:link
     php artisan migrate:fresh --seed
     php artisan optimize
     php artisan route:clear
     ```

