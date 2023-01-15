<?php

namespace system\library\amocrm;

class amoCRM {

  // Errors
  public $errors = [
    301 => 'Moved permanently.',
    400 => 'Wrong structure of the array of transmitted data, or invalid identifiers of custom fields.',
    401 => 'Not Authorized. There is no account information on the server. You need to make a request to another server on the transmitted IP.',
    403 => 'The account is blocked, for repeatedly exceeding the number of requests per second.',
    404 => 'Not found.',
    500 => 'Internal server error.',
    502 => 'Bad gateway.',
    503 => 'Service unavailable.'
  ];

  // Config
  public $subdomain;
  public $client_secret;
  public $client_id;
  public $code;
  public $token_file;
  public $redirect_uri;

  // Token
  public $access_token;

  public function __construct($config)
  {
    foreach ($config as $key => $value) {
      $this->$key = $value;
    }
  }

  public function getToken() {
    
    $link = "https://" . $this->subdomain . ".amocrm.ru/oauth2/access_token";

    $postfields = [
      'client_id'     => $this->client_id,
      'client_secret' => $this->client_secret,
      'grant_type'    => 'authorization_code',
      'code'          => $this->code,
      'redirect_uri'  => $this->redirect_uri,
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT,'amoCRM-oAuth-client/1.0');
    curl_setopt($curl, CURLOPT_URL, $link);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postfields));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $code = (int)$code;
    $this->checkError($code);

    $response = json_decode($out, true);     

    $dataAuth = [
      "access_token"  => $response['access_token'],     
      "refresh_token" => $response['refresh_token'],
      "token_type"    => $response['token_type'],
      "expires_in"    => $response['expires_in'],
      "endTokenTime"  => $response['expires_in'] + time(),
    ];

    $f = fopen($this->token_file, 'w');
    fwrite($f, json_encode($dataAuth));
    fclose($f);

    print_r($dataAuth);
  }

  public function updateToken() {

    $localToken = json_decode(file_get_contents(__DIR__ . '/' . $this->token_file), true);

    if ($localToken["endTokenTime"] - 60 < time()) {

      $link = "https://" . $this->subdomain . ".amocrm.ru/oauth2/access_token";

      $postfields = [
        'client_id'     => $this->client_id,
        'client_secret' => $this->client_secret,
        'grant_type'    => 'refresh_token',
        'refresh_token' => $localToken["refresh_token"],
        'redirect_uri'  => $this->redirect_uri,
      ];

      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-oAuth-client/1.0');
      curl_setopt($curl, CURLOPT_URL, $link);
      curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
      curl_setopt($curl, CURLOPT_HEADER, false);
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postfields));
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
      $out = curl_exec($curl);
      $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      curl_close($curl);

      $code = (int)$code;
      $this->checkError($code);

      $response = json_decode($out, true);

      $dataAuth = [
        "access_token"  => $response['access_token'],        
        "refresh_token" => $response['refresh_token'],        
        "token_type"    => $response['token_type'],         
        "expires_in"    => $response['expires_in'],          
        "endTokenTime"  => $response['expires_in'] + time(),
      ];
      
      $f = fopen($this->token_file, 'w');
      fwrite($f, json_encode($dataAuth));
      fclose($f);

      $this->access_token = $response['access_token'];

    } else {
      $this->access_token = $localToken["access_token"];
    }
  }

  public function sendLead($data) {

    $this->updateToken();

    $name = 'Заявка - Ходасевич';
    $phone = $data['phone'];
    $email = $data['email'];

    $postfields = [
      [
        "name" => $name,
        "created_by" => 0,
        "created_at" => time(),
        "_embedded" => [
          "contacts" => [
            [
              "first_name" => "Новый клиент",
              "custom_fields_values" => [
                [
                  "field_code" => "EMAIL",
                  "values" => [
                    [
                      "enum_code" => "WORK",
                      "value" => $email
                    ]
                  ]
                ],
                [
                  "field_code" => "PHONE",
                  "values" => [
                    [
                      "enum_code" => "WORK",
                      "value" => $phone
                    ]
                  ]
                ],
              ]
            ]
          ],
        ],
      ]
    ];

    $method = "/api/v4/leads/complex";

    $headers = [
      'Content-Type: application/json',
      'Authorization: Bearer ' . $this->access_token,
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
    curl_setopt($curl, CURLOPT_URL, "https://" . $this->subdomain . ".amocrm.ru" . $method);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postfields));
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($curl, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $out = curl_exec($curl);
    $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $code = (int) $code;

    $this->checkError($code, $postfields);
  }

  public function getInfo() {

    header('Content-Type: text/html; charset=utf-8');

    function printLink($method, $title, $subdomain) {
      echo '<br>';
      echo "<a href='https://$subdomain.amocrm.ru/$method' target='_blank'>$title</a>";
      echo '<br>';
    }
    
    printLink('api/v4/leads/custom_fields', 'Список utm меток', $this->subdomain);
    printLink('api/v4/users', 'Список пользователей', $this->subdomain);
    printLink('api/v4/contacts/custom_fields', 'Список полей контакта', $this->subdomain);
    
    echo '<br>';
    echo "<a href='https://www.amocrm.ru/developers/content/crm_platform/custom-fields' target='_blank'>Документация</a>";
    echo '<br>';
  }

  public function checkError($code, $postfields = '') {
    try
    {
      if ($code < 200 || $code > 204) {
        throw new \Exception(isset($this->errors[$code]) ? $this->errors[$code] : 'Undefined error', $code);
      }
    }
    catch(\Exception $e)
    {
      $message = 'Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Код ошибки: ' . $e->getCode() . PHP_EOL . 'Ошибка: ' . $e->getMessage() . PHP_EOL;
      if ($postfields) $message .= 'Данные: ' . json_encode($postfields) . PHP_EOL;
      $this->logError($message);
      die;
    }
  }

  public function logError($text){
    file_put_contents(__DIR__ . '/log-error.txt', $text, FILE_APPEND);
    echo json_encode(['code' => 'core-error', 'message' => $text]);
    die;
  }
  
}