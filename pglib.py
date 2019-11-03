import psycopg2
from psycopg2.extras import DictCursor
from typing import List
import logging

from settings import Settings


class PGLib(object):

    def __init__(self):

        self.logger = logging.getLogger('Log')
        self.param: str = 'host=%s port=%d dbname=%s user=%s password=%s' % \
                          (Settings.PostgreSQL.host, Settings.PostgreSQL.port, Settings.PostgreSQL.dbname,
                           Settings.PostgreSQL.user, Settings.PostgreSQL.password)

        self.escapetable = str.maketrans({
            '\'': "\'\'",
        })

        self.isConnected: bool = False
        try:
            self.handle = psycopg2.connect(self.param)
            self.cursor = self.handle.cursor(cursor_factory=DictCursor)
        except psycopg2.Error as e:
            self.logger.error(msg=e)
        else:
            self.isConnected = True

    def __del__(self):
        if self.isConnected:
            self.cursor.close()
            self.handle.close()
            pass

    def pg_escape_string(self, *, src: str) -> str:
        return src.translate(self.escapetable)

    def select(self, *, query: str) -> list:
        try:
            self.cursor.execute('begin')
            self.cursor.execute(query)
            result: list = self.cursor.fetchall()
            self.cursor.execute('commit')
        except psycopg2.Error as e:
            self.logger.error(msg=e)
        else:
            return result

    def update(self, *, table: str, id: int, kv: dict) -> int:

        item: List[str] = []

        try:
            self.cursor.execute('begin')
            self.cursor.execute('lock table %s in exclusive mode' % (table, ))  # notice!
            if id == 0:
                query: str = 'select max(id)+1 as next from %s' % (table,)
                self.cursor.execute(query)
                result = self.cursor.fetchone()
                id = int(result['next'])
                query: str = 'insert into %s(id) values(%d)' % (table, id)
                self.cursor.execute(query)
                pass

            for k, v in kv.items():
                item.append("%s='%s'" % (k, v))

            query: str = 'update %s set %s where id=%d' % (table, ','.join(item), id)
            # self.logger.debug(msg=query)
            self.cursor.execute(query)
            self.cursor.execute('commit')
        except psycopg2.Error as e:
            self.logger.error(msg=e)
            return 0
        else:
            return id


if __name__ == '__main__':

    logger = logging.getLogger('Log')
    logger.setLevel(logging.DEBUG)
    handler = logging.StreamHandler()
    handler.setLevel(logging.DEBUG)
    logger.addHandler(handler)

    pglib = PGLib()

    # kv = {'shop': 1, 'note': "よくわかんない"}
    #
    # # pglib.update(table='daily', kv=kv, id=0)
    #
    # ooo = pglib.select(query="select dtp,id,name from shop where vf=true and dtp<>'' order by dtp asc")
    # for shop in ooo:
    #     print('dtp=[%s] id=%d (%s)' % (shop['dtp'], shop['id'], shop['name']))

    src: str = "abc'def'ghi"
    print(src.encode())
    print(pglib.pg_escape_string(src=src).encode())
