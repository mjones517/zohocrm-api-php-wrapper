<html>
<head>
    <title>insertRecords Tests</title>
</head>
<body>
<h1>insertRecords Tests</h1>
<pre>
<?php

require('../zoho_config.php');
require_once('../class_zoho_crm.php');

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "\n";
print "Ticket: " . $my_ticket . "\n";

$my_record = array("First Name"=>"Test 1", "Last Name"=>"Test User", "Email"=>"test1@testuser.com", "Mobile"=>"+66-81-111-1111", "Description"=>"Created via ZohoCRM API: Test 1, Test User, +66-81-111-1111");

print "Record 1: \n";
print_r($my_record);
print "\n";

$my_rs = array($my_record);

print "Record Set: \n";
print_r($my_rs);
print "\n";
/*
$my_second_record = array("First Name"=>"Test 2", "Last Name"=>"Test User", "Email"=>"test2@testuser.com", "Mobile"=>"+66-81-222-2222", "Description"=>"Created via ZohoCRM API: Test 2, Test User, +66-81-222-2222");
print "Record 2: \n";
print_r($my_second_record);
print "\n";

//$my_rs[] = $my_second_record;
array_push($my_rs, $my_second_record);

print "Record Set: \n";
print_r($my_rs);
print "\n";

$my_second_record = array("First Name"=>"Test 3", "Last Name"=>"Test User", "Email"=>"test3@testuser.com", "Mobile"=>"+66-81-222-2222", "Description"=>"Created via ZohoCRM API: Test 3, Test User, +66-81-111-1111 (Dup Phone)");
print "Record 2: \n";
print_r($my_second_record);
print "\n";

//$my_rs[] = $my_second_record;
array_push($my_rs, $my_second_record);

print "Record Set: \n";
print_r($my_rs);
print "\n";

$my_second_record = array("First Name"=>"Test 1", "Last Name"=>"Test User", "Email"=>"test4@testuser.com", "Mobile"=>"+66-81-444-4444", "Description"=>"Created via ZohoCRM API: Test 1, Test User, +66-81-444-4444 (Dup Name)");
print "Record 2: \n";
print_r($my_second_record);
print "\n";

//$my_rs[] = $my_second_record;
array_push($my_rs, $my_second_record);

print "Record Set: \n";
print_r($my_rs);
print "\n";

$my_second_record = array("First Name"=>"Test 5", "Last Name"=>"Test User", "Email"=>"test1@testuser.com", "Mobile"=>"+66-81-555-5555", "Description"=>"Created via ZohoCRM API: Test 1, Test User, +66-81-444-4444, test1@testuser.com (Dup Email)");
print "Record 2: \n";
print_r($my_second_record);
print "\n";

//$my_rs[] = $my_second_record;
array_push($my_rs, $my_second_record);
*/
print "Record Set: \n";
print_r($my_rs);
print "\n";

try {
    $contacts = new ZohoCRM("Contacts", $my_ticket);
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}



print "Contact: \n";
print_r($contacts);
print "\n";

//$my_xml_data = $contacts->test_convert_to_xml($my_rs);

try {
    print "Trying record insert...\n";
    $my_result = $contacts->insertRecords($my_rs);
    print "Finished record insert...\n";
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}

print "My result (message): " . $my_result['message'] . "\n\n";

print "My result (error): " . $my_result['error'] . "\n\n";

print "My result (json): " . $my_result['json'] . "\n\n";

print "My result (record_set):\n";
print_r($my_result['rs'] );
print "\n\n";

?>
</pre>
</body>
</html>