PHP_VERSION=8.4

sudo apt install -y $(awk -v v="$PHP_VERSION" '{print "php"v"-"$1}' extensions.txt)
