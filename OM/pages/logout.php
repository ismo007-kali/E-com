<?php
session_start();
session_destroy();
header('Location: ?page=login');
exit();
