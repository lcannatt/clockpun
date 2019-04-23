<?php
// This script is meant to bounce back and forth with the registration to validate username availability
// And facilitate the finalized registration of the user.
// Response will be json object with format {usernameOK:true/false,regSuccess:true/false}
// Since this will be live pinging from reg screens ~once/sec, should probably implement rate limiting to prevent abuse.