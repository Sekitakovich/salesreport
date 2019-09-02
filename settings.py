from dataclasses import dataclass
from typing import Dict


@dataclass()
class Settings(object):

    class FTP(object):

        server: str = 'ap01.dtpnet.co.jp'  # notice, testserver

        username: str = 'sr168'
        password: str = 'sr#168'
        defaultfolder: str = 'sr168'

        suffixS: str = '_SALES'
        suffixB: str = '_BUDGET'

        suffix: Dict[str, str] = {
            'B': '_BUDGET',
            'S': '_SALES',
        }

    class Local(object):

        workpath: str = 'work'
        savepath: str = 'fromCV'
        savedays: int = 7

        logpath: str = 'CVlogs'
        logfile: str = '%s/server.log' % (logpath,)

    class Params(object):

        intervalSec: int = (60*5)

    class INFO(object):

        version: str = '1.01'
