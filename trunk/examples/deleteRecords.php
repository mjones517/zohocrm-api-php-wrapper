<html>
<head>
    <title>deleteRecords Tests</title>
</head>
<body>
<h1>deleteRecords Tests</h1>
<pre>
<?php

require('../zoho_config.php');
require_once('../class_zoho_crm.php');

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "\n";
print "Ticket: " . $my_ticket . "\n";

$id = '000000000000000000';  // Add a valid record ID here

print "ID: $id\n";

try {
    $leads = new ZohoCRM("Leads", $my_ticket);
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}

print "Lead: \n";
print_r($leads);
print "\n";

try {
    print "Trying record delete...\n";
    $my_result = $leads->deleteRecords($id);
    print "Finished record delete...\n";
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