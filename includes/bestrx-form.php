<?php

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!isset($templateParameters)) {
    throw new Exception("The bestrx-api pluging can't read its template variables");
}

?>

<form id="bestrx-refill-form"
      class="bestrx-refill"
      METHOD="post"
      action="<?php echo $templateParameters['currentPageUrl'] ?>">

    <?php wp_nonce_field('bestrx-nonce'); ?>

    <?php
    if ($templateParameters['generalError'] ?? false) {
        echo '<span class="error">' . $templateParameters['generalError'] . '</span>';
    }
    ?>

  <div>
    <label class="bestrx-required" for="bestrx-lastname">Last Name</label>
    <div>
        <?php
        if ($templateParameters['errors']['name'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['name'] . '</span>';
        }
        ?>
      <input id="bestrx-lastname"
             name="bestrx-lastname"
             type="text"
             value="<?php echo($templateParameters['name'] ?? '') ?> "
             size="8"
             tabindex="1"
             required>
    </div>
  </div>

  <div>
    <label class="bestrx-required" for="bestrx-dob">Date of Birth</label>
    <div>
        <?php
        if ($templateParameters['errors']['dob'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['dob'] . '</span>';
        }
        ?>
      <input id="bestrx-dob"
             name="bestrx-dob"
             type="date"
             value="<?php echo($templateParameters['dob'] ?? '') ?>"
             tabindex="2"
             required>
    </div>
  </div>

  <div>
    <label class="bestrx-required" for="bestrx-delivery">Delivery Option</label>
    <div>
        <?php
        if ($templateParameters['errors']['deliver'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['deliver'] . '</span>';
        }
        ?>
      <select id="bestrx-delivery" name="bestrx-delivery" tabindex="3" required>
        <option value="DELIVERY" <?php echo($templateParameters['deliver'] == 'DELIVERY' ? 'selected' : '') ?>>Delivery</option>
        <option value="PICKUP" <?php echo($templateParameters['deliver'] == 'PICKUP' ? 'selected' : '') ?>>Pickup</option>
        <option value="MAIL" <?php echo($templateParameters['deliver'] == 'MAIL' ? 'selected' : '') ?>>Mail</option>
      </select>
    </div>
  </div>

  <div>
    <label class="bestrx-required" for="bestrx-rxnumber">Rx Number</label>
    <div>
        <?php
        if ($templateParameters['errors']['rxNumber'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['rxNumber'] . '</span>';
        }
        ?>
      <input id="bestrx-rxnumber"
             name="bestrx-rxnumber"
             type="text"
             class="field text fn"
             value="<?php echo($templateParameters['rxNumber'] ?? '') ?>"
             size="8"
             tabindex="4"
             required>
    </div>
  </div>

  <div>
    <label for="bestrx-phone">Phone Number</label>
    <div>
        <?php
        if ($templateParameters['errors']['phone'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['phone'] . '</span>';
        }
        ?>
      <input id="bestrx-phone"
             name="bestrx-phone"
             type="text"
             class="field text fn"
             value="<?php echo($templateParameters['phone'] ?? '') ?>"
             size="8"
             tabindex="5">
    </div>
  </div>

  <div>
    <label for="bestrx-notes">Anything else we need to know about this perscription?</label>
    <div>
        <?php
        if ($templateParameters['errors']['phone'] ?? false) {
            echo '<span class="error">' . $templateParameters['errors']['phone'] . '</span>';
        }
        ?>
      <textarea id="bestrx-notes"
             name="bestrx-notes"
             class="field text fn"
             rows="10"
             tabindex="6"><?php echo($templateParameters['notes'] ?? '') ?></textarea>
    </div>
  </div>

  <div><p class="bestrx-required-key">* required</p></div>

  <div>
    <div>
      <input name="bestrx-submitted" type="submit" value="Submit">
    </div>
  </div>


</form>