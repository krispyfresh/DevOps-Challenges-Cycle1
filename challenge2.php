<?php

// Challenge 2: Write a script that builds anywhere from 1 to 3 512MB cloud servers (the number is based
// on user input). Inject an SSH public key into the server for login. Return the IP addresses for the server.
// The servers should take their name from user input, and add a numerical identifier to the name. For example,
// if the user inputs "bob", the servers should be named bob1, bob2, etc... This must be done in PHP with php-opencloud.

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

// get user input
$linux = true;
$imagelist = $RScompute -> imageList();
$num = 0;
while($i = $imagelist -> next())
{
    print "[$num]".$i -> name."\n";
    $num++;
}

$imagenum = readline("Which image would you like to use? "); // add error checking here (input should be between 0-$num)

$image = $RScompute -> Image($imagelist[$imagenum] -> id);
$servername = readline("What do you want the base name of your cloud servers to be? "); // add error checking here (alphanumeric only)
$number = readline("How many cloud servers do you want to create? "); // add error checking here (input should be between 1-3)
$sshkey = readline("Enter location of SSH public key: "); // add error checking here (file existence)   
$keypair = $RScompute -> keypair();

// check for existence of a keypair with the same name, because it will error out if you try to create a new key with the same name
$keys = $RScompute -> listKeypairs();
foreach ($keys as $k)
    if ($k -> getName() == "api_challenge_key")
        $k -> delete();  // delete the key if we find it so that we can replace it

$keypair -> create(array(
    'name'      => 'api_challenge_key',
    'publicKey' => file_get_contents($sshkey)));

//print "api_challenge_key=".file_get_contents($sshkey)."\n";
// create a flavor object for the 512 MB flavor
$flavor = $RScompute -> Flavor(2);    
    
// iterate through the server create operation as many times as the user asked
for($i = 1; $i <= $number; $i++)
{
    $newserver = $RScompute -> server();
    $newserver -> create(array(
        'name'      => $servername.$i,
        'image'     => $image,
        'flavor'    => $flavor,
        'keypair'   => 'api_challenge_key'));

    print "\nCreating server $i of $number.\n";
    sleep(15);
    $ips = $newserver -> ips();    

    print "Server Name: $servername"."$i\n";
    print "Public IPv4 Address: ".$ips -> public[0] -> addr."\n";
    print "Public IPv6 Address: ".$ips -> public[1] -> addr."\n";
    print "Root/administrator password: ".$newserver -> adminPass."\n";
    print "Uploaded key from $sshkey to the server\n";

}


?>