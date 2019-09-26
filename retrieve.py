import ftplib
from typing import List
import logging


class Retriever(object):

    def __init__(self, *, server: str, username: str, password: str, folder: str, workpath: str):

        self.server: str = server
        self.username: str = username
        self.password: str = password
        self.folder: str = folder

        self.workpath: str = workpath
        self.logger = logging.getLogger('Log')

    def readCSV(self, *, suffix: str) -> list:

        """
        未処理の**.csvをファイル毎に取得し保存する
        """

        commer: List[str] = []

        try:
            with ftplib.FTP(host=self.server, user=self.username, passwd=self.password) as ftp:
                ftp.cwd(self.folder)  # cd to target folder
                news: List[str] = ftp.nlst('*%s.csv' % suffix)  # do ls *.csv
                if len(news):
                    for filename in news:
                        csv: str = ('%s/%s' % (self.workpath, filename))
                        with open(csv, 'wb') as f:  # notice, encoding is Shift-Jis
                            ftp.retrbinary('RETR %s' % (filename,), f.write)  # ここで保存
                            commer.append(filename)
                            ftp.delete(filename=filename)
                            self.logger.info(msg='+++ retrieved and deleted %s from %s' % (filename, self.server))
                else:
                    self.logger.debug(msg='??? no %s files' % (suffix,))

        except (ftplib.error_perm, ftplib.error_proto, ftplib.error_reply, ftplib.error_temp, IOError) as e:
            self.logger.error(msg=e)
        else:
            pass

        return commer

