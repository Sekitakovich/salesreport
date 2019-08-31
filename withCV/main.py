import logging


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

    savepath: str = 'logs'

    ftpDTP = Retriever(server=server, username=username, password=password, folder=folder, savepath=savepath)
    processor = Processor(savepath=savepath)

    csv = ftpDTP.saveSales()
    for src in csv:
        processor.readSales(src=src)