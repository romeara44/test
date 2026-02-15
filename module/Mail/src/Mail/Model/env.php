<?php
  $variables = [
      'SENDGRID_API_KEY' => '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',
      'DB_HOST' => 'localhost',
      'DB_USERNAME' => 'root',
      'DB_PASSWORD' => '',
      'DB_NAME' => 'demoDB',
      'DB_PORT' => '3306',
  ];

  foreach ($variables as $key => $value) {
      putenv("$key=$value");
  }
?>