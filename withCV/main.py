import logging

from withCV.retrieve import Retriever
from withCV.processor import Processor


if __name__ == '__main__':

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)

    # formatter = '%(asctime)s [%(levelname)s]: %(message)s'
    # filehandler = TimedRotatingFileHandler(Common.logFile, when='D', interval=1, backupCount=7, utc=True)
    # filehandler.setFormatter(logging.Formatter(formatter, datefmt=Common.Daily.tsFormat))
    # filehandler.suffix = '%Y-%m-%d'
    # filehandler.setLevel(logging.INFO)
    # logger.addHandler(filehandler)

    formatter = '%(asctime)s %(module)s:%(funcName)s [%(levelname)s]: %(message)s'
    streamhandler = logging.StreamHandler()
    streamhandler.setFormatter(logging.Formatter(formatter, datefmt='%H:%M:%S'))
    streamhandler.setLevel(logging.DEBUG)
    logger.addHandler(streamhandler)

    server: str = 'ap01.dtpnet.co.jp'  # notice, testserver
    username: str = 'sr168'
    password: str = 'sr#168'
    folder: str = 'sr168'

    workpath: str = 'work'
    savepath: str = 'logs'

    ftpDTP = Retriever(server=server, username=username, password=password, folder=folder, workpath=workpath)
    processor = Processor(workpath=workpath, savepath=savepath)

    processor.prepareMatching()

    b = ftpDTP.readCSV(type='B')
    for src in b:
        processor.importCV(src=src, type='B')

    s = ftpDTP.readCSV(type='S')
    for src in s:
        processor.importCV(src=src, type='S')