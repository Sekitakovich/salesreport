import os
import pathlib
from datetime import datetime as dt
import logging

from settings import Settings


class Cleaner(object):

    def __init__(self):

        self.path = pathlib.Path(Settings.Local.savepath)
        self.limitsec: int = Settings.Local.savedays * 60 * 60 * 24

        self.logger = logging.getLogger('Log')

    def exec(self):

        now = dt.now()
        for f in self.path.iterdir():
            ct: str = os.stat(f).st_ctime
            at = dt.fromtimestamp(ct)
            secs = (now-at).total_seconds()
            print('%s = %d' % (f.name, secs))
            if secs>=self.limitsec:
                os.remove(f)
                self.logger.debug(msg='deleted %s from saved files' % (f.name,))


if __name__ == '__main__':

    cleaner = Cleaner()

    cleaner.exec()

