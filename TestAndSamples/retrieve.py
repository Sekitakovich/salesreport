import ftplib
from typing import List, Dict
from dataclasses import dataclass
from datetime import datetime as dt
import logging


class Processor(object):

    def __init__(self, *, savepath: str):

        self.savepath: str = savepath
        self.logger = logging.getLogger('Log')

    def readSales(self, *, src: str):

        self.logger.debug(msg='Processing %s' % (src,))
        fullpath: str = '%s/%s' % (self.savepath, src)
        try:
            with open(fullpath, encoding='shift_jis') as f:
                line: List[str] = f.readlines()
                for index, text in enumerate(line, 1):
                    csv: List[str] = text.rstrip('\n').split(',')
                    self.logger.debug(msg='Line[%04d] %s' % (index, csv))
        except (IOError,) as e:
            self.logger.error(msg=e)
        else:
            pass


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

    # def takeSales(self) -> SALES:
    #
    #     report = SALES()
    #     commer: List[str] = []
    #
    #     try:
    #         topTS: dt = dt.now()
    #         with ftplib.FTP(host=self.server, user=self.username, passwd=self.password) as ftp:
    #             ftp.cwd(self.folder)  # cd to target folder
    #             src: List[str] = ftp.nlst('*_SALES.csv')  # do ls *.csv
    #             for filename in src:
    #                 # print(filename)
    #                 log: str = ('%s/%s' % (self.savepath, filename))
    #                 with open(log, 'wb') as f:  # notice, encoding is Shift-Jis
    #                     ftp.retrbinary('RETR %s' % (filename,), f.write)
    #                     commer.append(filename)
    #         endTS: dt = dt.now()
    #     except (ftplib.error_perm, ftplib.error_proto, ftplib.error_reply, ftplib.error_temp, PermissionError, FileNotFoundError, ValueError) as e:
    #         print(e)
    #     else:
    #         print('Retrieved %d files at %s (%d secs)' % (len(commer), topTS, (endTS-topTS).total_seconds()))
    #         for src in commer:
    #             print('Processing %s' % (src,))
    #             fullpath = '%s/%s' % (self.savepath, src)
    #             with open(fullpath, encoding='shift_jis') as f:
    #                 line: List[str] = f.readlines()
    #                 for text in line:
    #                     csv: List[str] = text.rstrip('\n').split(',')
    #                     report.csv.append(csv)
    #
    #         pass
    #
    #     return report


if __name__ == '__main__':

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)
    handler = logging.StreamHandler()
    handler.setLevel(logging.DEBUG)
    logger.addHandler(handler)

    server: str = 'ap01.dtpnet.co.jp'
    username: str = 'sr168'
    password: str = 'sr#168'
    folder: str = 'sr168'

    savepath: str = 'logs'

    ftpDTP = Retriever(server=server, username=username, password=password, folder=folder, savepath=savepath)
    processor = Processor(savepath=savepath)

    csv = ftpDTP.saveSales()
    for src in csv:
        processor.readSales(src=src)