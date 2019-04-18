<?php

require_once "database.php";

/*
 * Authenticates a user via cookies, returns nothing
 * just sets variables for user authentication in the database.
 */
//this is the name for the username cookies
DEFINE("USERNAME_COOKIE", "scusername");
//this is the name for the login pwd token
DEFINE("TOKEN_COOKIE", "sctoken");


function authenticateFromCookies() {
        //get a handle on the database
        $db = Database::getDB();

        //if cookies are set, attempt authentication
        if(isset($_COOKIE[USERNAME_COOKIE]) && isset($_COOKIE[TOKEN_COOKIE])) {
                $username = $_COOKIE[USERNAME_COOKIE];
                $token = $_COOKIE[TOKEN_COOKIE];

                //do the auth process in the db
                $db->authenticateUser($username, $token);
        }
}

//now actually call the auth function
authenticateFromCookies();
