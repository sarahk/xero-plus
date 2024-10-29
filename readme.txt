composer

add xero
add clicksend
add guzzle

to get composer to reload

composer dump-autoload

to run pest tests from the terminal

./vendor/bin/pest
./vendor/bin/pest --group=unit
./vendor/bin/pest --display-errors --log-events-text pestresults.txt



https://pestphp.com/docs/cli-api-reference





mysqldump -u xeroplus -p --no-data xeroplus

mysql -h localhost -u xeroplus -p




PHPSTAN - error checking
https://phpstan.org/user-guide/rule-levels

vendor/bin/phpstan analyse -l 6 app/Models/
vendor/bin/phpstan analyse -l 6 app/Models/Enums
vendor/bin/phpstan analyse -l 6 app/Models/Traits
