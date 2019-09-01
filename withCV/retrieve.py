import ftplib
from typing import List, Dict
from dataclasses import dataclass
from datetime import datetime as dt
import logging


class Retriever(object):

    def __init__(self, *, server: str, username: str, password: str, folder: str, savepath: str):

        self.server: str = server
        self.username: str = username
        self.password: str = password
        self.folder: str = folder

        self.savepath: str = savepath
        self.logger = logging.getLogger('Log')

    def saveSales(self) -> list:

        """
        未処理の*_SALES.csvをファイル毎に取得し保存する
        """

        commer: List[str] = []

        try:
            with ftplib.FTP(host=self.server, user=self.username, passwd=self.password) as ftp:
                ftp.cwd(self.folder)  # cd to target folder
                src: List[str] = ftp.nlst('*_SALES.csv')  # do ls *.csv
                for filename in src:
                    log: str = ('%s/%s' % (self.savepath, filename))
                    with open(log, 'wb') as f:  # notice, encoding is Shift-Jis
                        ftp.retrbinary('RETR %s' % (filename,), f.write)
                        commer.append(filename)

        except (ftplib.error_perm, ftplib.error_proto, ftplib.error_reply, ftplib.error_temp, IOError) as e:
            print(e)
        else:
            pass

        return commer
