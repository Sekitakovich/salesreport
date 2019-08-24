import psycopg2


if __name__ == '__main__':

    handle = psycopg2.connect('host=localhost port=5432 dbname=next user=postgreq password=postgres')
    print(handle)