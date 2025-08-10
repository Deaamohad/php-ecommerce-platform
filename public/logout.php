<?php
session_start();

session_destroy();
header("Location: login?success=logged-out");
exit();
?>
