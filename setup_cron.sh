# Setup CRON job to run cron.php every 5 minutes
CRON_JOB="*/5 * * * * php $(pwd)/src/cron.php >/dev/null 2>&1"
(crontab -l 2>/dev/null; echo "$CRON_JOB") | sort | uniq | crontab -
echo "CRON job installed: $CRON_JOB" 
