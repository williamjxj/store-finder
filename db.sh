#!/bin/bash
# $Id$

MYSQL="mysql -u store_finder -pwilliam -D store_finder"

$MYSQL <<"EOT"
select sid, CONCAT_WS(',',street,city,prov,zip,country) address from stores where lat is null or lng is null;
# select distinct country from stores;
EOT

$MYSQL <<"EOT"
# select CONCAT_WS(',',sid,lat,lng) from stores where country  in ('Canada', 'United States of America');
#select count(*) from stores where country  in ('Canada', 'United States of America');
EOT

