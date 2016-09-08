<?php
namespace Magos\Util;

class Mail
{
	protected $body;
	protected $subject;
	protected $to;
	protected $cc;
	protected $attach;
	protected $username;
	protected $mail;

	public function __construct($config) {
		if (!is_array($config)) {
			throw new \Exception("La configuración de correo no ha sido definida", 1);
		}
		if (!empty($config['host'])
			&& !empty($config['port'])
			&& !empty($config['username'])
			&& !empty($config['password'])
			&& !empty($config['ssl'])
		) {
			$this->mail = new \Zend\Mail\Transport\Smtp();
			$options   = new \Zend\Mail\Transport\SmtpOptions(array(
				'name' => $config['host'],
				'host' => $config['host'],
				'port' => $config['port'],
				'connection_class'  => 'login',
				'connection_config' => array(
					'username' => $config['username'],
					'password' => $config['password'],
					'ssl' => $config['ssl'],
				),
			));
			$this->username = $config['username'];
			$this->mail->setOptions($options);
		}
	}

	public function composeMail($options) {
		if (array_key_exists('subject', $options)) {
			$this->subject = $options['subject'];
		}
		if (array_key_exists('to', $options)) {
			$this->to = $options['to'];
		}
		if (array_key_exists('attach', $options)) {
			$this->attach = $options['attach'];
		} else {
			$this->attach = array();
		}
		if (array_key_exists('body', $options)) {
			$this->body = $options['body'];
		}
	}

	public function send() {
		$data = array('success'=>false, 'msg' => '');
		try {
			$bodyMessage = new \Zend\Mime\Part($this->body);
			$bodyMessage->type = 'text/html';

			$partsMessage = array($bodyMessage);
			if(is_array($this->attach) && count($this->attach) > 0){
				foreach ($this->attach as $k => $file) {
					// Obtener Información del Adjunto
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mimetype = finfo_file($finfo, $file);
					finfo_close($finfo);
					$filename = basename($file);

					//Armar Adjunto
					$fileContent = fopen($file, 'r');
					$attachment = new \Zend\Mime\Part($fileContent);
					$attachment->type = $mimetype;
					$attachment->filename = $filename;
					$attachment->disposition = \Zend\Mime\Mime::DISPOSITION_ATTACHMENT;
					// Setting the encoding is recommended for binary data
					$attachment->encoding = \Zend\Mime\Mime::ENCODING_BASE64;
					array_push($partsMessage, $attachment);
				}
			}
			$bodyPart = new \Zend\Mime\Message();
			$bodyPart->setParts($partsMessage);

			$message = new \Zend\Mail\Message();
			$message->addTo($this->to)
				->setEncoding('UTF-8')
				->addFrom($this->username, 'ContabEc')
				->setSubject($this->subject)
				->setBody($bodyPart);

			$this->mail->send($message);
			$data['success'] = true;
		} catch(\Exception $e) {
			$data['success'] = false;
			$data['msg'] = $e->getMessage();
		}
		return $data;
	}
}
