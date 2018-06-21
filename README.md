# biz.lcdservices.logretention

This extension creates an API that can be run as a scheduled job to purge changelog records prior to the configured retention window.

After installing the extension through the normal means, navigate to Administer > System Settings > Log Retention Settings. Indicate the retention period in months. Logging records prior to that window will be purged when this API is run. Optionally select tables which will be excluded from the purging process. For example, if you want to retain all relationship history for contacts, you can exclude the civicrm_relationship table. Note that the exclusion process does not take into consideration any dependencies that may exist between tables.

Now navigate to Administer > System Settings > Scheduled Jobs and locate the Log Retention job. Edit the job, enable it, and set it to the desired frequency. Because the job is potentially long running, we suggest you run it weekly and schedule it for the weekend (assuming that is a low traffic time for your site).

<b>Note: </b>The log purging/retention process can only be performed on logging tables configured using the InnoDB or MyISAM table engine type. The default engine used by the enhanced logging feature in CiviCRM is ARCHIVE, which does not support row deletion. If you have installed this extension before enabling enhanced logging, it will create your tables using InnoDB. If your logging tables have already been created, enable this extension and then run the system.updatelogtables API function which will rebuild those tables using InnoDB as defined in this extension.
