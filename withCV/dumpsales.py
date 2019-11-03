import sys


if __name__ == '__main__':
    for file in sys.argv[1:]:
        print(file)
        with open(file, 'rt', encoding='cp932') as f:
            csv = f.read().split('\n')
            for line in csv:
                if line:
                    item = line.split(',')
                    print('[%s] [%s] apay = %d atotal = %d' % (item[0], item[1], int(item[12]), int(item[13])))
