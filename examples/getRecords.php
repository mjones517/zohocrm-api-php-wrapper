<html>
<head>
    <title>getRecords Tests</title>
</head>
<body>
<h1>getRecords Tests</h1>
<pre>
<?php

require('../zoho_config.php');
require_once('../class_zoho_crm.php');

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "\n";
print "Ticket: " . $my_ticket . "\n";

try {
    $contacts = new ZohoCRM("Contacts", $my_ticket);
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}

print "Lead: \n";
print_r($contacts);
print "\n";

try {
    print "Trying getMyRecords...\n";
    $my_result = $contacts->getRecords();
    print "Finished getMyRecords...\n";
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