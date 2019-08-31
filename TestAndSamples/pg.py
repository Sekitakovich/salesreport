import psycopg2
from psycopg2.extras import DictCursor
from dataclasses import dataclass
from typing import Dict


@dataclass()
class Reffer(object):

    id: int
    name: str


class PGSession(object):

    def __init__(self):

        self.param: str = 'host=localhost port=5432 dbname=next user=postgres password=postgres'

    def listup(self) -> Dict[str, Reffer]:

        query: str = "select id,dtp,name from shop where vf=true and dtp<>'' order by id asc"
        matchtable: Dict[str, Reffer] = {}

        try:
            with psycopg2.connect(self.param) as handle:
                with handle.cursor(cursor_factory=DictCursor) as cursor:
                    cursor.execute(query)
                    result = cursor.fetchall()
                    for row in result:
                        mt = Reffer(id=int(row['id']), name=row['name'])
                        matchtable[row['dtp']] = mt
        except psycopg2.Error as e:
            print(e)
        else:
            pass

        return matchtable


if __name__ == '__main__':

    session = PGSession()

    mt = session.listup()
    for k, v in mt.items():
        print('%s = %s (%s)' % (k, v.id, v.name))

