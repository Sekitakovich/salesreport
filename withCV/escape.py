if __name__ == '__main__':

    s = "123'how"

    table = str.maketrans({"'": "\\'", '1': 'one'})
    e = s.translate(table)

    print(s)
    print(e)
    print(e.encode())
