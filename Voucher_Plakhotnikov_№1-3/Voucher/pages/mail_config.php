<?php

define('SMTP_HOST', getenv('SMTP_HOST'));
define('SMTP_PORT', (int) getenv('SMTP_PORT'));
define('SMTP_USER', getenv('SMTP_USER'));
define('SMTP_PASS', getenv('SMTP_PASS'));
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME'));
