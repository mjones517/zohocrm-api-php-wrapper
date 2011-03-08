

<?php 

if (file_exists(dirname(__FILE__) . '/zoho_config.php')) {
    require_once(dirname(__FILE__) . '/zoho_config.php');
} else {
    die("Zoho Config file not found...");
}

class ZohoAPITicket {

    var $ticket;
    var $user;
    var $pass;
    var $api_key;
    
    public function __construct($user=DEFAULT_ZOHO_USER, $pass=DEFAULT_ZOHO_PASS, $api_key=DEFAULT_ZOHO_API_KEY) {
        $this->user = $user;
        $this->pass = $pass;
        $this->api_key = $api_key;
    }
    
    function getTicket() {
        
        if ($this->ticket){
            // We already instanitated the ticket object so we have a ticket valid for at least 2 hours.
            return $this->ticket;
        
        // Only clean the ticket table if there is a ticket table!
        } elseif (!ZOHO_TICKET_TABLE) {
            // New ticket object.  First check database for cached, unexpired ticket; if there is none, POST to API to get a new one
            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($mysqli->connect_error) {
                throw new ZohoTicketException("Could not connect to database " . DB_NAME . "Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
            }
            
            // Preemptive delete of all expired tickets
            $query = "DELETE FROM " . ZOHO_TICKET_TABLE . " WHERE expire_date < NOW()";
            $result = $mysqli->query($query);
            
            $query = 'SELECT user, ticket, expire_date FROM ' . ZOHO_TICKET_TABLE;
            $result = $mysqli->query($query);
            
            if ($result->num_rows) {
                while ($row = $result->fetch_assoc()) {
                    if ($row["user"] == $this->user) {
                        //We found an unexpired ticket for this user, save it as a global
                        $this->ticket = $row["ticket"];
                    }
                }
            }
        }
        
        if (!$this->ticket) {
            // There was no ticket in the DB, or the ticket in the DB was expired; we need to post to Zoho to get a new one for the user...
            //set POST variables
            $url = 'https://accounts.zoho.com/login';
            $fields = array( 'LOGIN_ID'=>urlencode($this->user),
                                    'PASSWORD'=>urlencode($this->pass),
                                    'FROM_AGENT'=>urlencode("true"),
                                    'servicename'=>urlencode("ZohoCRM")
                                    );
            //url-ify the data for the POST
            $fields_string = '';
            foreach($fields as $key=>$value) { 
                    $fields_string .= $key.'='.$value.'&'; 
            }
            rtrim($fields_string,'&');
            
            // Post via cURL, 
            $ch = curl_init();
            //set the url, number of POST vars, POST data, and have cURL return the data as a result
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_POST,count($fields));
            curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            //execute POST
            $curl_count = 0;
            $curl_result = curl_exec($ch);
            while ((!$curl_result) && ($curl_count < 10)) {
                $curl_count +=1;
                print "API call failed; requested $curl_count times.\n";
            }
            
            $curl_info = curl_getinfo($ch);
            curl_close($ch);
            
            if ($curl_info['http_response'] = 200) {
                $lines = explode("\n", $curl_result);
                foreach ($lines as $line) {
                    trim($line);
                    if (substr_count ($line, "=")) {
                        list ($key, $value) = explode('=', $line);
                        if ($key == "TICKET") {
                            $this->ticket = $value;
                        }
                    }
                }
                
                // Only save a ticket if there is a DB table to save it to!
                if (!ZOHO_TICKET_TABLE) {
                    // Save the ticket with (7 day - two hour) expire_date
                    $expire_date = date("c", time() + (60*60*24*7-7200));  // set expire_date to one week minus two hours, to be safe.
                    $query = "INSERT INTO " . ZOHO_TICKET_TABLE . " (user, ticket, expire_date) VALUES ('$this->user', '$this->ticket', '$expire_date')";
                    $result = $mysqli->query($query);
                    if (!$mysqli->affected_rows) {
                        throw new ZohoTicketException("Could not add Zoho API Ticket to table " . ZOHO_TICKET_TABLE . " in database " . DB_NAME . " error: " . $mysqli->error);
                     }
                }

            } else {
                throw new ZohoTicketException("Could not request new ticket via API: " . $curl_info['http_response']);
            }
        }
        return $this->ticket;
    }
}



class ZohoCRM {
    
    var $api_calls_remaining = True;

