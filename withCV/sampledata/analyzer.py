from typing import List
from dataclasses import dataclass
from datetime import datetime as dt


@dataclass()
class FromDTP(object):

    yyyymmdd: dt  # 計上日付
    shop: str  # 得意先コード
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


if __name__ == '__main__':

    file: str = '20190809183719_SALES.csv'

    with open(file, 'rt', encoding='shift_jis') as f:
        index: int = 0
        while True:
            line: str = f.readline()
            if line:
                item: List[str] = line.strip().split(',')
                if len(item) == 12:
                    print(item)
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
                    except (IndexError, ValueError) as e:
                        print(e)
                        pass
                    else:
                        dtp = FromDTP(yyyymmdd=yyyymmdd, shop=shop, member=member, visitor=visitor, etime=etime,
                                      result=result, book=book, booktotal=booktotal,
                                      note=note, mlot=mlot, myen=myen, welcome=welcome)
                        print(dtp)
                        index += 1
                        pass
                pass
            else:
                break