import logging
import time
import argparse
from logging.handlers import TimedRotatingFileHandler

from retrieve import Retriever
from processor import Processor
from cleaner import Cleaner
from settings import Settings


if __name__ == '__main__':

    """
    以下三行、×××な某社のおかげで急遽追加された機能のスイッチ
    """
    todayOnly: bool = True  # 当日のSALESレコード以外を捨てる
    takeB: bool = False  # BUDGETをインポートする
    takeS: bool = True  # SALESをインポートする

    """
    やれやれ
    """

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)

    formatter = '%(asctime)s %(module)s:%(lineno)s %(funcName)s [%(levelname)s]: %(message)s'

    streamhandler = logging.StreamHandler()
    streamhandler.setFormatter(logging.Formatter(formatter, datefmt='%H:%M:%S'))
    streamhandler.setLevel(logging.DEBUG)
    logger.addHandler(streamhandler)

    logfile: str = Settings.Local.logfile
    fileHandler = TimedRotatingFileHandler(logfile, when='D', interval=1, backupCount=7, utc=False)
    fileHandler.setFormatter(logging.Formatter(formatter, datefmt='%Y-%m-%d %H:%M:%S'))
    fileHandler.setLevel(logging.INFO)
    logger.addHandler(fileHandler)

    ftpserver: str = Settings.FTP.server
    username: str = Settings.FTP.username
    password: str = Settings.FTP.password
    defaultfolder: str = Settings.FTP.defaultfolder

    workpath: str = Settings.Local.workpath
    savepath: str = Settings.Local.savepath

    parser = argparse.ArgumentParser(description='CV auto importer version %s' % (Settings.INFO.version,))

    parser.add_argument('-o', '--oneshot', action='store_true', help='oneshot mode (not loop)')
    args = parser.parse_args()

    oneshot: bool = args.oneshot

    ftpDTP = Retriever(server=ftpserver, username=username, password=password, folder=defaultfolder, workpath=workpath)
    processor = Processor(workpath=workpath, savepath=savepath, todayOnly=todayOnly, takeB=takeB, takeS=takeS)  # 暫定措置
    cleaner = Cleaner()

    logger.info(msg='=== CV -> Salesreport autoimport system version %s was started' % (Settings.INFO.version))
    logger.info(msg='Notice! takeS=%s takeB=%s todayOnly=%s' % (takeS, takeB, todayOnly))

    counter: int = 0

    while True:

        try:
            cleaner.exec()

            counter += 1
            logger.info(msg='*** Start session[%d]' % (counter, ))
            topS = time.time()
            processor.prepareMatching()

            processor.wanted()

            for k, v in Settings.FTP.suffix.items():

                b = ftpDTP.readCSV(suffix=v)
                for src in b:
                    processor.importCV(filename=src, type=k)

            endS = time.time()
            logger.info(msg='--- end session over %.2f sec' % (endS-topS,))

            if oneshot:
                break
            else:
                time.sleep(Settings.Params.intervalSec)  # 要検討
        except KeyboardInterrupt as e:
            break
        else:
            pass