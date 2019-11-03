import smtplib
from email.message import EmailMessage
from typing import List
import logging

from withCV.pglib import PGLib
from withCV.settings import Settings


class NotifyMail(object):

    def __init__(self):

        self.logger = logging.getLogger('Log')
        self.pglib = PGLib()

        self.notifyMask = 0x00000010  # at PostgreSQL:staff.attribute

        # 以下HPFのそれを取得・設定すること

        self.server: str = Settings.SMTP.server
        self.port: int = Settings.SMTP.port
        self.user: str = Settings.SMTP.user
        self.password = Settings.SMTP.password

        self.title = '【重要】 CV→SalesReport 連携エラー通知'
        self.top = 'CVからの売上データインポートにて下記エラーが発生しています'
        self.end = '至急ご確認ください'

    def buildBody(self, *, item: List[str]) -> str:

        line: List[str] = []
        br: str = '------------------------------------------------'

        line.append(self.top)
        line.append('')
        line.append(br)
        for index, text in enumerate(item, 1):
            line.append('%d. %s' % (index, text))

        line.append(br)
        line.append('')
        line.append(self.end)
        body = '\n'.join(line)

        return body

    def notify(self, *, item: List[str]) -> bool:

        body = self.buildBody(item=item)
        if body:
            sendto = self.sendlist()

            for mail in sendto:

                msg = EmailMessage()

                msg['Subject'] = self.title
                msg['From'] = self.user
                msg['To'] = mail
                msg.set_content(body)

                try:
                    with smtplib.SMTP(self.server, self.port) as smtp:

                        # smtp.set_debuglevel(True)
                        smtp.ehlo()
                        smtp.login(user=self.user, password=self.password)
                        smtp.send_message(msg)

                except smtplib.SMTPException as e:
                    self.logger.critical(msg=e)
                else:
                    self.logger.info(msg='send notify to [%s]' % (mail, ))
                    pass
        else:
            self.logger.critical(msg='notify body is empty')

        return True

    def sendlist(self) -> List[str]:

        sendto: List[str] = []

        query = 'select nickname,mail from staff where vf=true and attribute&%d<>0' % self.notifyMask
        dst: list = self.pglib.select(query=query)
        for m in dst:
            to: str = m['mail'][0]
            if to:
                sendto.append(to)

        return sendto


if __name__ == '__main__':

    m = NotifyMail()

    item = ['タコです', 'イカです']

    m.notify(item=item)

