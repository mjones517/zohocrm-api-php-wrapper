This is a PHP wrapper class for the ZohoCRM API.  It takes some of the drudge work out of implementing an application that used the ZohoCRM API and also removed some of the oddities of the ZohoCRM API.

There are still many things that should be added at at least one API call (getSearchRecordsByPDC) that is not implemented in the wrapper class due to it's odd interface and the fact that I had no need for it.

Some improvements could be made as far as chaining user IDs so that more than 250 API calls could be made.

There are several examples of various API calls included with plenty of debug output so you can see what's happening.