    var $VALID_MODULE_TYPES = array ("Leads", "Accounts", "Contacts", "Potentials", "Campaigns", "Tasks", "Events", "Cases", "Solutions", "Products", "PriceBooks", "Quotes", "Vendors", "PurchaseOrders", "SalesOrders", "Invoices", "Notes");
    
    public function __construct($module, $ticket, $api_key=DEFAULT_ZOHO_API_KEY) {
        $this->module = $module;
        $this->ticket = $ticket;
        $this->api_key = $api_key;
        if (!in_array ($this->module , $this->VALID_MODULE_TYPES )) {
            throw new ZohoException("$module is not a valid module type.");
        }
        if (!$this->ticket) {
            throw new ZohoException("You must include a Zoho API Ticket as the second parameter.\n");
        }
        if (!$this->api_key) {
            throw new ZohoException("You must include a Zoho API Key, either you did not include one, or the default key is missing. API KEY: " . $this->api_key . "\n");
        }
    }
    
    public function get_api_calls_remaining() {
        return $this->api_calls_remaining;
    }
    
    public function getMyRecords($select_columns = 'All', $from_index = 1, $to_index = 200, $sort_column_string = NULL, $sort_order_string = 'asc', $last_modified_time = NULL, $new_format = 1) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        
        // All is a special case in how Zoho deals with search columns
        if ($select_columns != 'All') {
            $select_columns = "$this->module($select_columns)";
        }
        
