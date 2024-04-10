<?php
/*********************************************************************
    cron.php

    File to handle LOCAL cron job calls.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
@chdir(dirname(__FILE__).'/'); //Change dir.
require('api.inc.php');

if (!osTicket::is_cli())
    die(__('cron.php only supports local cron calls - use http -> api/tasks/cron'));

require_once(INCLUDE_DIR.'class.api.php');

$parser = new ApiEmailDataParser();

$targetMailRawContentFile = '/tmp/debug-mail';
if (isset($argv) && isset($argc) && $argc >= 2) {
    $targetMailRawContentFile = $argv[1];
}
echo "[INFO] Input: $targetMailRawContentFile\n";
if (!file_exists($targetMailRawContentFile)) {
    echo "[INFO] Input File not found\n";
} else {
    $data = $parser->parse(file_get_contents($targetMailRawContentFile));
    print_r($data);
    //$ret = $obj->processEmail(file_get_contents($targetMailRawContentFile), []);
    //print_r($ret);
}
?>
