United 3D Makers
========

Prerequisites
========

The **wkhtmltopdf** library is mandatory to generate PDF files, with the appropriate execution rights. The path to the binary file must be set in parameters.yml *snappy_lib_path* key.

The mailing system uses **Sendinblue** API and SMTP server. The SMTP server configuration must be set in parameters.yml *mailer_** keys.


Server File System Recommendations
========

To facilitate the deployments (by keeping the logs, sessions, uploads and parameters from one release to another), the following file system structure is recommended on the server:

    current -> ./releases/v1.2.1/
    htdocs -> ./current/web/
    releases/
        |_ v1.0.0/
        |_ v1.1.0/
        |_ v1.2.0/
        |_ v1.2.1/
        |_ vX.Y.Z/
    shared/
        |_ config/
        |_ logs/
        |_ sessions/
        |_ uploads/
            |_ maker/
                |_ identy-paper/
                |_ portfolio/
                |_ profile/
            |_ message/
                |_ attachment/
            |_ order/
            |_ print/
            |_ project/
            |_ ref/

**Important**: the sub-directories in the uploads directory must all exist.
    
**Composer** is required to install releases. If it is not already installed on the server, it can be put in an additional dedicated "tools" directory, with the appropriate execution rights (see installation procedure on https://getcomposer.org):

    tools/
    

Deployment
========

**Step 1 - Local**

Locally compile the VueJS production assets:

    ./node_modules/.bin/encore production
    
This will generate the following directory:

    /web/build

Upload that directory to the server, in the release to deploy root directory.

**Step 2 - Server**

From the release to deploy root directory, perform the following commands (this is an example with a release called vX.Y.Z):

    cd releases/vX.Y.Z/
    cd app/config/
    ln -s ../../../../shared/config/parameters.yml parameters.yml
    cd ../../var
    ln -s ../../../shared/logs/ logs
    ln -s ../../../shared/sessions/ sessions
    ln -s ../../../shared/uploads/ uploads
    cd ..
    export COMPOSER_PROCESS_TIMEOUT=600
    ../../tools/composer.phar install
    php bin/console doctrine:migration:migrate
    php bin/console cache:clear --env=prod
    php bin/console cache:warmup --env=prod
    
**Step 3 - Server**

Return to the vhost main directory and update the "current" symlink to the new release:

     cd ../../
     rm current
     ln -s ./releases/vX.Y.Z/ current
     
**Step 4 - Server**

Depending on server configuration, it may be necessary to refresh the OPcache.


Cron tasks
========

The following URLs must be called as cron tasks, every hour:

    https://app.united-3dmakers.com/fr/order/track
    https://app.united-3dmakers.com/fr/cron/order/update-downloaded-to-validated

On Gandi Simple Hosting server, the crontab configuration is set in the following file:

    /srv/data/etc/cron/anacrontab

Its content would be:

    1@hourly 0 order_tracking wget https://app.united-3dmakers.com/fr/order/track
    1@hourly 0 order_update wget https://app.united-3dmakers.com/fr/cron/order/update-downloaded-to-validated

 
Development Tips: VueJS integration with encore-webpack
========

Source : https://symfony.com/doc/3.4/frontend.html
VueJS : https://vuejs.org/

**Install Dependencies** 

    yarn install

**Compile Asset Dev version (non minify and VueJS dev mode)**

    ./node_modules/.bin/encore dev

**Compile Asset Dev version & hot reload with Symfony server**

    ./node_modules/.bin/encore dev --watch

**Compile Asset for Production (Minify & uglify)**

    ./node_modules/.bin/encore production

**Same Shorter commands**

    yarn run encore dev
    yarn run encore dev --watch
    yarn run encore production