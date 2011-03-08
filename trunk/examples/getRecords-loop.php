<html>
<head>
    <title>getRecords (Loop) Test</title>
</head>
<body>
<h1>getRecords (Loop) Test</h1>
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
for ($i = 0; $i <= 3; $i++) {
    
    $from_index = ($i * 200) + 1;
    $to_index = $from_index + 199;

    print "Trying to select record numbers from $from_index to $to_index \n\n";
    
    try {
        print "Trying getRecords...\n";
        $my_result = $contacts->getRecords($select_columns='All', $from_index=$from_index, $to_index=$to_index, $sort_column_string="Modified Time");
        print "Finished getRecords...\n";
    } catch (Exception $e) {
        print 'Caught exception: ' . $e->getMessage() . "\n";
    }
    
    print "My result (message): " . $my_result['message'] . "\n\n";
    
    print "My result (error): " . $my_result['error'] . "\n\n";
    
    print "My result (json): " . $my_result['json'] . "\n\n";
    
    print "My result (record_set):\n";
    print_r($my_result['rs'] );
    print "\n\n";
}

?>
</pre>
</body>
</html>