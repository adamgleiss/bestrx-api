<?php

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!isset($templateParameters)) {
    throw new Exception("The bestrx-api pluging can't read its template variables");
}

?>

<div id="bestrx-message-div">
    <h1><?php echo $templateParameters['message'] ?> </h1>
    <p class="message">Need to submit another perscription? <a href="<?php echo $templateParameters['currentPageUrl'] ?>" >Click here.</a></p>
    <p>Thank you for your business.</p>
</div>