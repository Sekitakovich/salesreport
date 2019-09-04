import smtplib
from email.message import EmailMessage
from typing import List


class MailSender(object):

    def __init__(self):

        self.server: str = 'mail.klabo.co.jp'
        self.port: int = 587  # submission port

        self.user: str = 'k-seki'
        self.password = 'Narikunn+2019'

    #     self.to: List[str] = []
    #
    # def plusMember(self, *, to: str):
    #
    #     if to not in self.to:
    #         self.to.append(to)
    #
    def buildMail(self, *, subject: str, body: str, to: str) -> EmailMessage:

        msg = EmailMessage()

        msg['Subject'] = subject
        msg['From'] = self.user
        msg['To'] = to
        msg.set_content(body)

        return msg

    def send(self, *, msg: EmailMessage, debug=False) -> bool:

        try:
            with smtplib.SMTP(self.server, self.port) as smtp:

                smtp.set_debuglevel(debug)
                smtp.ehlo()
                smtp.login(user=self.user, password=self.password)
                smtp.send_message(msg)
        except smtplib.SMTPException as e:
            print(e)
            return False
        else:
            return True


if __name__ == '__main__':

    m = MailSender()

    ooo = m.buildMail(subject='test', body='日本語本文でおま', to='takoparadise2007@yahoo.co.jp')
    print(ooo)

    result = m.send(msg=ooo, debug=True)
    print(result)
