# Usage

## Build Docker Image

```
% docker build -t osticket-dev:develop -f develop/Dockerfile .
```

## Running the Docker Container - All Nginx logs are directed to stdout

```
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:develop
```

## Running the Docker Container - Start in a Bash shell

```
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:develop /bin/bash
root@:/# service mysql start ; service php8.1-fpm start ; service nginx start
 * Starting MySQL database server mysqld
   su: warning: cannot change directory to /nonexistent: No such file or directory
                                                                                    [ OK ]
 * Starting nginx nginx                                                             [ OK ]
root@:/# tail -f /var/log/nginx/*.log
==> /var/log/nginx/error.log <==

==> /var/log/nginx/access.log <==
```

## Use the Docker volume option to mount and overwrite the source code in the image, facilitating easier development and testing.

### Optional: Clean up the source code directory

```
% cd ./tmp/my-osticket-src
% git clean -x -d -f
% cp include/ost-sampleconfig.php include/ost-config.php
% cd - 
```

### Running the Docker Container with the volume option

```
% docker run -it -p 80:80 -p 3306:3306 -v ./tmp/my-osticket-src:/var/www/osticket-develop osticket-dev:develop
```

## Setting up osTicket

### Installation

Navigate to http://localhost/ and follow the setup instructions.

Be cautious with the MySQL database settings. The default settings are:

  - DB Host: 127.0.0.1
  - DB Name: osticket_dev
  - DB User: developer
  - DB Password: 12345678

Note: Using `localhost` as DB Host does not work (`PHP Fatal error: Uncaught mysqli_sql_exception: Permission denied`).

## Database Access

### PHPMyAdmin

To access the database via PHPMyAdmin, navigate to http://localhost/phpmyadmin and log in with the following credentials:

  - Username: developer
  - Password: 12345678

### Using MySQL Client via SSH Tunnel

server account:

  - Username: developer
  - Password: 12345678

#### SSH Tunnel

```
% docker run -it -p 20022:22 -p 80:80 -p 3306:3306 -v ./tmp/my-osticket-src:/var/www/osticket-develop osticket-dev:develop
$ ssh -N -p 20022 -L 23306:127.0.0.1:3306 developer@localhost
password: 12345678
```

#### macOS mysql client via Python mycli tool

```
% python3 -m venv /tmp/venv
% source /tmp/venv/bin/activate
(venv) /tmp % pip install mycli
(venv) /tmp % mycli -h 127.0.0.1 -P 23306 -u developer -p 12345678
% mycli -h 127.0.0.1 -P 23306 -u developer -p 12345678
MySQL 8.0.37
mycli 1.27.2
Home: http://mycli.net
Bug tracker: https://github.com/dbcli/mycli/issues
Thanks to the contributor - Colin Caine
MySQL developer@127.0.0.1:(none)>
```

---

# osTicket Docker Development Environment v1.18.1 Usage

```
% docker build -t osticket-dev:v1.18.1 -f v1.18.1/Dockerfile .
% docker build -t osticket-dev:v1.18.1-updated -f v1.18.1-updated/Dockerfile .
```

## Running the Docker Container - All Nginx logs are directed to stdout

```
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:v1.18.1
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:v1.18.1-updated
```

## Running the Docker Container - Start in a Bash shell

```
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:1.18.1 /bin/bash
root@:/# service mysql start ; service php8.1-fpm start ; service nginx start
 * Starting MySQL database server mysqld
   su: warning: cannot change directory to /nonexistent: No such file or directory
                                                                                    [ OK ]
 * Starting nginx nginx                                                             [ OK ]
root@:/# tail -f /var/log/nginx/*.log
==> /var/log/nginx/error.log <==

==> /var/log/nginx/access.log <==

```

## Setting up osTicket v1.18.1

### Installation

Navigate to http://localhost/ and follow the setup instructions.

Be cautious with the MySQL database settings. The default settings are:

  - DB Host: 127.0.0.1
  - DB Name: osticket_v1_18_1
  - DB User: developer
  - DB Password: 12345678

Note: Using `localhost` as DB Host does not work (`PHP Fatal error: Uncaught mysqli_sql_exception: Permission denied`).

### Configuration

1. Admin Panel -> Emails -> Emails -> Support -> Remote Mailbox
2. Admin Panel -> Emails -> Settings -> Incoming Emails -> Email Fetching -> Enable

## Testing

### Saving Raw Email with Prefix Path `/tmp/debug-mail.`

```
# cat -n /var/www/osticket-v1.18.1/include/class.mailfetch.php 
...
...
    78	    function processMessage(int $i, array $defaults = []) {
    79	        try {
    80	
    81		    @file_put_contents("/tmp/debug-mail.$i", $this->mbox->getRawEmail($i));
    82	
    83	            // Please note that the returned object could be anything from
    84	            // ticket, task to thread entry or a boolean.
    85	            // Don't let TicketApi call fool you!
    86	            return $this->getTicketsApi()->processEmail(
    87	                    $this->mbox->getRawEmail($i), $defaults);
    88	        } catch (\TicketDenied $ex) {
    89	            // If a ticket is denied we're going to report it as processed
    90	            // so it can be moved out of the Fetch Folder or Deleted based
    91	            // on the MailBox settings.
    92	            return true;
    93	        } catch (\EmailParseError $ex) {
    94	            // Upstream we try to create a ticket on email parse error - if
    95	            // it fails then that means we have invalid headers.
    96	            // For Debug purposes log the parse error + headers as a warning
    97	            $this->logWarning(sprintf("%s\n\n%s",
    98	                        $ex->getMessage(),
    99	                        $this->mbox->getRawHeader($i)));
   100	        }
   101	        return false;
   102	    }
...
```

### Displaying Mail Extraction Result

Running `api/cron.php`

```
# cd /var/www/osticket-v1.18.1
# php api/cron.php
# ls /tmp/debug-mail*  
/tmp/debug-mail.1
```

Running `api/cron-dev.php`

```
# php api/cron-dev.php 
[INFO] Input: /tmp/debug-mail
[INFO] Input File not found

# php api/cron-dev.php /tmp/debug-mail.1
[INFO] Input: /tmp/debug-mail.1
Ticket Object
(
    [ht] => Array
        (
            [ticket_id] => 2
            [ticket_pid] => 
...
            [lastupdate] => 2024-03-27 21:35:52
            [created] => 2024-03-27 21:35:52
            [updated] => 2024-03-27 21:35:52
            [topic] => 
            [staff] => 
            [user] => User Object
                (
                    [ht] => Array
                        (
                            [id] => 2
                            [org_id] => 0
                            [default_email_id] => 2
                            [status] => 0
                            [name] => UserName
                            [created] => 2024-03-27 21:35:52
                            [updated] => 2024-03-27 21:35:52
                            [default_email] => UserEmailModel Object
                                (
                                    [ht] => Array
                                        (
                                            [id] => 2
                                            [user_id] => 2
                                            [flags] => 0
                                            [address] => user@example.com
...
```
