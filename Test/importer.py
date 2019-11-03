from pglib import PGLib

if __name__ == '__main__':

    src: str = 'shop.csv'
    pglib = PGLib()

    # query = 'select id,name from shop where vf=true order by id desc'
    # rows = pglib.select(query=query)
    # print(rows)

    with open(src, 'rt', encoding='utf-8') as f:
        csv = f.read().split('\n')
        for line in csv:
            item = line.split('\t')
            sid = int(item[0])
            cid = item[1]
            sname = item[2]
            cname = item[3]
            print('Processing %s(%s) : %d = %s' % (sname, cname, sid, cid))
            kv = {'dtp': cid}
            pglib.update(kv=kv, id=sid, table='shop')