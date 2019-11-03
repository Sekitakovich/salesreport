from dataclasses import dataclass
from typing import Dict


@dataclass()
class Settings(object):

    class FTP(object):

        server: str = 'bsm05.dtpnet.co.jp'  # target  2019-10-30 19:00
        # server: str = 'ap01.dtpnet.co.jp'  # notice, testserver

        username: str = 'sr168'
        password: str = 'sr#168'
        defaultfolder: str = 'sr168/SEND'

        suffixS: str = '_SALES'
        suffixB: str = '_BUDGET'

        suffix: Dict[str, str] = {
            'B': '_BUDGET',
            'S': '_SALES',
        }

    class PostgreSQL(object):

        # dbname: str = 'hpfmaster'  # 2019-10-30 19:00
        dbname: str = 'next'

        host: str = 'localhost'
        user: str = 'postgres'
        password: str = 'postgres'
        port: int = 5432

    class Local(object):

        # basepath: str = 'withCV'
        #
        # workpath: str = '%s/work' % basepath
        # savepath: str = '%s/fins' % basepath
        savedays: int = 7
        #
        # logpath: str = '%s/logs' % basepath
        # logfile: str = '%s/server.log' % (logpath,)

        workpath: str = './work'
        savepath: str = './fins'
        logpath: str = './logs'
        logfile: str = '%s/server.log' % (logpath,)

    class Params(object):

        intervalSec: int = (60*10)

    class INFO(object):

        version: str = '1.01'

    class SMTP(object):

        # 以下HPFのそれを取得・設定すること

        server: str = 'mail.klabo.co.jp'
        port: int = 587  # submission port
        user: str = 'k-seki'
        password = 'Narikunn+2019'
