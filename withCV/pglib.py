import psycopg2
from psycopg2.extras import DictCursor
from typing import List
import logging


class PGLib(object):

    def __init__(self):

        self.logger = logging.getLogger('Log')
        self.param: str = 'host=localhost port=5432 dbname=next user=postgres password=postgres'

    def select(self, *, query: str) -> list:
        try:
            with psycopg2.connect(self.param) as handle:
                with handle.cursor(cursor_factory=DictCursor) as cursor:
                    cursor.execute('begin')
                    cursor.execute(query)
                    result: list = cursor.fetchall()
                    cursor.execute('commit')
        except psycopg2.Error as e:
            self.logger.error(msg=e)
        else:
            return result

    def update(self, *, table: str, id: int, kv: dict) -> bool:

        item: List[str] = []

        try:
            with psycopg2.connect(self.param) as handle:
                with handle.cursor(cursor_factory=DictCursor) as cursor:
                    cursor.execute('begin')
                    if id == 0:
                        query: str = 'select max(id)+1 as next from %s' % (table,)
                        cursor.execute(query)
                        result = cursor.fetchone()
                        id = int(result['next'])
                        query: str = 'insert into %s(id) values(%d)' % (table, id)
                        cursor.execute(query)
                        pass

                    for k, v in kv.items():
                        item.append("%s='%s'" % (k, v))

                    query: str = 'update %s set %s where id=%d' % (table, ','.join(item), id)
                    self.logger.debug(msg=query)
                    cursor.execute(query)
                    cursor.execute('commit')
        except psycopg2.Error as e:
            self.logger.error(msg=e)
            return False
        else:
            return True


if __name__ == '__main__':

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)
    handler = logging.StreamHandler()
    handler.setLevel(logging.DEBUG)
    logger.addHandler(handler)

    kv = {'shop': 1, 'note': "よくわかんない"}

    pglib = PGLib()
    # pglib.update(table='daily', kv=kv, id=0)

    ooo = pglib.select(query="select dtp,id,name from shop where vf=true and dtp<>'' order by dtp asc")
    for shop in ooo:
        print('dtp=[%s] id=%d (%s)' % (shop['dtp'], shop['id'], shop['name']))