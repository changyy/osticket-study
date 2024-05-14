<?php
/*********************************************************************
    COPY from cron.php 

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
@chdir(dirname(__FILE__).'/'); //Change dir.
require('api.inc.php');
if (!osTicket::is_cli())
    die(__('test-mailDownload.php only supports local cron calls'));

require_once(INCLUDE_DIR.'api.cron.php');
//LocalCronApiController::call();

//require_once(INCLUDE_DIR.'class.email.php');
//osTicket\Mail\Fetcher::run();

        global $ost;
        if(!$ost->getConfig()->isEmailPollingEnabled()) {
            echo "isEmailPollingEnabled = false\n";
	}

        $mailboxes = \MailBoxAccount::objects()
            ->filter(['active' => 1])
            ->order_by('last_activity');

        $start_time = \Misc::micro_time();
        foreach ($mailboxes as $mailbox) {
            // Check if the mailbox is active 4realz by getting credentials
            if (!$mailbox->isActive())
                continue;

            // Try fetching emails
            try {
                // class.mailfetch.php
                //$mailbox->fetchEmails();

		// class.email.php
		//   $fetcher = new osTicket\Mail\Fetcher($this);
		//   return $fetcher->processEmails();
		// class.mailfetch.php
		//   function processEmails() { ... }
		
		echo "Try fetching emails at ".date('Y-m-d H:i:s')."\n";
		echo $mailbox->getEmail()."\n";
		$fetcher = new osTicket\Mail\Fetcher($mailbox);
		$mbox = $mailbox->getMailBox();
		if (!$mbox) {
                     echo "no connection\n";
		     break;
	        }
                $archiveFolder = $fetcher->getArchiveFolder();
		echo "getArchiveFolder: $archiveFolder\n";
                $deleteFetched =  $fetcher->canDeleteEmails();
		echo "getDeleteFetched: $deleteFetched\n";
                $max = $fetcher->getMaxFetch() ?: 30; // default to 30 if not set

                // Get message count in the Fetch Folder
                $messageCount = $mbox->countMessages();
                // If the number of emails in the folder are more than Max Fetch
                // then process the latest $max emails - this is necessary when
                // fetched emails are not getting archived or deleted, which might
                // lead to fetcher being stuck 4ever processing old emails already
                // fetched
                if ($messageCount > $max) {
                    // Latest $max messages
                    $messages = range($messageCount-$max, $messageCount);
                } else {
                    // Create a range of message sequence numbers (msgno) to fetch
                    // starting from the oldest taking max fetch into account
                    $messages = range(1, min($max, $messageCount));
        	}
                echo "messages: ".print_r($messages, true)."\n";
		foreach ($messages as $i) {
		    echo "Download email: $i\n";
		    // class.mail.php
		    // echo $mbox->getRawEmail($i)."\n";
		    echo "bin2hex(substr(getRawHeader, -4)):    ".bin2hex(substr($mbox->getRawHeader($i), -4))."\n";
		    echo "bin2hex(substr(getRawContent, 0, 4)): ".bin2hex(substr($mbox->getRawContent($i), 0, 4))."\n";
		    break;
	        }
            } catch (\Throwable $t) {
		echo "Throwable:\n";
		print_r($t);
                if ($mailbox->getNumErrors() >= $MAXERRORS && $ost) {
                    //We've reached the MAX consecutive errors...will attempt logins at delayed intervals
                    // XXX: Translate me
                    $msg = sprintf("\n %s:\n",
                            _S('osTicket is having trouble fetching emails from the following mail account')).
                        "\n"._S('Email').": ".$mailbox->getEmail()->getAddress().
                        "\n"._S('Host Info').": ".$mailbox->getHostInfo();
                        "\n"._S('Error').": ".$t->getMessage().
                        "\n\n ".sprintf(_S('%1$d consecutive errors. Maximum of %2$d allowed'),
                                $mailbox->getNumErrors(), $MAXERRORS).
                        "\n\n ".sprintf(_S('This could be connection issues related to the mail server. Next delayed login attempt in aprox. %d minutes'), $TIMEOUT);
                    $ost->alertAdmin(_S('Mail Fetch Failure Alert'), $msg, true);
                }
            }
	} //end foreach.
