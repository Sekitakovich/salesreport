from typing import List, Dict
from datetime import datetime as dt
import logging
import shutil
# from dataclasses import dataclass

from pglib import PGLib
from notify import NotifyMail


# @dataclass()
# class DailyMembers(object):
#
#     yyyymmdd: dt  # 計上日付
#     # shop: str  # 得意先コード
#     member: int  # 新規顧客獲得数
#     visitor: int  # 来客数
#     etime: dt  # 出力日時
#     result: int  # 売上金額
#     book: int  # 取り置き金額
#     booktotal: int  # 取り置き残高
#     note: str  # メモ
#     mlot: int  # 顧客買い上げ数
#     myen: int  # 顧客買い上げ金額
#     welcome: int  # 接客回数
#

class Processor(object):

    def __init__(self, *, workpath: str, savepath: str):

        self.workpath: str = workpath
        self.savepath: str = savepath
        self.logger = logging.getLogger('Log')

        self.pglib = PGLib()
        self.nofity = NotifyMail()
        self.dateformat = '%Y-%m-%d'
        self.timeformat = '%Y-%m-%d %H:%M:%S'

        self.cvencoding = 'cp932'
        # self.cvtimeformat = '%Y/%m/%d %H:%M:%S'
        self.cvtimeformat = '%Y%m%d%H%M%S'
        self.ourtimeformat = '%Y-%m-%d %H:%M:%S'
        self.errorList: List[str] = []

        self.matchTable: Dict[str, int] = {}

    def wanted(self):
        """
        未処理のdailyを捜索・修復する
        """
        query: str = "select id,dtp from daily where vf=true and shop=0 and dtp<>'' order by id asc"
        member: List[dict] = self.pglib.select(query=query)
        for target in member:
            # print(target)
            dtp: str = target['dtp']
            if dtp in self.matchTable.keys():
                shopID = self.matchTable[dtp]
                udate: str = dt.now().strftime(self.timeformat)
                kv = {
                    'shop': shopID,
                    'udate': udate,
                }
                result: int = self.pglib.update(table='daily', kv=kv, id=target['id'])
                if result:
                    self.logger.info(msg='daily[%d] shop was set to %d from %s' % (result, shopID, dtp))
            else:
                # print(dtp)
                pass

    def findDaily(self, *, shopID: int, yyyymmdd: str, dtp: str) -> int:

        dailyID: int = 0

        query: str = "select id from daily where vf=true and shop=%d and yyyymmdd='%s' and dtp='%s'" % (shopID, yyyymmdd, dtp)
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

    def saveBudget(self, *, item: list) -> bool:

        udate: str = dt.now().strftime(self.timeformat)
        completed: bool = False

        try:
            yyyymmdd: str = dt(int(item[0][0:4]), int(item[0][4:6]), int(item[0][6:8])).strftime(self.dateformat)
            shop: str = item[1]
            target: int = int(item[2])
            last: int = int(item[3])  # add
        except (IndexError, ValueError, UnicodeDecodeError) as e:
            self.logger.error(msg=e)
            pass
        else:

            shopID = 0
            if shop in self.matchTable.keys():
                shopID: int = self.matchTable[shop]
            else:  # 店舗未登録
                self.logger.warning(msg='shop [%s] was not found' % (shop,))

            dailyID = self.findDaily(shopID=shopID, yyyymmdd=yyyymmdd, dtp=shop)
            if dailyID == 0:
                self.logger.warning(msg='daily [%s:%s] was not found' % (shop, yyyymmdd))
                kv = {'dtp': shop}
                dailyID = self.pglib.update(table='daily', kv=kv, id=0)

            kv = {'target': target, 'last': last, 'shop': shopID, 'yyyymmdd': yyyymmdd, 'udate': udate}
            if self.pglib.update(table='daily', kv=kv, id=dailyID):
                completed = True

        return completed

    def saveSales(self, *, item: list) -> bool:

        udate: str = dt.now().strftime(self.timeformat)
        completed: bool = False

        try:
            yyyymmdd: str = dt(int(item[0][0:4]), int(item[0][4:6]), int(item[0][6:8])).strftime(self.dateformat)
            shop: str = item[1]
            member: int = int(item[2])
            visitor: int = int(item[3])
            etime: str = dt.strptime(item[4], self.cvtimeformat).strftime(self.ourtimeformat)
            # etime: str = dt(int(item[4][0:4]), int(item[4][4:6]), int(item[4][6:8]),
            #                hour=int(item[4][8:10]), minute=int(item[4][10:12]), second=int(item[4][12:14])).strftime(self.timeformat)
            result: int = int(item[5])
            book: int = int(item[6])
            booktotal: int = int(item[7])
            # note: str = item[8]  # notice!
            note: str = self.pglib.pg_escape_string(src=item[8])
            mlot: int = int(item[9])
            myen: int = int(item[10])
            welcome: int = int(item[11])
            pass
        except (IndexError, ValueError, UnicodeDecodeError) as e:
            self.logger.error(msg=e)
        else:
            if shop:
                shopID = 0
                if shop in self.matchTable.keys():
                    shopID: int = self.matchTable[shop]
                else:  # 店舗未登録
                    self.logger.warning(msg='shop [%s] was not found' % (shop,))
                    self.errorList.append('shop [%s] was not found' % (shop,))

                dailyID = self.findDaily(shopID=shopID, yyyymmdd=yyyymmdd, dtp=shop)
                if dailyID == 0:
                    self.logger.warning(msg='daily [%s:%s] was not found' % (shop, yyyymmdd))
                    self.errorList.append('daily [%s:%s] was not found' % (shop, yyyymmdd))

                kv = {
                    'yyyymmdd': yyyymmdd,
                    'shop': shopID,
                    'member': member,
                    'visitor': visitor,
                    'etime': etime,
                    'result': result,
                    'book': book,
                    'booktotal': booktotal,
                    'mlot': mlot,
                    'myen': myen,
                    'welcome': welcome,
                    'note': note,  # notice
                    'entered': 1,
                    'open': 1,
                    'dtp': shop,
                    'udate': udate,
                }

                if self.pglib.update(table='daily', kv=kv, id=dailyID):
                    completed = True
            else:
                self.logger.critical(msg='void this cause no shopcode')

        return completed

    def importCV(self, *, filename: str, type: str):

        self.errorList.clear()

        workpath: str = '%s/%s' % (self.workpath, filename)
        savepath: str = '%s/%s' % (self.savepath, filename)

        try:
            with open(workpath, encoding=self.cvencoding) as f:
                line: List[str] = f.readlines()
        except (IOError,) as e:
            self.logger.error(msg=e)
        else:
            self.logger.debug(msg='Processing %s (%d lines)' % (filename, len(line)))

            erros: int = 0
            for index, text in enumerate(line, 1):
                csv: List[str] = text.rstrip('\n').split(',')
                # self.logger.debug(msg='Line[%04d] %s' % (index, csv))
                if type == 'S':
                    if self.saveSales(item=csv) is False:
                        erros += 1
                else:
                    if self.saveBudget(item=csv) is False:
                        erros += 1

            if erros == 0:
                shutil.move(src=workpath, dst=savepath)
            else:
                self.logger.warning(msg='%d erros was occured' % (erros,))

            if len(self.errorList):
                self.nofity.notify(item=self.errorList)

