import logging

from withCV.retrieve import Retriever
from withCV.processor import Processor


if __name__ == '__main__':

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)
    handler = logging.StreamHandler()
    handler.setLevel(logging.DEBUG)
    logger.addHandler(handler)

    server: str = 'ap01.dtpnet.co.jp'  # notice, testserver
    username: str = 'sr168'
    password: str = 'sr#168'
    folder: str = 'sr168'

    workpath: str = 'work'
    savepath: str = 'logs'

    ftpDTP = Retriever(server=server, username=username, password=password, folder=folder, workpath=workpath)
    processor = Processor(workpath=workpath, savepath=savepath)

    csv = ftpDTP.readCSV()
    for src in csv:
        processor.importSales(src=src)