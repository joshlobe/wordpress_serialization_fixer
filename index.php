<?php

// Define DB Variables
$servername = 'localhost';
$username  = 'root';
$password  = '';
$db   = 'serial';

// Define tables needing checking
// 'table' -> the name of the table
// 'key' -> the name of the unique key column of the table
// 'option' -> the name of the column holding the option name
// 'value' -> the name of the column holding the string to be checked
$tables = [ 
	[ 'table' => 'wp_bp_groups_groupmeta', 'key' => 'id', 'option' => 'meta_key', 'value' => 'meta_value' ],
	[ 'table' => 'wp_options', 'key' => 'option_id', 'option' => 'option_name', 'value' => 'option_value' ],
	[ 'table' => 'wp_postmeta', 'key' => 'meta_id', 'option' => 'meta_key', 'value' => 'meta_value' ],
	[ 'table' => 'wp_usermeta', 'key' => 'umeta_id', 'option' => 'meta_key', 'value' => 'meta_value' ]
];

// CHANGE NOTHING BELOW HERE

// Loop each table
foreach( $tables as $table ) {
	
	// Set counter to display to user if anything changed
	$count = 0;
	
	// Connect to DB
	$conn = new mysqli( $servername, $username, $password, $db );

	// Check connection
	if ($conn->connect_error) {

	  die( "Connection failed: " . $conn->connect_error );
	}
	
	// Display table being checked
	echo 'OPENING CONNECTION TO ' . $table['table'] . ' TABLE...<br />';
	
	// Get initial results
	$result = mysqli_query( $conn, 'SELECT * FROM ' . $table['table'] );
	
	// Loop results
	while( $row = $result->fetch_assoc() ) {

		$value_to_fix = $row[$table['value']];  // Get the serialized string
		$index = $row[$table['key']];  // Unique key column
		$fixed_value = recalculateSerializedString( $value_to_fix );  // Recalculate string

		// If the old value and new value to not match (need to update)
		if( $value_to_fix !== $fixed_value && $value_to_fix !== NULL ) {

			// Following used to output to browser
			echo ('CHANGING OPTION ID: ' . $index . ' : OPTION NAME: ' . $row[$table['option']] . ' ..... ');

			// Update query
			$update = mysqli_query( $conn, "UPDATE " . $table["table"] . " SET " . $table["value"] . "='" . mysqli_real_escape_string( $conn, $fixed_value ) . "' WHERE " . $table["key"] . "=" . $index );
			
			// Display if update succeeded
			$message = $update === true ? 'Successful' : 'Failed';
			echo $message . '<br />';
			
			// Increase count (if change happened)
			++$count;
		}
	}
	
	// Display records changed to user
	echo $count . ' ENTRIES UPDATED<br />';
	
	// Close DB connection
	$conn->close();
	
	// Display table being closed
	echo 'CLOSING CONNECTION TO ' . $table['table'] . ' TABLE<br /><br />';
}

// Function to recalculate serialized values
function recalculateSerializedString( $string ) {
	
	// Run through each serialized item (unserialized items will not be affected and return unchanged)
	$__ret = preg_replace_callback( '!s:(\d+):"(.*?)";!', function( $matches ) {
		
		// The callback function for preg_replace
		foreach( $matches as $match ) {
			
			// Example serialized data string
			// a:4:{i:0;s:4:"test";i:1;s:7:"testing";i:2;s:11:"muchtesting";i:3;s:3:"100";}
			// Example serialized callback match (multiple per serialized data string)
			// s:4:"test";
			
			// Take everything after the second colon of this match
			$new_string = implode( ':', array_slice( explode( ':', $match ), 2 ) );
			
			// Trim left apostrophe and right apostrophe and semicolon (leaves actual value)
			$trim = substr( $new_string, 1, -2 );
			
			// Rebuild correctly counted serialized data string
			return 's:' . strlen( $trim ) . ':"' . $trim . '";';
		}
	}, $string );
   
	// Return updated serialized string
   return $__ret;
}
