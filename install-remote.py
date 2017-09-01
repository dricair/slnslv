#! /usr/bin/python
# -*- coding: utf-8 -*-

import sys
import os
import os.path
import subprocess
import tempfile
import logging
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("--email", dest="email", default="", help="Email to send log message")
parser.add_argument("--debug", dest="debug", default=False, action="store_true", help="Activate debug messages")
parser.add_argument("--test",  dest="test",  default=False, action="store_true", help="Do not execute commands")

args = parser.parse_args()

log_level = logging.INFO
if args.debug: log_level = logging.DEBUG

logger = logging.getLogger("install")
logger.setLevel(log_level)

fh = logging.FileHandler('mail.log')
fh.setLevel(log_level)

ch = logging.StreamHandler()
ch.setLevel(log_level)

fh.setFormatter(logging.Formatter('%(asctime)s - %(levelname)-10s - %(message)s'))
ch.setFormatter(logging.Formatter('%(levelname)-10s %(message)s'))

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
ftp = "ftp://%s:%s@%s" % (username, password, server)

def run_command(cmd, logger):
  p = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
  (stdout, stderr) = ("", "")
  if not args.test:
    (stdout, stderr) = p.communicate()

  if type(stdout) is bytes: stdout = stdout.decode("utf-8")
  if type(stderr) is bytes: stderr = stderr.decode("utf-8")

  logger.debug("Command: " + subprocess.list2cmdline(cmd))
  logger.debug(stdout)
  if (stderr != ""): 
      if p.returncode:
          logger.error(stderr)
      else:
          logger.warning(stderr)

def synchronize(directory):
    (ldir, rdir) = (os.path.join(localdir, directory), os.path.join(remotedir, directory))
    logger.info("FTP synchronisation...")
    logger.info("  %s -> %s on %s@%s" % (ldir, rdir, username, server))

    commands = "mirror --no-symlinks --use-cache -e -R %s %s %s; quit" % (" ".join(map(lambda x: "--exclude %s" % x, excludes)),
                                                                          ldir, rdir)
    run_command([lftp, ftp, "-e", commands], logger)

synchronize("src/SLN/RegisterBundle")

logger.info("Répertoires logs et cache")
commands = "cd %s; rm -rf logs; rm -rf cache; mkdir logs; mkdir cache; chmod 777 logs; chmod 777 cache; quit" % os.path.join(remotedir, 'app')
run_command([lftp, ftp, "-e", commands], logger)


