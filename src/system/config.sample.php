<?php 

$config = [
  'amoCRM' => [
    'subdomain'     => '',                  // пример: kislovodskayak
    'client_secret' => '',                  // пример: D9aZ6vOhhIPrxfp53PgYYwtnVTic6ddsNSB4Lbcefh5ysyelz30wFbTd3canXv3N
    'client_id'     => '',                  // пример: 2e7af213-9172-4147-331e-c112a36ae8a3
    'code'          => '',                  // пример: def501005cacdfa5f18aa1329c8413611b3b1ca2fabb10f...
    'token_file'    => 'tokens.txt',
    'redirect_uri'  => '',                  // пример: http://n92361h9.beget.tech/system/library/amocrm/amocrm.php
  ],
  'mail' => [
    'protocol' => '',                       // пример: mail || smtp
	  'smtp_hostname' => '',                  // пример: tls://smtp.gmail.com
	  'smtp_username' => '',                  // пример: myexample@gmail.com
	  'smtp_password' => '',
	  'smtp_port' => '',                      // пример: 587 || 465
	  'smtp_timeout' => '5',
  ],
];