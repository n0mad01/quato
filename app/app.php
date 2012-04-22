<?php
/**
 *	app/classes/app.php
 *	a collection of "global" functions & variables
 */


/**
 *  A simple message/error dump
 */
function dumper($error) {

    echo '<pre>';
    echo '<br />' .  print_r($error) . '</pre><br />';
    echo '<hr>';
}