        // XML: http://crm.zoho.com/crm/private/xml/Leads/getRecords?newFormat=1&apikey=API Key&ticket=Ticket
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'selectColumns'=>$select_columns,
                                'fromIndex'=>$from_index,
                                'toIndex'=>$to_index,
                                );
        if ($sort_column_string != NULL) {
            $fields['sortColumnString'] = $sort_column_string;
            $fields['sortOrderString'] = $sortOrderString;
        }
        if ($last_modified_time != NULL) {
            $fields['lastModifiedTime'] = $last_modified_time;
        }
        $result = $this->postToAPI("getMyRecords", $fields);
        return $result;
    }
    
    public function getRecords($select_columns = 'All', $from_index = 1, $to_index = 200, $sort_column_string = NULL, $sort_order_string = 'asc', $last_modified_time = NULL, $new_format = 1) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        
        // All is a special case in how Zoho deals with search columns
        if ($select_columns != 'All') {
            $select_columns = "$this->module($select_columns)";
        }
        
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'selectColumns'=>$select_columns,
                                'fromIndex'=>$from_index,
                                'toIndex'=>$to_index,
                                );
        if ($sort_column_string != NULL) {
            $fields['sortColumnString'] = $sort_column_string;
            $fields['sortOrderString'] = $sort_order_string;
        }
        if ($last_modified_time != NULL) {
            $fields['lastModifiedTime'] = $last_modified_time;
        }
        
        $result = $this->postToAPI("getRecords", $fields);
        
        return $result;
    }
    
    public function getRecordById($id, $new_format = 1) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'id'=>$id
                                );
        if ($last_modified_time != NULL) {
            $fields['lastModifiedTime'] = $last_modified_time;
        }
        $result = $this->postToAPI("getRecordById", $fields);
        return $result;
    }
    
    public function getCVRecords($cv_name, $from_index = 1, $to_index = 200, $last_modified_time = NULL, $new_format = 1)  {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        if (!$cv_name) {
            throw new ZohoException('$cv_name is a required parameter for getCVRecords');
        }
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'cvName'=>$cv_name,
                                'fromIndex'=>$from_index,
                                'toIndex'=>$to_index,
                                );
        if ($last_modified_time != NULL) {
            $fields['lastModifiedTime'] = $last_modified_time;
        }
        $result = $this->postToAPI("getCVRecords", $fields);
        return $result;
    }
    
    public function getSearchRecords($search_condition, $select_columns = 'All', $new_format = 1) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        if (!$search_condition) {
            throw new ZohoException("$search_condition is a required parameter for getSearchRecords.\n");
        }
        $search_condition = "($search_condition)";
        
        // All is a special case in how Zoho deals with search columns
        if ($select_columns != 'All') {
            $select_columns = "$this->module($select_columns)";
        }
        
        
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'searchCondition'=>$search_condition,
                                'selectColumns'=>$select_columns
                                );
        $result = $this->postToAPI("getSearchRecords", $fields);
        return $result;
    }
    
    /***************************************************************************
     ********** UNIMPLEMENTED - THROWS ERROR IF ATTEMPTED **************
     ***************************************************************************/
    public function getSearchRecordsByPDC($search_column, $search_value, $select_columns = 'All', $new_format = 1) {
        
        throw new ZohoException('getSearchRecordsByPDC is not implemented.  It has a stupid interface and it\'s useless to me...\n');
        
    }
    
    public function insertRecords($rs, $new_format = 1, $wf_trigger=false, $is_approval=false, $duplicate_check=false)  {
        if (($is_approval == true) and  !($this->module == "Leads" or $this->module == "Contacts" or $this->module == "Cases")) {
            throw new ZohoException('Only Leads, Contacts and Cases can be queued for approval.');
        }
        if (!$rs) {
            throw new ZohoException('The $data associative array is a required parameter for insertRecords\n');
        }
        $xml_data = $this->convert_to_xml($rs);
        $fields = array( 'newFormat'=>$new_format,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'duplicateCheck'=>$duplicate_check,
                                'isApproval'=>$is_approval,
                                'wfTrigger'=>$wf_trigger
                                );
        $result = $this->postToAPI("insertRecords", $fields, $xml_data);
        return $result;
    }
    
    public function updateRecords($id, $rs, $new_format = 1, $wf_trigger=false, $is_approval=false, $duplicate_check=false)  {
        if (($is_approval == true) and ($this->module != "Leads" or $this->module != "Contacts" or $this->module != "Cases")) {
            throw new ZohoException('Only Leads, Contacts and Cases can be queued for approval.');
        } 
        if (!$id or !$rs) {
            throw new ZohoException('$id and the $data associative array are required parameters for updateRecords\n');
        }
        $xml_data = $this->convert_to_xml($rs);
        $fields = array( 'newFormat'=>$new_format,
                                'id'=>$id,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket,
                                'duplicateCheck'=>$duplicate_check,
                                'isApproval'=>$is_approval,
                                'wfTrigger'=>$wf_trigger,
                                );
        $result = $this->postToAPI("updateRecords", $fields, $xml_data);
        return $result;
    }
    
    public function deleteRecords($id) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }
        if (!$id) {
            throw new ZohoException('$id is a required parameter for deleteRecords');
        }
        $fields = array( 'id'=>$id,
                                'apikey'=>$this->api_key,
                                'ticket'=>$this->ticket
                                );
        $result = $this->postToAPI("deleteRecords", $fields);
        return $result;
    }

    public function convertLead($lead_id, $xml_data, $new_format = 1) {
        if ($this->module == "Notes") {
            throw new ZohoException('Notes can only be added or updated; this operation is not allowed.\n');
        }    
    }
    
     function convert_to_xml($rs) {

        // $rs (record set) MUST be an array of records, with each record an assoiciative array of key/value pairs.
        
        // SMight want to do some data validation before sending the request to be sure that the data is at least sane and has required fields...
        // validate_data($data);
                
        // write the opening header
        $xml_data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?><" . $this->module. ">";
        $row_num = 1;
        foreach ($rs as $records) {
            // for each record, Zoho expects a row number. Why?  Dunno...
            $xml_data .= "<row no=\"" . $row_num . "\">";
            foreach ($records as $key=>$value) {
                // Add the fields for each record.  Oddly, Zoho uses "val/content" instead of the standard terms "key/value"
                $value = "<![CDATA[" . $value . "]]>"; // wrap all character data in a CDATA wrapper
                $xml_data .= "<FL val=\"" . $key . "\">"  . $value . "</FL>";
            }
            $xml_data .= "</row>";
            $row_num += 1;
        }
        $xml_data .= "</" . $this->module. ">";
        return $xml_data;
     }

    function postToAPI($method, $fields, $xml_data=NULL) {
        
        $url = "http://crm.zoho.com/crm/private/json/$this->module/$method";
        
        //url-ify the data for the URL
        $fields_string = '';
        foreach($fields as $key=>$value) { 
            // get rid of any unwanted whitespace on ends of field value & URLEncode it before posting.
            if ($value != '') {
                $value = urlencode(trim($value));
                $fields_string .= $key.'='.$value.'&';
            }
        }
        
        print "URL : \n";
        print $url . "\n\n";
        
        print "XML Data (before it's urlencoded):\n";
        print $xml_data . "\n\n";
        
        // JFXB rtrim($fields_string, "& \n\t");
        if ($xml_data) {
            $xml_data = urlencode($xml_data);
            $fields_string .= "xmlData=$xml_data&";
        }         
        
        print "Post Data (field_string + xml_data:\n";
        print $fields_string . "\n\n";

        //open connection
        // TODO check for response and throw error after 10 failures 
        $ch = curl_init();
        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        //execute post
        $curl_count = 0;
        $json_response = curl_exec($ch);
        while ((!$json_response) && ($curl_count < 5)) {
            $curl_count +=1;
            print "API call failed; requested $curl_count times.\n";
        }
        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        
        if ($curl_info['http_response'] = 200) {
            $this->api_calls += 1;

            // JSON -> PHP array
            $response = json_decode($json_response, true);        
            
            // Check to see if the response is, in fact, a JSON response; sometimes Zoho sends an error as XML.  Zoho WTF spoecial case!
            if (!array_key_exists("response", $response)) {
                $error_code = 0;
                $error_msg = "Zoho API did not return a valid JSON response: $json_response";
                throw new ZohoException($error_msg, $error_code);
            }
            
            // Make sure there is a message string that we can return
             if (array_key_exists("message", $response['response'])) {
                $message = $response['response']['result']['message'];
            } else {
                $message = "";
            }
            
            // Process the error condition, set the API Limit flag is API has been exceeded
            if (array_key_exists("error", $response['response'])) {
                $error_code = $response['response']['error']['code'];
                $error_msg = $response['response']['error']['message'];
                if (($error_code == 4421) or ($error_code == 4101)) {
                    // 4421 means API calls are exceeded, 4101 means you exceeded your API search limit
                    $this->api_calls_remaining = False;
                }
                throw new ZohoException($error_msg, $error_code);
            }
            
            // Certain errors dont return an error, they return a nodata.  Another Zoho WTF Special case
            if (array_key_exists("nodata", $response['response'])) {
                $error_code = $response['response']['nodata']['code'];
                $error_msg = $response['response']['nodata']['message'];
                throw new ZohoException($error_msg, $error_code);
            }
            
            // The array created from JSON is poorly structured; let's make a clean recordset
            // Create an recrodset of each record returned, using the Zoho Row Number ('no') as the key.
            $rs = array();
            
            // Zoho likes to return similar, but ultimately different response structures based on the function called.
            // We look at the response structure and parse it so we return a nice, consistent recordset for PHP to use.
            if (array_key_exists("result", $response['response'])) {
                if (array_key_exists($this->module, $response['response']['result'])) {
                    $rows = $response['response']['result'][$this->module]['row'];
                } else if (array_key_exists(0, $response['response']['result']['recorddetail'])) {
                    $rows = $response['response']['result']['recorddetail'];
                } else if ($response['response']['result']['recorddetail']) {
                    $rows[0] = $response['response']['result']['recorddetail'];
                } else {
                    $rows = NULL;
                }
            }
    
            $row_num = 0;
            
            // Sometimes the response returns just one row, not an array of rows... so wrap it! TODO: fix upstream
            if (array_key_exists("no", $rows)) {
                $wrapper = array($rows);
                $rows = $wrapper;
            }
 
            if (count($rows)) {
                foreach ( $rows as $row ) {
                    //if ($row['no']) {
                    if (array_key_exists("no", $row)) {
                        $row_num = $row['no'];
                    }  else {
                        $row_num += 1;
                    }
                    $record = array();
                    //Convert the data from Zoho into a nice, convenient associative array to work with.
                    foreach ($row['FL'] as $field) {
                        $key = $field['val'];
                        $value = $field['content'];
                        $record[$key] = $value;
                    }
                    $rs[$row_num] = $record;
                }            
            }
        } else {
            $error_msg = "The API call returned an invalid HTTP Response: " . $curl_info['http_response'];
            throw new ZohoException($error_msg);
        }
        // return the data as a recordset and the raw response (useful for debugging, status messages)
        return array('rs'=>$rs, 'json'=>$json_response, 'message'=>$message);
    }
    
    /*******************************************************************************************
     *
     * Test functions; these can be removed from production as they query private methods
     * that need no public access, other than for testing purporses
     *

     ********************************************************************************************/
    
    public function test_convert_to_xml($rs) {
        $xml_data = $this->convert_to_xml($rs);
        return $xml_data;
    }
}

/**
 * Define a  very basic custom exception subclass for Zoho Exceptions
 */
class ZohoException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {

        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}

class ZohoTicketException extends Exception
{
    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}

?>

