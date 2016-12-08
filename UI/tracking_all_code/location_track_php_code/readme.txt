Active RFID People / Asset Tracking System With Mesh Networking
http://www.ns-tech.co.uk/active-rfid-tracking-system/

Install:

1. Copy contents of folder "upload_to_server" to your server.
2. Open "includes/config.php" and edit $cfg['db_server'], $cfg['db_username'], $cfg['db_password'], $cfg['db_database'] to be your database details, and $cfg['site_url'] to be the URL to the site with a trailing slash e.g. "http://www.example.com/" or "http://www.example.com/locationtrack/" (if it has been uploaded to a sub folder named "paralleltrack".
3. Import database.sql into your database

----------

Login with username "demo@ns-tech.co.uk" and password "demo".

Tags, readers, maps, users can all be added/editied/deleted via the web interface after logging in.

The URL required by the Windows "PC Serial To HTTP Data Forwarder" application will be e.g. http://www.example.com/locationtrack/?p=clientapi&password=track111.  Note the password is specified on the "p" variable in the URL.  Password specified here must match the $cfg['client_api_password'] variable in "includes/config.php".

----------

"includes/pages/clientapi_test.php" is a test script that can be used to simulate sending data to the appliaction from the "PC Serial To HTTP Data Forwarder".  To run the test script go to url http://www.example.com/locationtrack/?p=clientapi_test this will cause the application to post each data set back to the server.

----------

The original version of code that attempted to do trilateration on the data, is located at "includes/pages/clientapi_trilat.php", it have been replaced woth "includes/pages/clientapi.php which simply plots tags next to the reader with the strongest signal.

To disable simple plotting, and re enable trilateration based plotting rename "clientapi_trilat.php" (this file) to "clientapi.php"

The actual function that does the trilateration calculation itself is in "includes/classes/appgeneral.php" named "trilateration()"

----------
