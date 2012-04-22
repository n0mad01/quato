<?php

//echo '<pre>';
//print_r($_SERVER);
//print_r($_GET);

if (!defined('CSS_DIR')) {
    define('CSS_DIR', 'http://' . $_SERVER['SERVER_NAME'] . '/app/webroot/css/');
}
if (!defined('JS_DIR')) {
    define('JS_DIR', 'http://' . $_SERVER['SERVER_NAME'] . '/app/webroot/js/');
}

require('app/classes/dispatcher.php');

$Dispatcher = new Dispatcher();
$Dispatcher->dispatch();

?>

<!-- Piwik -->
<script type="text/javascript">
/*var pkBaseURL = (("https:" == document.location.protocol) ? "https://www.soluch.at/other/piwik/" : "http://www.soluch.at/other/piwik/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));*/
</script><script type="text/javascript">
/*try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 2);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}*/
</script><noscript><p><img src="http://www.soluch.at/other/piwik/piwik.php?idsite=2" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tag -->
