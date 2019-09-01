from typing import List, Dict
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
