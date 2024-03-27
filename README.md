# Usage

## Build Docker Image

```
% cd docker
% docker build -t osticket-dev:1.18.1 . 
```

## Running the Docker Container - All Nginx logs are directed to stdout

```
% docker run -it -p 80:80 -p 3306:3306 osticket-dev:1.18.1
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

### Testing 

```
# cd /var/www/osticket-v1.18.1
# php api/cron.php
```
