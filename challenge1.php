<?php

// Challenge 1: Write a script that builds a 512MB Cloud Server and returns the root password and
// IP address for the server. This must be done in PHP with php-opencloud.

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

$RScompute = $RSconnect -> computeService('cloudServersOpenStack', 'IAD', 'publicURL');

// print the list of images and get user input
$imagelist = $RScompute -> imageList();
$num = 0;
while($image = $imagelist -> next())
{
    print "[$num]".$image -> name."\n";
    $num++;
}
$imagenum = readline("Which image would you like to use? ");

// create an image object for the chosen image
$newimage = $RScompute -> Image($imagelist[$imagenum] -> id);

// create a flavor object for the 512 MB flavor
$newflavor = $RScompute -> Flavor(2);

// create the cloud server
$newserver = $RScompute -> server();
$newserver -> create(array(
    'name'      => 'KrispyServ',
    'image'     => $newimage,
    'flavor'    => $newflavor));

// sleep here because it takes a couple seconds for the cloud server object at RS to be created and assigned IPs and what not
print "Server creation has begun.  Retreiving IP address and password information...\n";
sleep(10);
// place IP info of server in an array.  this is kinda messy but there doesn't seem to be direct access to the IP addresses
$ips = $newserver -> ips();
print "Public IPv4 Address: ".$ips -> public[0] -> addr."\n";
print "Public IPv6 Address: ".$ips -> public[1] -> addr."\n";
print "Root/administrator password: ".$newserver -> adminPass."\n";

?>