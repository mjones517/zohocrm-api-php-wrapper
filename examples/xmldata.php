<h1>test_convert_to_xml Tests</h1>
<pre>
<?php

require('../zoho_config.php');
require_once('../class_zoho_crm.php');

$ticketobj = new ZohoAPITicket(DEFAULT_ZOHO_USER, DEFAULT_ZOHO_PASS);
$my_ticket = $ticketobj->getTicket();

print "user: " . DEFAULT_ZOHO_USER . ", pass: " . DEFAULT_ZOHO_PASS . "<br />\n";
print "Ticket: " . $my_ticket . "<br />\n";

$my_record = array("First Name"=>"Test 1", "Last Name"=>"Test User", "Mobile"=>"+66-81-111-1111", "Description"=>"Created via ZohoCRM API");

print "Record 2: <br />\n";
print_r($my_record);
print "<br />\n";

$my_rs = array($my_record);

print "Record Set: <br />\n";
print_r($my_rs);
print "<br />\n";

$my_second_record = array("First Name"=>"Test 2", "Last Name"=>"Test User", "Mobile"=>"+66-81-222-2222", "Description"=>"Created via ZohoCRM API");
print "Record 2: <br />\n";
print_r($my_second_record);
print "<br />\n";

//$my_rs[] = $my_second_record;
array_push($my_rs, $my_second_record);

print "Record Set: <br />\n";
print_r($my_rs);
print "<br />\n";

try {
    $lead = new ZohoCRM("Leads", $my_ticket);
} catch (Exception $e) {
    print 'Caught exception: ' . $e->getMessage() . "\n";
}



print "Lead: <br />\n";
print_r($lead);
print "<br />\n";

$my_xml_data = $lead->test_convert_to_xml($my_rs);

print "<br />\n";
print "XML Data: <br />\n";
print_r($my_xml_data);
print "<br />\n";


?>