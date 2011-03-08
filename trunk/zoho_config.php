<?php

// Store default API User/Pass here
define('DEFAULT_ZOHO_USER', 'your-default-user');
define('DEFAULT_ZOHO_PASS', 'your-default-pass');
// See http://zohocrmapi.wiki.zoho.com/Generating-API-Ticket.html
// For instructions on generating a Zoho API Key
define('DEFAULT_ZOHO_API_KEY', 'your-api-key-here');  


// To create persistance for tickets, create a ticket table in your DB,
// an empty ticket table will not persist tickets (persistance is recommended by zoho)
//define('ZOHO_TICKET_TABLE', 'zoho_tickets');
define('ZOHO_TICKET_TABLE', '');

// DB Credentials if you want Zoho Ticket to be persisted in the database
/* Zoho tickets are valid for one week.  Caching the ticket in a local database
 * will speed up API calls over generating a new API ticket every time a class is
 * instantiated.
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'your-dbuser');
define('DB_PASS', 'your-dbpass');
define('DB_NAME', 'your-dbname');

?>