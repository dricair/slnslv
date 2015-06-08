#! /usr/bin/python
# -*- coding: latin-1 -*-

import sys
import os
import os.path
import subprocess
import tempfile
import re
import ftplib
import httplib
import hashlib
import urllib
import optparse
import smtplib
import socket
import logging

from email.MIMEText import MIMEText

parser = optparse.OptionParser()
parser.add_option("-e", "--email", dest="email", default="", type="string",
                  help="Email to send log message")

(options, args) = parser.parse_args()

print __file__

updateFtp = True
updateSql = False

logger = logging.getLogger("install")
logger.setLevel(logging.DEBUG)

fh = logging.FileHandler('mail.log')
fh.setLevel(logging.DEBUG)

ch = logging.StreamHandler()
ch.setLevel(logging.DEBUG)

fh.setFormatter(logging.Formatter('%(asctime)s - %(name)s  %(levelname)-10s - %(message)s'))
ch.setFormatter(logging.Formatter('%(name)s %(levelname)-10s %(message)s'))

logger.addHandler(ch)
logger.addHandler(fh)


server = "ftp.stadelaurentinnatation.fr"
username = "stadelau"
password = "slv06700"
sqlfile = "stadelaurentinnatation.sql"
localdir = os.path.abspath(os.path.dirname(__file__))
localsql = os.path.join(localdir, sqlfile)
remotesite = "www.stadelaurentinnatation.fr"
remotedir = "/www/register" # Beware not ending with /
excludes = [".git/", ".gitignore", "TODO", "README", os.path.basename(__file__), "mail.log",  "composer.*", 
            "app/config/parameters.yml", "app/cache/", "app/logs/"]
lftp = "/usr/bin/lftp"
updatepage = "/update-db.php"
parameters_remote = "app/config/parameters-remote.yml"
ftp = "ftp://%s:%s@%s" % (username, password, server)

def run_command(cmd, logger):
  (stdout, stderr) = subprocess.Popen(cmd, stdout=subprocess.PIPE,
                                      stderr=subprocess.PIPE).communicate()

  logger.debug("Command: " + subprocess.list2cmdline(cmd))
  logger.debug(stdout,)
  if (stderr != ""): logger.error(stderr)


# Synchronize FTP
if updateFtp:
  logger.info("FTP synchronisation...")
  logger.info("  %s -> %s on %s@%s" % (localdir, remotedir, username, server))
  commands = "mirror --no-symlinks --use-cache -e -R %s %s %s; quit" % (" ".join(map(lambda x: "--exclude %s" % x, excludes)),
                                                                        localdir, remotedir)
  run_command([lftp, ftp, "-e", commands], logger)

  logger.info("Répertoires logs et cache")
  commands = "cd %s; rm -rf logs; rm -rf cache; mkdir logs; mkdir cache; chmod 777 logs; chmod 777 cache; quit" % os.path.join(remotedir, 'app')
  run_command([lftp, ftp, "-e", commands], logger)

  logger.info("Fichier parameters.yml (From %s)" % parameters_remote)
  commands = "cd %s; put -O app/config %s -o parameters.yml; quit" % (remotedir, parameters_remote)
  run_command([lftp, ftp, "-e", commands], logger)


# Change DB file (replace)
if updateSql:
  logger.info("SQL DB replace...")
  sqlin = open(localsql, "r")
  sqlout = tempfile.NamedTemporaryFile(mode="w+", suffix=".sql", delete=False)
  msg("Tmp file: " + sqlout.name)

  for line in sqlin.readlines():
      sqlout.write(line.replace("solidarsport.free.fr", "www.solidarsport.fr"))
  sqlout.close()



