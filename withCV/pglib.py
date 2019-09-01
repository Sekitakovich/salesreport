import psycopg2
from psycopg2.extras import DictCursor
from dataclasses import dataclass
from typing import List


class PGLib(object):

    def __init__(self):
        self.param: str = 'host=localhost port=5432 dbname=next user=postgres password=postgres'
        pass

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
                    print(query)
                    cursor.execute(query)
                    cursor.execute('commit')
        except psycopg2.Error as e:
            print(e)
            return False
        else:
            return True


if __name__ == '__main__':

    kv = {'shop': 1, 'note': "よくわかんない"}
    pglib = PGLib()
    pglib.update(table='daily', kv=kv, id=0)

