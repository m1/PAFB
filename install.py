#!/usr/bin/env python

import argparse
import subprocess
import getpass
import os
import sys
from shutil import copyfile

parser = argparse.ArgumentParser(description='PAFB install')

parser.add_argument("-c", "--connection", 
                    help="What database engine to use", 
                    choices=['mysql', 'pgsql', 'mariadb'], 
                    type=str.lower, 
                    default='mysql')

parser.add_argument("-H", "--host", 
                    help="Database host",
                    default='localhost')

parser.add_argument("-u", "--username", 
                    help="Database user",
                    default='root')

parser.add_argument("-P", "--port", 
                    help="Database port",
                    default=3306)

parser.add_argument("-p", "--password", 
                    help="Database password")

args = parser.parse_args()

if not args.password:
    args.password = getpass.getpass('Database Password: ')

try:
    composer_global = subprocess.check_output("which composer", shell=True)
    composer_global = True
except subprocess.CalledProcessError:
    composer_global = False

    if not os.path.exists('composer.phar'):
        sys.exit('Install composer (https://getcomposer.org/download/) then run this installer again')

for framework in ['lumen', 'phalcon', 'slim', 'silex']:
    f = open('setup/nginx/PAFB_{}'.format(framework),'r')

    print 'Installing {}'.format(framework)

    filedata = f.read()
    f.close()

    filedata = filedata.replace("# DB_DATABASE_PLACEHOLDER #", "pafb;")
    filedata = filedata.replace("# DB_CONNECTION_PLACEHOLDER #", "{};".format(args.connection))
    filedata = filedata.replace("# DB_HOST_PLACEHOLDER #", "{};".format(args.host))
    filedata = filedata.replace("# DB_USERNAME_PLACEHOLDER #", "{};".format(args.username))
    filedata = filedata.replace("# DB_PASSWORD_PLACEHOLDER #", "{};".format(args.password))
    filedata = filedata.replace("# DB_PORT_PLACEHOLDER #", "{};".format(args.port))

    sites_available_path = '/etc/nginx/sites-available/PAFB_{}'.format(framework)
    
    f = open(sites_available_path,'w')
    f.write(filedata)
    f.close()

    sites_enabled_path = '/etc/nginx/sites-enabled/PAFB_{}'.format(framework)

    if os.path.exists(sites_enabled_path):
        os.remove(sites_enabled_path)

    subprocess.call(['sudo', 'ln', '-s', sites_available_path, sites_enabled_path], stdout=subprocess.PIPE)

    if framework != 'phalcon':
        cwd = '/var/www/PAFB/frameworks/{}'.format(framework)
        FNULL = open(os.devnull, 'w')

        if composer_global:
            subprocess.call(['composer', 'install'], cwd=cwd, stdout=FNULL, stderr=subprocess.STDOUT)
            subprocess.call(['composer', 'dumpautoload', '-o'], cwd=cwd, stdout=FNULL, stderr=subprocess.STDOUT)
        else: 
            subprocess.call(['php', '/var/www/pafb/composer.phar', 'install'], cwd=cwd, stdout=FNULL, stderr=subprocess.STDOUT)
            subprocess.call(['php', '/var/www/pafb/composer.phar', 'dumpautoload', '-o'], cwd=cwd, stdout=FNULL, stderr=subprocess.STDOUT)

subprocess.call(['sudo', 'service', 'nginx', 'reload'], stdout=subprocess.PIPE)