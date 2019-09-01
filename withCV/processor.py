from typing import List, Dict
from datetime import datetime as dt
import logging
import shutil
from dataclasses import dataclass
from withCV.pglib import PGLib


@dataclass()
class DailyMembers(object):

    yyyymmdd: dt  # 計上日付
    # shop: str  # 得意先コード
    member: int  # 新規顧客獲得数
    visitor: int  # 来客数
    etime: dt  # 出力日時
    result: int  # 売上金額
    book: int  # 取り置き金額
    booktotal: int  # 取り置き残高
    note: str  # メモ
    mlot: int  # 顧客買い上げ数
    myen: int  # 顧客買い上げ金額
    welcome: int  # 接客回数


class Processor(object):

    def __init__(self, *, workpath: str, savepath: str):

        self.workpath: str = workpath
        self.savepath: str = savepath
        self.logger = logging.getLogger('Log')

        self.pglib = PGLib()
        self.dateformat = '%Y-%m-%d'
        self.timeformat = '%Y-%m-%d %H:%M:%S'

        self.matchTable: Dict[str, int] = {}

    def findDaily(self, *, shopID: int, yyyymmdd: str) -> int:

        dailyID: int = 0

        query: str = "select id from daily where vf=true and shop=%d and yyyymmdd='%s'" % (shopID, yyyymmdd)
        # self.logger.debug(msg=query)
        result = self.pglib.select(query=query)
        try:
            dailyID = int(result[0]['id'])
        except (IndexError, ValueError) as e:
            # self.logger.error(msg=e)
            pass

        return dailyID

    def prepareMatching(self):

        self.matchTable.clear()
        query: str = "select dtp,id from shop where vf=true and dtp<>'' order by dtp asc"
        result = self.pglib.select(query=query)
        for shop in result:
            self.matchTable[shop['dtp']] = shop['id']

        # self.logger.debug(msg=self.matchTable)

    def saveBudget(self, *, item: list):

        # self.logger.debug(msg=item)

        try:
            yyyymmdd: str = dt(int(item[0][0:4]), int(item[0][4:6]), int(item[0][6:8])).strftime(self.dateformat)
            shop: str = item[1]
            target = int(item[2])
        except (IndexError, ValueError, UnicodeDecodeError) as e:
            self.logger.error(msg=e)
            pass
        else:
            if shop in self.matchTable.keys():
                shopID: int = self.matchTable[shop]
                dailyID = self.findDaily(shopID=shopID, yyyymmdd=yyyymmdd)
                kv = {'target': target}
                if dailyID == 0:
                    kv['shop'] = shopID
                    kv['yyyymmdd'] = yyyymmdd
                self.pglib.update(table='daily', kv=kv, id=dailyID)
            else:  # 店舗未登録
                self.logger.error(msg='shop [%s] not found' % (shop,))

    def saveSales(self, *, item: list):

        # self.logger.debug(msg=item)

        try:
            yyyymmdd: str = dt(int(item[0][0:4]), int(item[0][4:6]), int(item[0][6:8])).strftime(self.dateformat)
            shop: str = item[1]
            member: int = int(item[2])
            visitor: int = int(item[3])
            etime: str = dt(int(item[4][0:4]), int(item[4][4:6]), int(item[4][6:8]),
                           hour=int(item[4][8:10]), minute=int(item[4][10:12]), second=int(item[4][12:14])).strftime(self.timeformat)
            result: int = int(item[5])
            book: int = int(item[6])
            booktotal: int = int(item[7])
            note: str = item[8]  # notice!
            mlot: int = int(item[9])
            myen: int = int(item[10])
            welcome: int = int(item[11])
            pass
        except (IndexError, ValueError, UnicodeDecodeError) as e:
            self.logger.error(msg=e)
        else:
            if shop in self.matchTable.keys():
                shopID: int = self.matchTable[shop]
                dailyID = self.findDaily(shopID=shopID, yyyymmdd=yyyymmdd)
                if dailyID:
                    kv = {
                        'yyyymmdd': yyyymmdd,
                        'shop': shopID,
                        'member': member,
                        'visitor': visitor,
                        'etime': etime,
                        'result': result,
                        'book': book,
                        # 'booktotal': booktotal,
                        'mlot': mlot,
                        'myen': myen,
                        'welcome': welcome,
                        'note': note,  # notice
                        'entered': 1,
                        'open': 1,
                    }
                    self.pglib.update(table='daily', kv=kv, id=dailyID)
                    # print(kv)
                else:  # daily未登録
                    # self.logger.error(msg='shop [%s] not found' % (shop,))
                    pass
            else:  # 未登録店舗
                self.logger.error(msg='shop [%s] not found' % (shop,))
                pass

    def importCV(self, *, src: str, type: str = 'S'):

        self.logger.debug(msg='Processing %s' % (src,))

        workpath: str = '%s/%s' % (self.workpath, src)
        savepath: str = '%s/%s' % (self.savepath, src)

        try:
            with open(workpath, encoding='shift_jis') as f:
                line: List[str] = f.readlines()
        except (IOError,) as e:
            self.logger.error(msg=e)
        else:
            for index, text in enumerate(line, 1):
                csv: List[str] = text.rstrip('\n').split(',')
                # self.logger.debug(msg='Line[%04d] %s' % (index, csv))
                if type == 'S':
                    self.saveSales(item=csv)
                else:
                    self.saveBudget(item=csv)

            shutil.move(src=workpath, dst=savepath)
            pass
