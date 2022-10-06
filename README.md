# Wordpress Serialization Fixer
Fixes errors in data serialization in WordPress database.

This repo is built upon original code by ''.  
We had changed urls using a search and replace on a sql file; and destroyed the serialization counts.  
This, in turn, destroyed our options tables when calling them back through WP.

The original code threw errors with PHP 8 and outdated mysql functions.  
This new code is updated to work with new versions of PHP and mysqli.  
It is also updated to use a callback on the preg_replace (original generated php error).

USE THIS CODE AT YOUR OWN RISK!!  
Please make a backup of the database first.  

USAGE  
Lines 4 through 7 need to be altered with the correct DB credentials.  
Line 14 is an array.  Each table needing checking can be added to the array.  
