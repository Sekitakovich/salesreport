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

        self.matchTable: Dict[str, int] = {}

    def findDaily(self, *, shopID: int, yyyymmdd: dt) -> int:

        dailyID: int = 0

        query: str = "select id from daily where id=%d and yyyymmdd='%s'" % (shopID, yyyymmdd.strftime('%Y-%m-%d'))
        result = self.pglib.select(query=query)
        try:
            dailyID = int(result[0])
        except (IndexError, ValueError) as e:
            self.logger.error(msg=e)

        return dailyID

    def prepareMatching(self):

        self.matchTable.clear()
        query: str = "select dtp,id from shop where vf=true and dtp<>'' order by dtp asc"
        result = self.pglib.select(query=query)
        for shop in result:
            self.matchTable[shop['dtp']] = shop['id']

        # self.logger.debug(msg=self.matchTable)

    def saveSales(self, *, item: list):

        self.logger.debug(msg=item)

        try:
            yyyymmdd: dt = dt(int(item[0][0:4]), int(item[0][4:6]), int(item[0][6:8]))
            shop: str = item[1]
            member: int = int(item[2])
            visitor: int = int(item[3])
            etime: dt = dt(int(item[4][0:4]), int(item[4][4:6]), int(item[4][6:8]),
                           hour=int(item[4][8:10]), minute=int(item[4][10:12]), second=int(item[4][12:14]))
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
            pass
        else:
            if shop in self.matchTable.keys():
                shopID: int = self.matchTable[shop]
                dailyID = self.findDaily(shopID=shopID, yyyymmdd=yyyymmdd)
                if dailyID:
                    dm = DailyMembers(yyyymmdd=yyyymmdd, member=member, visitor=visitor, etime=etime,
                                  result=result, book=book, booktotal=booktotal,
                                  note=note, mlot=mlot, myen=myen, welcome=welcome)
                    print(dm)
                else:  # daily未登録
                    pass
            else:  # 未登録店舗
                pass

    def importSales(self, *, src: str):

        self.logger.debug(msg='Processing %s' % (src,))

        self.prepareMatching()

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
                self.saveSales(item=csv)
            shutil.move(src=workpath, dst=savepath)
            pass
