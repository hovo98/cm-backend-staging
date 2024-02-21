timestamp = $(shell date +%s)

.deploy:
	@/bin/echo ============================================================================================= \
	&& /bin/echo Started cm-server deployment for ${ENV_NAME} at `date +"%Y-%m-%d %T"` \
	&& /bin/echo ============================================================================================= \
	&& /bin/echo Checking out ${BRANCH} \
	&& git checkout -f ${BRANCH} \
	&& git clean -d -f . \
	&& git pull -f \
	&& /bin/echo Copying files \
	&& cp -f -a /home/deploy/financelobby/${ENV_NAME}/repositories/cm-server/. /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server-$(timestamp) \
	&& cp -f -a /home/deploy/financelobby/${ENV_NAME}/conf/cm-server/.env /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server-$(timestamp)/.env \
	&& /bin/echo Installing composer dependencies \
	&& cd /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server-$(timestamp) \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini /usr/bin/composer install \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini artisan optimize:clear \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini artisan migrate --force \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini artisan passport:keys \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini artisan telescope:install \
	&& /usr/bin/php7.4 -n -c /etc/php/7.4/fpm/financelobby/${ENV_NAME}/php.ini artisan telescope:publish \
	&& /bin/echo Creating symlink \
	&& ln -sfn /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server-$(timestamp) /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server \
	&& sudo service php7.4-fpm-financelobby-${ENV_NAME}-server restart \
	&& /bin/echo Setting permissions \
	&& sudo chown -R www-data:www-data /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server/ \
	&& sudo chown deploy:deploy /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server/ \
	&& /bin/echo Reseting Laravel workers \
	&& supervisorctl restart ${ENV_NAME}-queue-worker:* \
	&& /bin/echo ============================================================================================= \
	&& /bin/echo Finished cm-server deployment for ${ENV_NAME} at `date +"%Y-%m-%d %T"` \
	&& /bin/echo Deployed to: /home/deploy/financelobby/${ENV_NAME}/www-data/cm-server/cm-server-$(timestamp) \
	&& /bin/echo =============================================================================================

deploy-dev: ENV_NAME=dev
deploy-dev: BRANCH=develop
deploy-dev: .deploy

deploy-pentest: ENV_NAME=dev
deploy-pentest: BRANCH=release/mvp
deploy-pentest: .deploy

deploy-stage: ENV_NAME=stage
deploy-stage: BRANCH=release/mvp
deploy-stage: .deploy

deploy-stage-premvp: ENV_NAME=stage-premvp
deploy-stage-premvp: BRANCH=release/pre-mvp
deploy-stage-premvp: .deploy