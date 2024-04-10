<?php
@chdir(dirname(__FILE__).'/');
require('api.inc.php');

if (!isset($argc) || !isset($argv)) {
    echo "This script can only be run from the command line.\n";
    exit(1);
}

if ($argc <= 1 || !file_exists($argv[1])) {
    echo "Usage: php ".$argv[0]." <eml> | php ".$argv[0]." <eml> mimeDecodeLibrary.php \n";
    exit(1);
}

$mimeDecodeFilePath = INCLUDE_DIR.'pear/Mail/mimeDecode.php';

if ($argc > 2) {
    if (file_exists($argv[2])) {
        $mimeDecodeFilePath = $argv[2];
    } else if (file_exists(INCLUDE_DIR.'pear/Mail/'.$argv[2])) {
        $mimeDecodeFilePath = INCLUDE_DIR.'pear/Mail/'.$argv[2];
    }
}
echo '[INFO] eml file: '.$argv[1]."\n";
echo '[INFO] Loading mimeDecode.php from: '.$mimeDecodeFilePath."\n";

require_once($mimeDecodeFilePath);

// https://github.com/osTicket/osTicket/blob/develop/include/class.mailparse.php#L53
$params = array('crlf'          => "\r\n",
                        'include_bodies'=> true, //$this->include_bodies,
                        'decode_headers'=> false, // $this->decode_headers,
                        'decode_bodies' => true ); // $this->decode_bodies);

$decoder = new Mail_mimeDecode(file_get_contents($argv[1]));
$obj = $decoder->decode($params);

print_r($obj);
