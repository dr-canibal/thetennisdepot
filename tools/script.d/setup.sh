#!/bin/bash

ENV="local";
FNAME="";
DOBACKUP=false;

DATE=`eval date +%Y%m%d_%H%I%s`

OPTSPEC="e:f:b:";
while getopts "$OPTSPEC" option; do
    case "${option}" in
        e) ENV=${OPTARG};;
        f) FNAME=${OPTARG};;
        b) DOBACKUP=true;;
    esac
done

source "./config/default.cfg"
source "./config/$ENV.cfg";

## Step 1: Create production database dump
DUMP_CMD1="mysqldump --single-transaction --quick --routines --no-data -h $PROD_DB_HOST -u $PROD_DB_USER -p$PROD_DB_PASSWORD $PROD_DB_NAME dxov_log_url dxov_log_url_info dxov_log_visitor dxov_log_visitor_info dxov_log_customer dxov_log_quote dxov_log_summary dxov_log_visitor_online dxov_report_event dxov_report_compared_product_index dxov_report_viewed_product_index | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | gzip -c > $SSH_FOLDER/dump/$DATE.sql.gz";
DUMP_CMD2="mysqldump --single-transaction --quick --routines --ignore-table=$PROD_DB_NAME.dxov_log_url --ignore-table=$PROD_DB_NAME.dxov_log_url_info --ignore-table=$PROD_DB_NAME.dxov_log_visitor --ignore-table=$PROD_DB_NAME.dxov_log_visitor_info --ignore-table=$PROD_DB_NAME.dxov_log_visitor_online --ignore-table=$PROD_DB_NAME.dxov_log_customer --ignore-table=$PROD_DB_NAME.dxov_log_quote --ignore-table=$PROD_DB_NAME.dxov_log_summary --ignore-table=$PROD_DB_NAME.dxov_report_compared_product_index --ignore-table=$PROD_DB_NAME.dxov_report_event --ignore-table=$PROD_DB_NAME.dxov_report_viewed_product_index -h $PROD_DB_HOST -u $PROD_DB_USER -p$PROD_DB_PASSWORD $PROD_DB_NAME | sed -e 's/DEFINER[ ]*=[ ]*[^*]*\*/\*/' | gzip -c >> $SSH_FOLDER/dump/$DATE.sql.gz"

if test "$DB_DUMP_OVER_SSH" = "true"; then
    echo "Dumping over SSH";
    ssh -l $SSH_USER $SSH_HOST "$DUMP_CMD1";
    ssh -l $SSH_USER $SSH_HOST "$DUMP_CMD2";
else
    echo "Raw dumping";
    eval $DUMP_CMD1;
    eval $DUMP_CMD2;
fi

## Step 2: Download dump if using remote server
if test "$DB_DUMP_OVER_SSH" = "true"; then
    echo "Downloading database dump";
    rsync -avzhe ssh $SSH_USER@$SSH_HOST:$SSH_FOLDER/dump/$DATE.sql.gz $LOCAL_DUMP_FOLDER/
fi

if test "$DB_DUMP_OVER_SSH" = "true"; then
    DUMP_FILE="$LOCAL_DUMP_FOLDER/$DATE.sql.gz";
else
    DUMP_FILE="$SSH_FOLDER/dump/$DATE.sql.gz";
fi

echo "Dump file is stored at $DUMP_FILE";

## read -p "Press any key to continue... " -n1 -s

## Step 2: Clean target database
echo "Dropping all existing tables";
if test "$DB_DUMP_OVER_SSH" = "true"; then
    TMP_SQL_FILE="$LOCAL_DUMP_FOLDER/$DATE.tmp.sql";
else
    TMP_SQL_FILE="$SSH_FOLDER/dump/$DATE.tmp.sql";
fi

echo "SET FOREIGN_KEY_CHECKS = 0;" > $TMP_SQL_FILE; \
mysqldump -u $DB_USER -p$DB_PASSWORD -h $DB_HOST --add-drop-table --no-data $DB_NAME | grep ^DROP >> $TMP_SQL_FILE; \
echo "SET FOREIGN_KEY_CHECKS = 1;" >> $TMP_SQL_FILE; \
mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME < $TMP_SQL_FILE;

## read -p "Press any key to continue... " -n1 -s

## Step 3: Load database dump to new database
IMPORT_CMD="gzip -dc < $DUMP_FILE | mysql -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_NAME";

echo "Loading original database dump";

## if test "$DB_DUMP_OVER_SSH" = "true"; then
    eval $IMPORT_CMD;
## fi

## read -p "Press any key to continue... " -n1 -s

## Step 4: Do database cleanup
echo "Cleaning up original database";

SQL1="DELETE FROM dxov_sales_flat_invoice WHERE increment_id LIKE '%INV_%' and base_grand_total=0";
SQL2="DELETE FROM dxov_sales_flat_order WHERE increment_id = ''";
MYSQL_CMD1='mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME -e "$SQL1"';
MYSQL_CMD2='mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME -e "$SQL2"';

eval $MYSQL_CMD1;
eval $MYSQL_CMD2;

## Step 5: Backup production media
echo "Dumping media";

if test "$DB_DUMP_OVER_SSH" = "true"; then
    echo "Dumping over SSH";
    rsync --progress -avzhe ssh $SSH_USER@$SSH_HOST:$SSH_FOLDER/thetennisdepot.com/html/media $MAGENTO_ROOT/
else
    echo "Raw dumping";
    rsync --progress -avz $SSH_FOLDER/thetennisdepot.com/html/media $MAGENTO_ROOT/
fi

## Step 6: Run system setup
## cd $MAGENTO_ROOT && chmod -R 777 media && chmod -R 777 var
cd $MAGENTO_ROOT; ## && chmod -R 777 media && chmod -R 777 var
cp -f $PROJECT_ROOT/conf/local.$ENV.xml app/etc/local.xml

$MAGERUN_CMD config:set "web/unsecure/base_url" "$UNSECURE_URL/" --scope="default" --scope-id="0"
$MAGERUN_CMD config:set "web/secure/base_url" "$SECURE_URL/" --scope="default" --scope-id="0"

$MAGERUN_CMD config:set "web/unsecure/base_url" "$UNSECURE_URL/badminton/" --scope="stores" --scope-id="2"
$MAGERUN_CMD config:set "web/secure/base_url" "$SECURE_URL/badminton/" --scope="stores" --scope-id="2"

$MAGERUN_CMD config:set "web/unsecure/base_url" "$UNSECURE_URL/squash/" --scope="stores" --scope-id="3"
$MAGERUN_CMD config:set "web/secure/base_url" "$SECURE_URL/squash/" --scope="stores" --scope-id="3"

$MAGERUN_CMD config:set "web/unsecure/base_url" "$UNSECURE_URL/beachtennis/" --scope="stores" --scope-id="4"
$MAGERUN_CMD config:set "web/secure/base_url" "$SECURE_URL/beachtennis/" --scope="stores" --scope-id="4"

$MAGERUN_CMD sys:setup:run -vvv >> setup.log

## Step 7: Post upgrade events
SQL="ALTER TABLE dxov_catalogrule_product_price DROP COLUMN rule_hash";
MYSQL_CMD1='mysql -u $DB_USER -p$DB_PASSWORD -h $DB_HOST $DB_NAME -e "$SQL"';

## $MAGERUN_CMD config:set "" ""

$MAGERUN_CMD index:reindex:all
$MAGERUN_CMD cache:clean
$MAGERUN_CMD cache:flush
##
