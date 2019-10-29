from dataclasses import dataclass
from typing import Dict


@dataclass()
class Settings(object):

    class FTP(object):

        # server: str = 'bsm05.dtpnet.co.jp'  # target
        server: str = 'ap01.dtpnet.co.jp'  # notice, testserver

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

        # dbname: str = 'hpfmaster'
        dbname: str = 'next'

        host: str = 'localhost'
        user: str = 'postgres'
        password: str = 'postgres'
        port: int = 5432

    class Local(object):

        workpath: str = 'work'
        savepath: str = 'fromCV'
        savedays: int = 7

        logpath: str = 'CVlogs'
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
