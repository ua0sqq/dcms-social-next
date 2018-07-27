<?php

if (isset($_SESSION['message'])) {
    echo '<div class="msg">' . "\n\t" . $_SESSION['message'] . "\n" . '</div>' . "\n";
    unset($_SESSION['message']);
}
