import logging
import time
from logging.handlers import TimedRotatingFileHandler

from withCV.retrieve import Retriever
from withCV.processor import Processor
from withCV.settings import Settings


if __name__ == '__main__':

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

    ftpDTP = Retriever(server=ftpserver, username=username, password=password, folder=defaultfolder, workpath=workpath)
    processor = Processor(workpath=workpath, savepath=savepath)

    logger.info(msg='=== CV -> Salesreport autoimport system version %s was started' % (Settings.INFO.version))

    counter: int = 0
    while True:

        counter += 1
        logger.info(msg='*** Start session[%d]' % (counter, ))
        processor.prepareMatching()

        processor.wanted()

        for k, v in Settings.FTP.suffix.items():

            b = ftpDTP.readCSV(suffix=v)
            for src in b:
                processor.importCV(filename=src, type=k)

        time.sleep(Settings.Params.intervalSec)  # 要検討