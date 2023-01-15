<?php

namespace system;

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/config.php';

use system\library\Request;
use system\library\amocrm\amoCRM;
use system\library\mail\Mail;

class Core
{
  protected $request;
  protected $amoCRM;
  protected $config;

  public $errors = [];

  public function __construct($config)
  {
    $this->config = $config;
    $this->amoCRM = new amoCRM($this->config['amoCRM']);
    $this->request = new Request();
    $this->sendData();
  }

  public function sendData()
  {
    if ($this->request->server['REQUEST_METHOD'] === 'POST') {

      header('Content-Type: application/json');

      if ($this->validateForm()) {
        $data = $this->request->post;
        $this->sendMail($data);
        $this->sendLead($data);
        echo json_encode(['code' => 'success', 'message' => 'Данные успешно отправлены']);
      } else {
        echo json_encode(['code' => 'error-validate', 'message' => 'Есть ошибки в данных', 'errors' => $this->errors]);
      }
    }
  }

  public function sendLead($data) {
    $this->amoCRM->sendLead($data);
  }

  public function sendMail($data)
  {
    $mail = new Mail($this->config['mail']);
    $mail->setTo('order@salesgenerator.pro');
    $mail->setFrom('khodasevich.anton.eduardovich@gmail.com');
    $mail->setSubject("Заявка - Ходасевич");

    $text = "Письмо доставлено по протоколу SMTP, в связи с бесплатностью/ограниченностью хостинга." . "\n\n";
    $text .= "Вам пришла новая заявка" . "\n\n";
    $text .= "Почта: " . $data['email'] . "\n";
    $text .= "Телефон: " . $data['phone'] . "\n";

    $mail->setText($text);
    $mail->send();
  }

  public function validateForm() {

    $this->errors = [];

    if (!preg_match('/^[^@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->errors['email'] = true;
		}

    if (!preg_match('/^\+7\s\(\d{3}\)\s\d{3}\-\d{2}-\d{2}$/', $this->request->post['phone'])) {
			$this->errors['phone'] = true;
		}

    return !$this->errors;

  }
}

new Core($config);
