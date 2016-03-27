#!/usr/bin/env bash
TYPO3_VERSION=6.2.19
debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
apt-get update
apt-get install -y apache2 libapache2-mod-php5 php5-mysql php5-gd imagemagick mysql-server vim curl phpunit parallel
wget -Otypo3.tar.gz -q "http://downloads.sourceforge.net/project/typo3/TYPO3%20Source%20and%20Dummy/TYPO3%20${TYPO3_VERSION}/typo3_src-${TYPO3_VERSION}.tar.gz"
rm -f /var/www/html/index.html
tar -xzf typo3.tar.gz -C /var/www/html --strip-components=1
touch /var/www/html/FIRST_INSTALL
touch /var/www/html/typo3conf/ENABLE_INSTALL_TOOL
chown -R www-data:www-data /var/www/html
ln -s typo3 /var/www/html/typo3_src
echo "upload_max_filesize = 10240k" > /etc/php5/apache2/conf.d/30-helfenkannjeder.ini
echo "post_max_size = 10240k" >> /etc/php5/apache2/conf.d/30-helfenkannjeder.ini
echo "max_execution_time = 240" >> /etc/php5/apache2/conf.d/30-helfenkannjeder.ini
echo "zend.multibyte = On" >> /etc/php5/apache2/conf.d/30-helfenkannjeder.ini
cp /etc/php5/apache2/conf.d/30-helfenkannjeder.ini /etc/php5/cli/conf.d/30-helfenkannjeder.ini
/etc/init.d/apache2 reload

# Install TYPO3
echo "drop database if exists helfenkannjeder"|mysql -u root -proot
COOKIE_FILE=$(mktemp)
RESULT_FILE=$(mktemp)
curl 'http://localhost/typo3/install/' -L -s -c $COOKIE_FILE > $RESULT_FILE
curl 'http://localhost/typo3/sysext/install/Start/Install.php' -L -s -g -H 'Content-Type: application/x-www-form-urlencoded' --data 'install%5Baction%5D=environmentAndFolders&install%5Bset%5D=execute' -b $COOKIE_FILE -c $COOKIE_FILE > $RESULT_FILE
TOKEN=$(cat $RESULT_FILE |grep token |sed -r "s/.*value=\"([0-9a-f]+)\".*/\\1/g"|head -1)
curl 'http://localhost/typo3/sysext/install/Start/Install.php?install[redirectCount]=4&install[context]=standalone&install[controller]=step&install[action]=databaseConnect' -L -s -g -H 'Content-Type: application/x-www-form-urlencoded' --data 'install%5Bcontroller%5D=step&install%5Baction%5D=databaseConnect&install%5Btoken%5D='$TOKEN'&install%5Bcontext%5D=standalone&install%5Bset%5D=execute&install%5Bvalues%5D%5Busername%5D=root&install%5Bvalues%5D%5Bpassword%5D=root&install%5Bvalues%5D%5Bhost%5D=localhost&install%5Bvalues%5D%5Bport%5D=3306&install%5Bvalues%5D%5Bsocket%5D=' -b $COOKIE_FILE -c $COOKIE_FILE > $RESULT_FILE
TOKEN=$(cat $RESULT_FILE |grep token |sed -r "s/.*value=\"([0-9a-f]+)\".*/\\1/g"|head -1)
curl 'http://localhost/typo3/sysext/install/Start/Install.php?install[redirectCount]=2&install[context]=standalone&install[controller]=step&install[action]=databaseSelect' -L -s -g -H 'Content-Type: application/x-www-form-urlencoded' --data 'install%5Bcontroller%5D=step&install%5Baction%5D=databaseSelect&install%5Btoken%5D='$TOKEN'&install%5Bcontext%5D=standalone&install%5Bset%5D=execute&install%5Bvalues%5D%5Bexisting%5D=&install%5Bvalues%5D%5Btype%5D=new&install%5Bvalues%5D%5Bnew%5D=helfenkannjeder' -b $COOKIE_FILE -c $COOKIE_FILE > $RESULT_FILE
TOKEN=$(cat $RESULT_FILE |grep token |sed -r "s/.*value=\"([0-9a-f]+)\".*/\\1/g"|head -1)
curl 'http://localhost/typo3/sysext/install/Start/Install.php?install[redirectCount]=3&install[context]=standalone&install[controller]=step&install[action]=databaseData' -L -s -g -H 'Content-Type: application/x-www-form-urlencoded' --data 'install%5Bcontroller%5D=step&install%5Baction%5D=databaseData&install%5Btoken%5D='$TOKEN'&install%5Bcontext%5D=standalone&install%5Bset%5D=execute&install%5Bvalues%5D%5Busername%5D=&install%5Bvalues%5D%5Bpassword%5D=password&install%5Bvalues%5D%5Bsitename%5D=HelfenKannJeder' -b $COOKIE_FILE -c $COOKIE_FILE > $RESULT_FILE

echo "insert into be_users (username, password, admin) values ('_cli_lowlevel', '$P$CH37CZNGxYDKIF54kOjl7rh5BK7iCo/', 0);" |mysql -uroot -proot helfenkannjeder
echo "insert into be_users (username, password, admin) values ('_cli_phpunit', '$P$CH37CZNGxYDKIF54kOjl7rh5BK7iCo/', 0);" |mysql -uroot -proot helfenkannjeder

# Functions
function ext_install_uri {
	NAME=$1
	TMPFILE=$(mktemp --suffix=.zip)
	wget -q -O$TMPFILE $2
	mkdir /var/www/html/typo3conf/ext/$NAME/
	unzip $TMPFILE -d /var/www/html/typo3conf/ext/$NAME/
	/var/www/html/typo3/cli_dispatch.phpsh extbase extension:install $NAME
}

function ext_install {
	/var/www/html/typo3/cli_dispatch.phpsh extbase extensionapi:fetch $1
	/var/www/html/typo3/cli_dispatch.phpsh extbase extensionapi:install $1
}



# Install Theme
TMPFILE=$(mktemp --suffix=.zip)
wget -q -O$TMPFILE http://extensions.helfenkannjeder.de/design.zip
unzip $TMPFILE -d /var/www/html/fileadmin/

mysql -uroot -proot helfenkannjeder < /vagrant/Resources/Private/Sql/data.sql

# Install extensions
ext_install_uri coreapi https://typo3.org/extensions/repository/download/coreapi/1.3.0/zip/
/var/www/html/typo3/cli_dispatch.phpsh extbase extensionapi:updatelist
ext_install phpunit
ext_install vhs
ext_install mvc_extjs
ext_install pagepath
ext_install sr_freecap
ext_install t3jquery
ext_install_uri qu_base http://extensions.helfenkannjeder.de/qu_base.zip

# Install HelfenKannJeder extension
/var/www/html/typo3/cli_dispatch.phpsh extbase extensionapi:install helfen_kann_jeder
