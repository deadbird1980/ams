<?php
function ams_error_handler($number, $message, $file, $line, $vars)
{
    $email = "
        <p>An error ($number) occurred on line
        <strong>$line</strong> and in the <strong>file: $file.</strong>
        <p> $message </p>";
    $email .= "<pre>" . print_r($vars, 1) . "</pre>";
    if ($session = Doo::session('ams')) {
        $email .= "<p>User ID:{$session->user->id}</p>";
    }
    $error_email = Doo::conf()->error_email;
    $headers = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    // Email the error to someone...
    error_log($email, 1, $error_email, $headers);
    // Make sure that you decide how to respond to errors (on the user's side)
    // Either echo an error message, or kill the entire project. Up to you...
    // The code below ensures that we only "die" if the error was more than
    // just a NOTICE.
    if ( ($number !== E_NOTICE) && ($number < 2048) ) {
        die("There was an error. Please try again later.");
    }
}
set_error_handler('ams_error_handler');
?>
