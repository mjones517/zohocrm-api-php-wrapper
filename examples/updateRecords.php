<html>
<head>
    <title>updateRecords Tests</title>
</head>
<body>
<h1>updateRecords Tests</h1>
<pre>
<?php

require('../zoho_config.php');
require_once('../class_zoho_crm.php');

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "\n";
print "Ticket: " . $my_ticket . "\n";

$id = '209040000000270005';

print "ID: $id\n";

// create record to update
$my_record = array("First Name"=>"Test 1", "Last Name"=>"Updated User", "Mobile"=>"+66-81-111-1111", "Description"=>"Updated via ZohoCRM API");

print "Record 1: \n";
print_r($my_record);
print "\n";

$my_rs = array($my_record);

print "Record Set: \n";
print_r($my_rs);
print "\n";

/*
$my_second_record = array("First Name"=>"Test 2", "Last Name"=>"Updated User", "Mobile"=>"+66-81-222-2222", "Description"=>"Updated via ZohoCRM API");
print "Record 2: \n";
print_r($my_second_record);
print "\n";

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

try {
    print "Trying record update...\n";
    $my_result = $contacts->updateRecords($id, $my_rs);
    print "Finished record update...\n";
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