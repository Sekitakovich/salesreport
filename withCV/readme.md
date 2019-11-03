PostgreSQLについて

外部での開発時、セキュリティ対策上[master.hpfrance.com:5432]ができないので
Puttyで[local:5432 -> remote:5432]としてトンネルを設定しておく。こうすることで、
[host=localhost, port=5432]と書けるのでローカル・リモートの違いを意識する必要が
なくなった。やったね♪

