<?php

// Write a script that prints a list of all of the DNS domains on an account. Let the user select a domain
// from the list and add an "A" record to that domain by entering an IP Address, TTL, and requested "A" record
// text. This must be done in PHP with php-opencloud. 

require('vendor/autoload.php');

use OpenCloud\Rackspace;

// read the ini file and store it in $ini as an array
// ini file is at .rackspace_cloud_credentials and contains:
// [authentication]
// username: $your_username
// apikey: $your_apikey
$ini = parse_ini_file(".rackspace_cloud_credentials", TRUE);

$auth = array('username' => $ini['authentication']['username'],
              'apiKey' => $ini['authentication']['apikey']);
$RSconnect = new Rackspace(RACKSPACE_US, $auth);

$RS_DNS = $RSconnect -> dnsService();

$domainlist = $RS_DNS -> DomainList();

$i = 1;

foreach($domainlist as $d)
    print "[".$i++."]".$d -> name."\n";

if($i == 1)
    print "No domains exist on this account.\n";
else
    $choice = readline("Which domain do you want to add an A record to? ");
$domainname = $domainlist[$choice - 1] -> name;

foreach($domainlist as $d)
    if($d -> name == $domainname)
        $domain = $d;
    
$host = readline("What is the host name you would like to add [_____.$domainname.]? ");
$ip = readline("What is the IP address for $host.$domainname? ");
$ttl = readline("What is the TTL? ");

$newrecord = $domain -> Record();
$newrecord -> create(array(
                    'name'  => $host.".".$domainname,
                    'type'  => 'A',
                    'data'  => $ip,
                    'ttl'   => intval($ttl)));
?>