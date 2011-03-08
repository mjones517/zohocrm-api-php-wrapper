<html>
<head>
    <title>getMyRecords Tests</title>
</head>
<body>
<h1>getMyRecords Tests</h1>
<pre>
<?php

print "Starting tests for getMyRecords...\n";
print 'Including: ' . dirname(__FILE__) . '/../class_zoho_crm.php' . "\n";

require_once(dirname(__FILE__) . '/../class_zoho_crm.php');

print "Required filed added...\n\n";

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "\n";

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "Ticket: " . $my_ticket . "\n";

try {
    $leads = new ZohoCRM("Leads", $my_ticket);
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}

print "Lead: \n";
print_r($leads);
print "\n";

try {
    print "Trying getMyRecords...\n";
    $my_result = $leads->getMyRecords();
    print "Finished rgetMyRecords...\n";
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