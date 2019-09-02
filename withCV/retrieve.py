import ftplib
from typing import List, Dict
from dataclasses import dataclass
from datetime import datetime as dt
import logging


class Retriever(object):

    def __init__(self, *, server: str, username: str, password: str, folder: str, workpath: str):

        self.server: str = server
        self.username: str = username
        self.password: str = password
        self.folder: str = folder

        self.workpath: str = workpath
        self.logger = logging.getLogger('Log')

    def readCSV(self, *, type: str = 'S') -> list:

        """
        未処理の*_SALES.csvをファイル毎に取得し保存する
        """

        commer: List[str] = []

        try:
            with ftplib.FTP(host=self.server, user=self.username, passwd=self.password) as ftp:
                self.logger.debug(msg='+++ Connected to FTP:%s' % (self.server,))
                ftp.cwd(self.folder)  # cd to target folder
                suffix: str = '_SALES' if type == 'S' else '_BUDGET'
                src: List[str] = ftp.nlst('*%s.csv' % suffix)  # do ls *.csv
                if len(src):
                    for filename in src:
                        csv: str = ('%s/%s' % (self.workpath, filename))
                        with open(csv, 'wb') as f:  # notice, encoding is Shift-Jis
                            ftp.retrbinary('RETR %s' % (filename,), f.write)
                            commer.append(filename)
                            self.logger.debug(msg='+++ retrieved %s' % (filename,))
                else:
                    self.logger.warning(msg='??? no %s files' % (suffix,))

        except (ftplib.error_perm, ftplib.error_proto, ftplib.error_reply, ftplib.error_temp, IOError) as e:
            self.logger.error(msg=e)
        else:
            pass

        return commer

