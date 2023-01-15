<?php

namespace system\library\mail;

class Mail {
  
  protected $protocol = 'mail';

  protected $to;
	protected $from;
	protected $subject;
	protected $text;

	protected $smtp_hostname;
	protected $smtp_username;
	protected $smtp_password;
	protected $smtp_port = 25;
	protected $smtp_timeout = 5;
  protected $verp = false;

  public function __construct($config)
  {
    foreach ($config as $key => $value) {
      $this->$key = $value;
    }
  }

	public function setTo($to) {
		$this->to = $to;
	}

	public function setFrom($from) {
		$this->from = $from;
	}

	public function setSubject($subject) {
		$this->subject = $subject;
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function send() {
		
    $this->validate();

		$header = 'MIME-Version: 1.0' . PHP_EOL;
		$header .= 'Date: ' . date('D, d M Y H:i:s O') . PHP_EOL;
		$header .= 'From: ' . $this->from . PHP_EOL;
		$header .= 'Reply-To: ' . $this->from . PHP_EOL;
		$header .= 'Return-Path: ' . $this->from . PHP_EOL;
		$header .= 'X-Mailer: PHP/' . phpversion() . PHP_EOL;

    if ($this->protocol != 'mail') {
			$header .= 'To: <' . $this->to . '>' . PHP_EOL;
			$header .= 'Subject: ' . $this->subject . PHP_EOL;
		}

    $message = 'Content-Type: text/plain; charset="utf-8"' . PHP_EOL;
    $message .= 'Content-Transfer-Encoding: 8bit' . PHP_EOL . PHP_EOL;
    $message .= $this->text . PHP_EOL;

    
    if ($this->protocol == 'mail') {

			ini_set('sendmail_from', $this->from);
			mail($this->to, $this->subject, $message, $header);

		} elseif ($this->protocol == 'smtp') {

			if (substr($this->smtp_hostname, 0, 3) == 'tls') {
				$hostname = substr($this->smtp_hostname, 6);
			} else {
				$hostname = $this->smtp_hostname;
			}

			$handle = fsockopen($hostname, $this->smtp_port, $errno, $errstr, $this->smtp_timeout);

			if (!$handle) {
        $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: ' . $errstr . ' (' . $errno . ')');
			} else {

				if (substr(PHP_OS, 0, 3) != 'WIN') {
					socket_set_timeout($handle, $this->smtp_timeout, 0);
				}
	
				while ($line = fgets($handle, 515)) {
					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					// Некоторые SMTP-серверы отвечают кодом 220 перед ответом 250. следовательно, нам нужно игнорировать строку ответа 220
					if (substr($reply, 0, 3) == 220 && substr($line, 3, 1) == ' ') {
						$reply = '';
						continue;
					}
					else if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
          $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: EHLO не принят с сервера!');
				}

				if (substr($this->smtp_hostname, 0, 3) == 'tls') {
					fputs($handle, 'STARTTLS' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 220) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: STARTTLS не принят от сервера!');
					}

					stream_socket_enable_crypto($handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
				}

				if (!empty($this->smtp_username)  && !empty($this->smtp_password)) {
					fputs($handle, 'EHLO ' . getenv('SERVER_NAME') . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: EHLO не принят с сервера!');
					}

					fputs($handle, 'AUTH LOGIN' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: AUTH LOGIN не принят от сервера!');
					}

					fputs($handle, base64_encode($this->smtp_username) . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 334) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Имя пользователя не принято с сервера!');
					}

					fputs($handle, base64_encode($this->smtp_password) . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 235) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Пароль не принят с сервера!');
					}
				} else {
					fputs($handle, 'HELO ' . getenv('SERVER_NAME') . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if (substr($reply, 0, 3) != 250) {
						throw new \Exception('Error: HELO not accepted from server!');
					}
				}

				if ($this->verp) {
					fputs($handle, 'MAIL FROM: <' . $this->from . '>XVERP' . "\r\n");
				} else {
					fputs($handle, 'MAIL FROM: <' . $this->from . '>' . "\r\n");
				}

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
          $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: MAIL FROM не принят с сервера!');
				}

				if (!is_array($this->to)) {
					fputs($handle, 'RCPT TO: <' . $this->to . '>' . "\r\n");

					$reply = '';

					while ($line = fgets($handle, 515)) {
						$reply .= $line;

						if (substr($line, 3, 1) == ' ') {
							break;
						}
					}

					if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
            $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: RCPT TO не принят с сервера!');
					}

				} else {
					foreach ($this->to as $recipient) {
						fputs($handle, 'RCPT TO: <' . $recipient . '>' . "\r\n");

						$reply = '';

						while ($line = fgets($handle, 515)) {
							$reply .= $line;

							if (substr($line, 3, 1) == ' ') {
								break;
							}
						}

						if ((substr($reply, 0, 3) != 250) && (substr($reply, 0, 3) != 251)) {
              $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: RCPT TO не принят с сервера!');
						}
					}
				}

				fputs($handle, 'DATA' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 354) {
          $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: DATA не приняты с сервера!');
				}

				// Согласно RFC 821 мы не должны отправлять более 1000, включая CRLF.
				$message = str_replace("\r\n", "\n", $header . $message);
				$message = str_replace("\r", "\n", $message);

				$lines = explode("\n", $message);

				foreach ($lines as $line) {
					$results = str_split($line, 998);

					foreach ($results as $result) {
						if (substr(PHP_OS, 0, 3) != 'WIN') {
							fputs($handle, $result . "\r\n");
						} else {
							fputs($handle, str_replace("\n", "\r\n", $result) . "\r\n");
						}
					}
				}

				fputs($handle, '.' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 250) {
          $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: DATA не приняты с сервера!');
				}

				fputs($handle, 'QUIT' . "\r\n");

				$reply = '';

				while ($line = fgets($handle, 515)) {
					$reply .= $line;

					if (substr($line, 3, 1) == ' ') {
						break;
					}
				}

				if (substr($reply, 0, 3) != 221) {
          $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: QUIT не принят от сервера!');
				}

				fclose($handle);
			}
		}
	}

  public function validate() {
    if (!$this->to) {
      $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Укажите электроную почту на которую отправляется письмо!');
		}

		if (!$this->from) {
      $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Укажите электроную почту с которой отправляется письмо!');
		}

		if (!$this->subject) {
      $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Укажите заголовок письма!');
		}

		if (!$this->text) {
      $this->logError('Дата: ' . date("Y-m-d H:i:s") . PHP_EOL . 'Ошибка: Укажите текст письма!');
		}
  }

  public function logError($text){
    file_put_contents(__DIR__ . '/log-error.txt', $text . PHP_EOL, FILE_APPEND);
    echo json_encode(['code' => 'core-error', 'message' => $text]);
    die;
  }

}
