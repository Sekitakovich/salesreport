import ftplib
from typing import List
from dataclasses import dataclass
from datetime import datetime as dt


@dataclass()
class DTP(object):

    at: dt = dt.now()


class Retriever(object):

    def __init__(self, *, server: str, username: str, password: str, folder: str):

        self.server: str = server
        self.username: str = username
        self.password: str = password
        self.folder: str = folder

        self.logs = 'logs'

    def exec(self) -> list:
        commer: List[str] = []
        try:
            topTS: dt = dt.now()
            with ftplib.FTP(host=self.server, user=self.username, passwd=self.password) as ftp:
                ftp.cwd(self.folder)  # cd to target folder
                src: List[str] = ftp.nlst('*.csv')  # do ls *.csv
                for filename in src:
                    # print(filename)
                    log: str = ('%s/%s' % (self.logs, filename))
                    with open(log, 'wb') as f:  # notice, encoding is Shift-Jis
                        ftp.retrbinary('RETR %s' % (filename,), f.write)
                        commer.append(filename)
            endTS: dt = dt.now()
        except (ftplib.error_perm, ftplib.error_proto, ftplib.error_reply, ftplib.error_temp, PermissionError, FileNotFoundError, ValueError) as e:
            print(e)
        else:
            print('Retrieved %d files at %s (%d secs)' % (len(commer), topTS, (endTS-topTS).total_seconds()))

            for src in commer:
                print('Processing %s' % (src,))
                fullpath = '%s/%s' % (self.logs, src)
                with open(fullpath, encoding='utf-8') as f:
                    line: List[str] = f.readlines()
                    for text in line:
                        csv: List[str] = text.rstrip('\n').split(',')
                        print(csv)


            pass

        return commer


if __name__ == '__main__':

    server: str = 'ap01.dtpnet.co.jp'
    username: str = 'sr168'
    password: str = 'sr#168'
    folder: str = 'RECV'

    ftpDTP = Retriever(server=server, username=username, password=password, folder=folder)
    commer: list = ftpDTP.exec()

    # print(commer)