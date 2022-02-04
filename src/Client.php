<?php 

namespace Mahtab2003\MOARC;

/**
 * Title: NOARC
 * Description: A free library to manage signup, login in php. 
 * Version: v0.1 alpha
 * Build Date: 26 Jan 2022
 * Developer: mahtab2003
 * Contact: mahtabhassan159@gmail.com
 */

class Client
{
	private $db;
	private $mofh;
	private $config;
	private $table_prefix = 'is_'; 
	private $cookie_name = 'client';
	private $config_file = __DIR__.'/../config.php';
	private $include_dir = __DIR__.'/../';
	private $template_dir = __DIR__.'/../';

	function __construct()
	{
		ob_start();
		session_start();
		ini_set('max_execution_time', 120);
		$this->getConfig();
		$this->initDB();
		$this->Autoload();
		$this->initMOFH();
	}

	public function setTitle(string $title)
	{
		$this->title = $title.' - '.$this->getHostInfo('name');
	}

	public function setLink(string $type, string $uri)
	{
		$this->link[] = [$type, $uri];
	}

	private function getTitle()
	{
		if(isset($this->title))
		{
			return $this->title;
		}
		else
		{
			throw new \Exception('Page title not set!');
		}
	}

	private function getLink()
	{
		if(isset($this->link))
		{
			return $this->link;
		}
		else
		{
			throw new \Exception('Page link not set!');
		}
	}

	private function getConfig()
	{
		if(file_exists($this->config_file))
		{
			include $this->config_file;
			$this->config = $Config;
		}
		else
		{
			throw new \Exception('Configuration file not found!');
		}
	}

	private function initDB()
	{
		if(isset($this->config['db']))
		{
			$this->db = new \mysqli;
			$this->db->connect(
				$this->config['db']['hostname'],
				$this->config['db']['username'],
				$this->config['db']['password'],
				$this->config['db']['database']
			);
		}
		else
		{
			throw new \Exception('Database configuration not found!');
		}
	}

	private function Autoload()
	{
		if(count($this->config['vendor']) > 0)
		{
			for($i = 0; $i < count($this->config['vendor']); $i++)
			{
				if(file_exists($this->config['vendor'][$i]))
				{
					include $this->config['vendor'][$i];
				}
			}
		}
	}

	public function loadDocPart(string $filename)
	{
		if(file_exists($this->include_dir.$filename.'.php'))
		{
			include $this->include_dir.$filename.'.php';
		}
		else
		{
			throw new \Exception("'".$filename."' include file not found!");
		}
	}

	public function loadDocBody(string $filename)
	{
		if(file_exists($this->template_dir.$filename.'.php'))
		{
			include $this->template_dir.$filename.'.php';
		}
		else
		{
			throw new \Exception("'".$filename."' template file not found!");
		}
	}

	public function getHostInfo(string $field = 'all')
	{
		$sql = $this->db->query("SELECT * FROM `".$this->table_prefix."base` WHERE `base_id` = 'hostbase'");
		if($sql)
		{
			$data = $sql->fetch_assoc();
			$returnData = [
				'name' => $data['base_name'],
				'email' => $data['base_email'],
				'status' => '',
				'uri' => rtrim($data['base_url'],'/').'/'
			];
			if($data['base_status'] == 1)
			{
				$returnData['status'] = "active";
			}
			else
			{
				$returnData['status'] = "inactive";
			}
			if($field == 'all')
			{
				return [
					'status' => 'success', 
					'data' => $returnData
				];
			}
			elseif(isset($returnData[$field])){
				return [
					'status' => 'success', 
					'data' => $returnData[$field]
				];
			}
			else{
				return [
					'status' => 'failed', 
					'data' => "Invalid index '".$field."' requested!"
				];
			}
		}
		else
		{
			throw new \Exception("Invalid SQL query syntax!");
		}
	}

	private function validateData(string $Data, string $type = 'string')
	{
		$Data = trim($Data);
		if($type == 'string')
		{
			$Data = $this->db->real_escape_string($Data);
			$Data = htmlentities($Data);
			return [
				'status' => 'success', 
				'data' => $Data
			];
		}
		elseif($type == 'email')
		{
			if(!mb_ereg("^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,3})$", $Data))
			{
				return [
					'status' => 'failed', 
					'data' => "Invalid email address format!"
				];
			}
			else
			{
				$Data = $this->db->real_escape_string($Data);
				return [
					'status' => 'success', 
					'data' => $Data
				];
			}
		}
		elseif($type == 'domain')
		{
			if(strlen($Data) < 4)
			{
				return [
					'status' => 'failed',
					'data' => 'Domain name must have at least 5 characters!'
				];
			}
			else
			{
				if(preg_match('^@!&%*$#~\/^', $Data))
				{
					return [
						'status' => 'failed', 
						'data' => "The domain name should not use illegal characters!"
					];
				}
				else
				{
					$Data = $this->db->real_escape_string($Data);
					$Data = htmlentities($Data);
					return [
						'status' => 'success', 
						'data' => $Data
					];
				}
			}
		}
		elseif($type == 'name')
		{
			if(preg_match('^@!&%*$#~\/^', $Data))
			{
				return [
					'status' => 'failed', 
					'data' => "The name should not use illegal characters!"
				];
			}
			else
			{
				$Data = $this->db->real_escape_string($Data);
				$Data = htmlentities($Data);
				return [
					'status' => 'success', 
					'data' => $Data
				];
			}
		}
		elseif($type == 'textarea')
		{
			$Data = $this->db->real_escape_string($Data);
			return [
				'status' => 'success', 
				'data' => $Data
			];
		}
		else{
			return [
				'status' => 'failed',
				'data' => 'Invalid data type provided!'
			];
		}
	}

	private function getUnloggedClientInfo(string $email, string $field = '')
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$email = $isVaildEmail['data'];
			$sql = $this->db->query("SELECT `client_password`,`client_key`,`client_recovery_key`,`client_status` FROM `".$this->table_prefix."client` WHERE `client_email` = '".$email."'");
			if($sql->num_rows > 0)
			{
				$data = $sql->fetch_assoc();
				$returnData = [
					'client_key' => $data['client_key'],
					'password_hash' => $data['client_password'],
					'recovery_key' => $data['client_recovery_key'],
					'client_status' => $data['client_status']
				];
				if($data['client_status'] == 1)
				{
					$returnData['client_status'] = "verified";
				}
				else
				{
					$returnData['client_status'] = "unverified";
				}
				if($field == '')
				{
					return [
						'status' => 'success', 
						'data' => $returnData
					];
				}
				elseif(isset($returnData[$field]))
				{
					return [
						'status' => 'success', 
						'data' => $returnData[$field]
					];
				}
				else
				{
					return [
						'status' => 'failed', 
						'data' => "Invalid index '".$field."' requested!"
					];
				}
			}
			else
			{
				return [
					'status' => 'failed', 
					'data' => "Client with this email address doesn't exsists!"
				];
			}
		}
	}

	private function isRegistered(string $email)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$email = $isVaildEmail['data'];
			$sql = $this->db->query("SELECT `client_id` FROM `".$this->table_prefix."client` WHERE `client_email` = '".$email."'");
			if($sql->num_rows > 0)
			{
				return [
					'status' => 'success', 
					'data' => "Client already exsists with this email address!"
				];
			}
			else
			{
				return [
					'status' => 'failed', 
					'data' => "Client with this email address doesn't exsists!"
				];
			}
		}
	}

	private function createRecoveryKey()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 16);
		return $string;
	}

	private function createTicketID()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 8);
		return $string;
	}

	private function createClientKey()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 10);
		return $string;
	}

	private function createHostingUsername()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 8);
		return $string;
	}

	private function createHostingPassword()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 18);
		return $string;
	}

	private function hashPassword(string $password)
	{
		$password = trim($password);
		$password = hash('sha256', $password);
		return $password;
	}

	private function verifyPassword(string $password1, string $password2)
	{
		if(hash_equals($password2, $this->hashPassword($password1)))
		{
			return [
				'status' => 'success',
				'data' => 'Password matched successfully!'
			];
		}
		else{
			return [
				'status' => 'failed',
				'data' => "Password doesn't matched!"
			];
		}
	}

	public function registerClient(string $name, string $email, string $password)
	{
		$isRegistered = $this->isRegistered($email);
		if($isRegistered['status'] == 'success')
		{
			return $isRegistered;
		}
		else
		{
			$isVaildName = $this->validateData($name, 'name');
			$isVaildEmail = $this->validateData($email, 'email');
			if($isVaildName['status'] == 'failed')
			{
				return $isVaildName;
			}
			elseif($isVaildEmail['status'] == 'failed')
			{
				return $isVaildEmail;
			}
			else
			{
				$name = $isVaildName['data'];
				$email = $isVaildEmail['data'];
				$password = $this->hashPassword($password);
				$client_key = $this->createClientKey();
				$recovery_key = $this->createRecoveryKey();
				$time = time();
				$client_status = 0;
				$sql = $this->db->query("INSERT INTO `".$this->table_prefix."client`(
					`client_name`,
					`client_email`,
					`client_password`,
					`client_status`,
					`client_date`,
					`client_key`,
					`client_recovery_key`
				) VALUES(
					'".$name."',
					'".$email."',
					'".$password."',
					'".$client_status."',
					'".$time."',
					'".$client_key."',
					'".$recovery_key."'
				)");
				if($sql)
				{
					return [
						'status' => 'success',
						'data' => 'Client account registered successfully!'
					];
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => "Something went's wrong while registering new account!"
					];
				}
			}
		}
	}

	public function logClientIn(string $email, string $password, int $days = 1)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$isRegistered = $this->isRegistered($email);
			if($isRegistered['status'] == 'failed')
			{
				return $isRegistered;
			}
			else
			{
				$email = $isVaildEmail['data'];
				$password1 = $this->getUnloggedClientInfo($email, 'password_hash');
				$client_key = $this->getUnloggedClientInfo($email, 'client_key');
				$validatePassword = $this->verifyPassword($password, $password1['data']);
				if($validatePassword['status'] == 'success')
				{
					setcookie(
						$this->cookie_name,
						base64_encode(
							gzcompress(
								json_encode(
									[
										$email,
										md5($client_key['data'])
									]
								)
							)
						),
						time() + $days * 86400,
						'/'
					);
					return [
						'status' => 'success',
						'data' => 'Client logged in successfully!'
					];
				}
				else
				{
					return $validatePassword;
				}
			}
		}
	}

	public function isLogged()
	{
		if(isset($_COOKIE[$this->cookie_name]))
		{
			$CookieData = json_decode(
							gzuncompress(
								base64_decode(
									$_COOKIE[$this->cookie_name]
								)
							)
						);
			$isVaildEmail = $this->validateData($CookieData[0], 'email');
			$isVaildHash = $this->validateData($CookieData[1], 'string');
			if($isVaildEmail['status'] == 'failed')
			{
				return $isVaildEmail;
			}
			elseif($isVaildHash['status'] == 'failed')
			{
				return $isVaildHash;
			}
			else
			{

				$CookieData[0] = $isVaildEmail['data'];
				$isRegistered = $this->isRegistered($CookieData[0]);
				if($isRegistered['status'] == 'failed')
				{
					setcookie(
						$this->cookie_name,
						NULL,
						-1,
						'/'
					);
					return $isRegistered;
				}
				else
				{
					$hash = $this->getUnloggedClientInfo($CookieData[0], 'client_key');
					if(hash_equals($CookieData[1], md5($hash['data'])))
					{
						return [
							'status' => 'success',
							'data' => 'Login data is successfully verified'
						];
					}
					else
					{
						return [
							'status' => 'failed',
							'data' => 'Invaild login data provided!'
						];
					}
				}
			}
		}
		else
		{
			return [
				'status' => 'failed',
				'data' => 'No client currently logged in!'
			];
		}
	}

	public function logClientOut()
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'success')
		{
			setcookie(
				$this->cookie_name,
				NULL,
				-1,
				'/'
			);
			return [
				'status' => 'success',
				'data' => 'Client logged out successfully!'
			];
		}
		else
		{
			return $isLogged;
		}
	}

	public function isVerifiedClient(string $email)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$isRegistered = $this->isRegistered($email);
			if($isRegistered['status'] == 'failed')
			{
				return $isRegistered;
			}
			else
			{

				$email = $isVaildEmail['data'];
				$client_status = $this->getUnloggedClientInfo($email, 'client_status');
				return [
					'status' => 'success',
					'data' => $client_status
				];
			}
		}
	}

	public function resetClientPassword(string $email, string $recovery_key, string $password)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$isRegistered = $this->isRegistered($email);
			if($isRegistered['status'] == 'failed')
			{
				return $isRegistered;
			}
			else
			{
				$recovery_key1 = $this->getUnloggedClientInfo($email, 'recovery_key');
				if($recovery_key == $recovery_key1['data'])
				{
					$email = $isVaildEmail['data'];
					$sql = $this->db->query('UPDATE `'.$this->table_prefix.'client` SET `client_password` = "'.$this->hashPassword($password).'" WHERE `client_email` = "'.$email.'"');
					if($sql)
					{
						return [
							'status' => 'failed',
							'data' => 'Client password reset successfully!'
						];
					}
					else
					{
						return [
							'status' => 'failed',
							'data' => "Something went's wrong while processing this request!"
						];
					}
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => 'Invaild recovery key provided!'
					];
				}
			}
		}
	}

	public function changeClientPassword(string $old_password, string $new_password)
	{
		$old_password = $this->validateData($old_password, 'string')['data'];
		$new_password = $this->validateData($new_password, 'string')['data'];
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$email = $this->getLoggedClientInfo('email')['data'];
			$old_password_hash = $this->getUnloggedClientInfo($email ,'password_hash')['data'];
			$verifyPassword = $this->verifyPassword($old_password, $old_password_hash);
			if($verifyPassword['status'] == 'success')
			{
				$sql = $this->db->query('UPDATE `'.$this->table_prefix.'client` SET `client_password` = "'.$this->hashPassword($new_password).'" WHERE `client_email` = "'.$email.'"');
				if($sql)
				{
					return [
						'status' => 'failed',
						'data' => 'Client password changed successfully!'
					];
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => "Something went's wrong while processing this request!"
					];
				}
			}
			else
			{
				return [
					'status' => 'failed',
					'data' => "Invaild client password provided!"
				];
			}
		}
	}

	public function changeClientName(string $name)
	{
		$isVaildName = $this->validateData($name, 'name');
		if($isVaildName['status'] == 'failed')
		{
			return $isVaildName;
		}
		else
		{
			$isLogged = $this->isLogged();
			if($isLogged['status'] == 'failed')
			{
				return $isLogged;
			}
			else
			{
				$name = $isVaildName['data'];
				$email = $this->getLoggedClientInfo('email')['data'];
				$sql = $this->db->query('UPDATE `'.$this->table_prefix.'client` SET `client_name` = "'.$name.'" WHERE `client_email` = "'.$email.'"');
				if($sql)
				{
					setcookie($this->cookie_name, NULL, -1, '/');
					return [
						'status' => 'success',
						'data' => 'Client name changed successfully!'
					];
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => "Something went's wrong while processing this request!"
					];
				}
			}
		}
	}

	public function changeClientEmail(string $email)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$isLogged = $this->isLogged();
			if($isLogged['status'] == 'failed')
			{
				return $isLogged;
			}
			else
			{
				$email = $isVaildEmail['data'];
				$sql = $this->db->query("SELECT `client_id` FROM `".$this->table_prefix."client` WHERE `client_email` = '".$email."'");
				if($sql->num_rows > 0)
				{
					return [
						'status' => 'failed',
						'data' => 'Another client is already registered with this email address!'
					];
				}
				else
				{
					$client_key = $this->getLoggedClientInfo('client_key')['data'];
					$sql = $this->db->query('UPDATE `'.$this->table_prefix.'client` SET `client_email` = "'.$email.'" WHERE `client_key` = "'.$client_key.'"');
					if($sql)
					{
						setcookie($this->cookie_name, NULL, -1, '/');
						return [
							'status' => 'success',
							'data' => 'Client email changed successfully!'
						];
					}
					else
					{
						return [
							'status' => 'failed',
							'data' => "Something went's wrong while processing this request!"
						];
					}
				}
			}
		}
	}

	public function resetClientRecoveryKey(string $email)
	{
		$isVaildEmail = $this->validateData($email, 'email');
		if($isVaildEmail['status'] == 'failed')
		{
			return $isVaildEmail;
		}
		else
		{
			$isRegistered = $this->isRegistered($email);
			if($isRegistered['status'] == 'failed')
			{
				return $isRegistered;
			}
			else
			{
				$email = $isVaildEmail['data'];
				$recovery_key = $this->createRecoveryKey();
				$sql = $this->db->query('UPDATE `'.$this->table_prefix.'client` SET `client_recovery_key` = "'.$recovery_key.'" WHERE `client_email` = "'.$email.'"');
				if($sql)
				{
					return [
						'status' => 'failed',
						'data' => 'Client recovey key changed successfully!'
					];
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => "Something went's wrong while processing this request!"
					];
				}
			}
		}
	}

	public function setResponseMessage(int $status, string $message, string $filepath, string $param = '')
	{
		if($status == 0)
		{
			$status = 'failed';
		}
		else
		{
			$status = 'success';
		}

		$data = [
			'status' => $status, 
			'data' => $message
		];
		$_SESSION['response'] = json_encode($data);
		if($param !== '')
		{
			$param = '?'.htmlspecialchars($param);
		}
		else
		{
			$param = '';
		}
		header('location: '.$filepath.'.php'.$param);
	}

	public function getResponseMessage()
	{
		if(isset($_SESSION['response']))
		{
			$data = $_SESSION['response'];
			unset($_SESSION['response']);
			return json_decode($data, true);
		}
	}

	public function sendEmail(string $receipent, string $subject, string $body){
		try
		{
			$mail = new \PHPMailer;
			$mail->SMTPDebug = true;
			$mail->isSMTP();
			$mail->Host = $this->config['smtp']['hostname'];
			$mail->SMTPAuth = true;
			$mail->Username = $this->config['smtp']['username'];
			$mail->Password = $this->config['smtp']['password'];
			$mail->SMTPSecure = 'tls';
			$mail->Port = $this->config['smtp']['port'];
			$mail->From = $this->config['smtp']['from'];
			$mail->FromName = $this->config['smtp']['name'];
			$mail->addAddress($receipent);
			$mail->addReplyTo('no-reply@gmail.com', 'No Reply');
			$mail->WordWrap = 10000;
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = $body;
			$mail->send();
		} 
		catch(Exception $e)
		{
			return $e;
		}
	}

	public function getLoggedClientInfo(string $field = '')
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$CookieData = json_decode(
							gzuncompress(
								base64_decode(
									$_COOKIE[$this->cookie_name]
								)
							)
						);
			$isVaildEmail = $this->validateData($CookieData[0], 'email');
			if($isVaildEmail['status'] == 'failed')
			{
				return $isVaildEmail;
			}
			else
			{
				$email = $isVaildEmail['data'];
				$isRegistered = $this->isRegistered($email);
				if($isRegistered['status'] == 'failed')
				{
					return $isRegistered;
				}
				else
				{
					$sql = $this->db->query("SELECT `client_name`,`client_key`,`client_recovery_key`,`client_status`,`client_date` FROM `".$this->table_prefix."client` WHERE `client_email` = '".$email."'");
					if($sql){
						$data = $sql->fetch_assoc();
						$returnData = [
							'name' => $data['client_name'],
							'email' => $email,
							'date' => date('d F Y', $data['client_date']),
							'client_key' => $data['client_key'],
							'recovery_key' => $data['client_recovery_key'],
							'status' => ''
						];
						if($data['client_status'] == 1)
						{
							$returnData['status'] = "verified";
						}
						else
						{
							$returnData['status'] = "unverified";
						}
						if($field == '')
						{
							return [
								'status' => 'success', 
								'data' => $returnData
							];
						}
						elseif(isset($returnData[$field])){
							return [
								'status' => 'success', 
								'data' => $returnData[$field]
							];
						}
						else{
							return [
								'status' => 'failed', 
								'data' => "Invalid index '".$field."' requested!"
							];
						}
					}
					else{
						return [
							'status' => 'failed',
							'data' => "Something went's wrong while processing the request!"
						];
					}
				}
			}
		}
	}

	public function getSupportTicketInfo(string $ticketID, string $field = 'all')
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$ticketID = $this->validateData($ticketID, 'string')['data'];
			$field = $this->validateData($field, 'string')['data'];
			$client_key = $this->getLoggedClientInfo('client_key')['data'];
			$sql = $this->db->query("SELECT * FROM `".$this->table_prefix."ticket` WHERE `ticket_key` = '".$ticketID."' AND `ticket_for` = '".$client_key."'");
			if($sql->num_rows > 0)
			{
				$data = $sql->fetch_assoc();
				if($data['ticket_status'] == 0)
				{
					$status = 'open';
				}
				elseif($data['ticket_status'] == 1)
				{
					$status = 'support reply';
				}
				elseif($data['ticket_status'] == 2)
				{
					$status = 'client reply';
				}
				elseif($data['ticket_status'] == 3)
				{
					$status = 'closed';
				}
				$returnData = [
					'subject' => $data['ticket_subject'],
					'content' => $data['ticket_content'],
					'status' => $status,
					'date' => date('d F Y', $data['ticket_date'])
				];
				if($field == 'all')
				{
					return [
						'status' => 'success', 
						'data' => $returnData
					];
				}
				elseif(isset($returnData[$field]))
				{
					return [
						'status' => 'success', 
						'data' => $returnData[$field]
					];
				}
				else
				{
					return [
						'status' => 'failed', 
						'data' => "Invalid index '".$field."' requested!"
					];
				}
			}
			else
			{
				return [
					'status' => 'failed',
					'data' => "Requested support ticket not found!"
				];
			}
		}
	}

	public function getSupportTicketReply(string $ticketID)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$ticketID = $this->validateData($ticketID, 'string')['data'];
			$isValidTicket = $this->getSupportTicketInfo($ticketID);
			if($isValidTicket['status'] == 'failed')
			{
				return $isValidTicket;
			}
			else
			{
				$sql = $this->db->query("SELECT * FROM `".$this->table_prefix."reply` WHERE `reply_for` = '".$ticketID."'");
				if($sql->num_rows > 0)
				{
					while($data = $sql->fetch_assoc()){
						$returnData[] = [
							'from' => $data['reply_from'],
							'content' => $data['reply_content'],
							'date' => date('d F Y', $data['reply_date'])
						];
					}
					return [
						'status' => 'success',
						'data' => [
							'count' => count($returnData),
							'list' => $returnData,
						]
					];
				}
				else
				{
					return [
						'status' => 'success',
						'data' => [
							'count' => 0,
							'list' => []
						]
					];
				}
			}
		}
	}

	public function createSupportTicket(string $subject, string $content)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$subject = $this->validateData($subject, 'string')['data'];
			$content = $this->validateData($content, 'textarea')['data'];
			$client_key = $this->getLoggedClientInfo('client_key')['data'];
			$time = time();
			$ticketID = $this->createTicketID();
			$sql = $this->db->query("INSERT INTO `".$this->table_prefix."ticket`(
				`ticket_subject`,
				`ticket_content`,
				`ticket_status`,
				`ticket_date`,
				`ticket_key`,
				`ticket_for`
			) VALUES (
				'".$subject."',
				'".$content."',
				'0',
				'".$time."',
				'".$ticketID."',
				'".$client_key."'
			)");
			if($sql)
			{
				return [
					'status' => 'success',
					'data' => 'Support ticket created successfully!'
				];
			}
			else
			{
				return [
					'status' => 'failed',
					'data' => "Something went's wrong while processing the request!"
				];
			}
		}
	}

	public function createSupportTicketReply(string $ticketID, string $content)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$ticketID = $this->validateData($ticketID, 'string')['data'];
			$content = $this->validateData($content, 'textarea')['data'];
			$client_key = $this->getLoggedClientInfo('client_key')['data'];
			$time = time();
			$isValidTicket = $this->getSupportTicketInfo($ticketID);
			if($isValidTicket['status'] == 'failed')
			{
				return $isValidTicket;
			}
			else
			{
				$sql = $this->db->query("INSERT INTO `".$this->table_prefix."reply`(
					`reply_from`,
					`reply_content`,
					`reply_date`,
					`reply_for`
				) VALUES (
					'".$client_key."',
					'".$content."',
					'".$time."',
					'".$ticketID."'
				)");
				$sql = $this->db->query("UPDATE `".$this->table_prefix."ticket` SET `ticket_status` = '2' WHERE `ticket_key` = '".$ticketID."'");
				if($sql)
				{
					return [
						'status' => 'success',
						'data' => 'Support ticket reply added successfully!'
					];
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => "Something went's wrong while processing the request!"
					];
				}
			}
		}
	}

	private function initMOFH()
	{
		if(isset($this->config['mofh']))
		{
			$this->mofh = new \InfinityFree\MofhClient\Client;
			$this->mofh->setApiUsername($this->config['mofh']['username']);
			$this->mofh->setApiPassword($this->config['mofh']['password']);
			$this->mofh->setPlan($this->config['mofh']['plan']);
		}
		else
		{
			throw new Exception("MyOwnFreeHost configuration not found!");		
		}
	}

	public function domainAvailability(string $domain)
	{
		if(strlen($domain) < 4)
		{
			return [
				'status' => 'failed',
				'data' => 'Domain name must have at least 5 characters!'
			];
		}
		else
		{
			$isVaildDomain = $this->validateData($domain, 'domain');
			if($isVaildDomain['status'] == 'failed')
			{
				return $isVaildDomain;
			}
			else
			{
				$request = $this->mofh->availability([
					'domain' => $isVaildDomain['data']
				]);
				$response = $request->send();
				if($response->isSuccessful() == 0 && strlen($response->getMessage()) > 1)
				{
					return [
						'status' => 'failed',
						'data' => $response->getMessage()
					];
				}
				elseif($response->isSuccessful() == 1 && $response->getMessage() == 1)
				{
					return [
						'status' => 'success',
						'data' => 'Domain name is eligible for hosting account!'
					];
				}
				elseif($response->isSuccessful() == 0 && $response->getMessage() == 0)
				{
					return [
						'status' => 'failed',
						'data' => 'Domain name is not eligible for hosting account!'
					];
				}
			}
		}
	}

	public function createHostingAccount(string $domain)
	{
		$isVaildDomain = $this->domainAvailability($domain);
		if($isVaildDomain['status'] == 'failed')
		{
			return $isVaildDomain;
		}
		else
		{
			$isLogged = $this->isLogged();
			if($isLogged['status'] == 'failed')
			{
				return $isLogged;
			}
			else
			{
				$email = $this->getLoggedClientInfo('email');
				$username = $this->createHostingUsername();
				$password = $this->createHostingPassword();
				$client_key = $this->getLoggedClientInfo('client_key');
				$request = $this->mofh->createAccount([
					'username' => $username,
					'password' => $password,
					'domain' => $domain,
					'email' => $email['data']
				]);
				$response = $request->send();
				$responseData = $response->getData();
		        $returnArray = array(
		            'message' => $responseData['result']['statusmsg'],
		            'status' => $responseData['result']['status'],
		            'domain' => str_replace('cpanel', strtolower($username), $this->config['mofh']['cpanel_uri']),
		            'date' => time()
		        );
		        if($returnArray['status'] == 0 && strlen($returnArray['message']) > 1)
		        {
		        	return [
		        		'status' => 'failed',
		            	'data' => $returnArray['message']
		            ];
		        }
		        elseif($returnArray['status'] == 1 && strlen($returnArray['message']) > 1)
		        {
		        	$returnArray['username'] = $responseData['result']['options']['vpusername'];
		        	$sql = $this->db->query("INSERT INTO `".$this->table_prefix."hosting`(
						  `hosting_username`,
						  `hosting_password`,
						  `hosting_domain`,
						  `hosting_status`,
						  `hosting_date`,
						  `hosting_sql`,
						  `hosting_key`,
						  `hosting_for`
		           		) VALUES (
		           		  '".$returnArray['username']."',
		           		  '".$password."',
		           		  '".$domain."',
		           		  '0',
		           		  '".$returnArray['date']."',
		           		  'sqlxxx',
		           		  '".$username."',
		           		  '".$client_key['data']."'
		           		)");
		            if($sql)
		            {
		            	return [
		            		'status' => 'success',
		            		'data' => 'Hosting account created successfully!'
		            	];
		            }
		            else
		            {
		            	return [
		            		'status' => 'failed',
		            		'data' => "Something went's wrong while proccessing request!"
		            	];
		            }
		        }
		        else
		        {
		           	return [
		           		'status' => 'failed',
		           		'data' => "Something went's wrong while proccessing request!"
		           	];
		        }
			}
		}
	}

	public function getHostingAccountInfo(string $account_key)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$account_key = $this->validateData($account_key, 'string')['data'];
			$client_key = $this->getLoggedClientInfo('client_key')['data'];
			$sql = $this->db->query("SELECT * FROM `".$this->table_prefix."hosting` WHERE `hosting_key` = '".$account_key."' AND `hosting_for` ='".$client_key."'");
			if($sql->num_rows > 0)
			{
				$data = $sql->fetch_assoc();
				$status = $data['hosting_status'];
				if($status == 0)
				{
					$status = 'proccessing';
				}
				elseif($status == 1)
				{
					$status = 'active';
				}
				elseif($status == 2)
				{
					$status = 'deactivated';
				}
				elseif($status == 3)
				{
					$status = 'suspnded';
				}
				elseif($status == 4)
				{
					$status = 'deactivating';
				}
				elseif($status == 5)
				{
					$status = 'reactivating';
				}
				return [
					'status' => 'success',
					'data' => [
						'general' => [
							'username' => $data['hosting_username'],
							'password' => $data['hosting_password'],
							'cpanel_uri' => $this->config['mofh']['cpanel_uri'],
							'created_on' => date('d F Y', $data['hosting_date']),
							'client_domain' => $data['hosting_domain'],
							'main_domain' => strtolower(str_replace('cpanel', $data['hosting_key'], $this->config['mofh']['cpanel_uri'])),
							'status' => $status,
							'ns_1' => $this->config['mofh']['ns_1'],
							'ns_2' => $this->config['mofh']['ns_2']
						],
						'ftp' => [
							'username' => $data['hosting_username'],
							'password' => $data['hosting_password'],
							'host_uri' => str_replace('cpanel', 'ftp', $this->config['mofh']['cpanel_uri']),
							'port' => '21'
						],
						'mysql' => [
							'username' => $data['hosting_username'],
							'password' => $data['hosting_password'],
							'host_uri' => str_replace('cpanel', $data['hosting_sql'], $this->config['mofh']['cpanel_uri']),
							'port' => '3306'
						],
						'link' => [
							'file_manager' => 'https://filemanager.ai/new/#/c/ftpupload.net/'.$data['hosting_username'].'/'.base64_encode(
								json_encode(['t' => 'ftp', 'c' => ['v' => 1, 'p' => $data['hosting_password']]]))
						]
					]
				];
			}
			else
			{
				return [
			       'status' => 'failed',
			       'data' => "No web hosting account found with this key!"
			     ];
			}
		}
	}

	public function getHostingAccountDomains(string $account_key)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$account_key = $this->validateData($account_key, 'string')['data'];
			$verifyAccountKey = $this->getHostingAccountInfo($account_key);
			if($verifyAccountKey['status'] == 'failed')
			{
				return $verifyAccountKey;
			}
			else
			{
				$status = $verifyAccountKey['data']['general']['status'];
				if($status == 'active')
				{
					$defaultDomain = $verifyAccountKey['data']['general']['client_domain'];
					$password = $verifyAccountKey['data']['general']['password'];
					$request = $this->mofh->getUserDomains([
						'username' => $account_key
					]);
					$response = $request->send();
					$Result = $response->getDomains();
					if(count($Result) > 0)
					{
						foreach($Result as $domain)
						{
							if($domain == $defaultDomain)
							{
								$domains[] = [
									'name' => $domain,
									'file_manager' => 'https://filemanager.ai/new/#/c/ftpupload.net/'.$data['hosting_username'].'/'.base64_encode(
									json_encode(['t' => 'ftp', 'c' => ['v' => 1, 'p' => $password]]))
								];
							}
							else
							{
								$domains[] = [
									'name' => $domain,
									'file_manager' => 'https://filemanager.ai/new/#/c/ftpupload.net/'.$data['hosting_username'].'/'.base64_encode(
									json_encode(['t' => 'ftp', 'c' => ['v' => 1, 'p' => $password, 'i' => '/'.$domain.'/htdoxs/']]))
								];
							}
						}
						return [
							'status' => 'success',
							'data' => [
								'count' => count($domains),
								'list' => $domains
							] 
						];
					}
					else
					{
						return [
							'status' => 'success',
							'data' => [
								'count' => 0,
								'list' => []
							]
						];
					}
				}
				else{
					return [
						'status' => 'failed',
						'data' => 'Hosting account is not currently active!'
					];
				}
			}
		}
	}

	public function deactivateHostingAccount(string $account_key, string $reason)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$account_key = $this->validateData($account_key, 'string')['data'];
			$reason = $this->validateData($reason, 'string')['data'];
			$verifyAccountKey = $this->getHostingAccountInfo($account_key);
			if($verifyAccountKey['status'] == 'failed')
			{
				return $verifyAccountKey;
			}
			else
			{
				if(strlen($reason) < 8)
				{
					return [
						'status' => 'failed',
						'data' => 'Deactivation reason must have at least 8 characters!'
					];
				}
				else
				{
					$status = $verifyAccountKey['data']['general']['status'];
					if($status == 'active')
					{
						$request = $this->mofh->suspend([
							'username' => $account_key,
							'reason' => $reason
						]);
						$response = $request->send();
						$Data = $response->getData();
						$returnArray = array(
							'status' => $Data['result']['status'],
							'message' => $Data['result']['statusmsg']
						);
						if($returnArray['status'] == 0 && !is_array($returnArray['message']))
						{
							return [
				        		'status' => 'failed',
				            	'data' => $returnArray['message']
				            ];
						}
						elseif($returnArray['status'] == 0 && is_array($returnArray['message']))
						{
							$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_status` = '4' WHERE `hosting_key` = '".$account_key."'");
							if($sql)
				            {
				            	return [
				            		'status' => 'success',
				            		'data' => 'Hosting account deactivated successfully!'
				            	];
				            }
				            else
				            {
				            	return [
				            		'status' => 'failed',
				            		'data' => "Something went's wrong while proccessing request!"
				            	];
				            }
						}
						else
						{
							return [
				        		'status' => 'failed',
				            	'data' => "Something went's wrong while proccessing request!"
				            ];
						}
					}
					else
					{
						return [
							'status' => 'failed',
							'data' => 'Hosting account is not currently active!'
						];
					}
				}
			}
		}
	}

	public function reactivateHostingAccount(string $account_key)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$account_key = $this->validateData($account_key, 'string')['data'];
			$verifyAccountKey = $this->getHostingAccountInfo($account_key);
			if($verifyAccountKey['status'] == 'failed')
			{
				return $verifyAccountKey;
			}
			else
			{
				$status = $verifyAccountKey['data']['general']['status'];
				if($status !== 'active')
				{
					$request = $this->mofh->unsuspend([
						'username' => $account_key
					]);
					$response = $request->send();
					$Data = $response->getData();
					$returnArray = array(
						'status' => $Data['result']['status'],
						'message' => $Data['result']['statusmsg']
					);
					if($returnArray['status'] == 0 && !is_array($returnArray['message']))
					{
						return [
							'status' => 'failed',
							'data' => $returnArray['message']
						];
					}
					if($returnArray['status'] == 1 && is_array($returnArray['message']))
					{
						$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_status` = '4' WHERE `hosting_key` = '".$account_key."'");
						if($sql)
				        {
				        	return [
				          		'status' => 'success',
				           		'data' => 'Hosting account deactivated successfully!'
				           	];
				        }
				        else
				        {
				        	return [
				         		'status' => 'failed',
				         		'data' => "Something went's wrong while proccessing request!"
				         	];
				        }
					}
					else
					{
						return [
				        	'status' => 'failed',
				           	'data' => "Something went's wrong while proccessing request!"
				        ];
					}
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => 'Hosting account is already active!'
					];
				}
			}
		}
	}

	public function changeHostingAccountPassword(string $account_key, string $old_password, string $new_password)
	{
		$isLogged = $this->isLogged();
		if($isLogged['status'] == 'failed')
		{
			return $isLogged;
		}
		else
		{
			$account_key = $this->validateData($account_key, 'string')['data'];
			$old_password = $this->validateData($old_password, 'string')['data'];
			$new_password = $this->validateData($new_password, 'string')['data'];
			$verifyAccountKey = $this->getHostingAccountInfo($account_key);
			if($verifyAccountKey['status'] == 'failed')
			{
				return $verifyAccountKey;
			}
			else
			{
				$status = $verifyAccountKey['data']['general']['status'];
				$password = $verifyAccountKey['data']['general']['password'];
				if($status == 'active')
				{
					if($password == $old_password)
					{
						$request = $this->mofh->password([
							'username' => $account_key,
							'password' => $new_password,
							'enabledigest' => 1,
						]);
						$response = $request->send();
						$Data = $response->getData();
						$returnArray = array(
							'status' => $Data['passwd']['status'],
							'message' => $Data['passwd']['statusmsg'],
							'password' => $new_password
						);
						if($returnArray['status'] == 0 && strlen($returnArray['message']) > 1)
						{
							return [
				        		'status' => 'failed',
				            	'data' => $returnArray['message']
				            ];
						}
						elseif($returnArray['status'] == 1 && strlen($returnArray['message']) > 1)
						{
							$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_password` = '".$returnArray['password']."' WHERE `hosting_key` = '".$account_key."'");
							if($sql)
				            {
				            	return [
				            		'status' => 'success',
				            		'data' => 'Hosting account password changed successfully!'
				            	];
				            }
				            else
				            {
				            	return [
				            		'status' => 'failed',
				            		'data' => "Something went's wrong while proccessing request!"
				            	];
				            }
						}
						else
						{
							return [
				        		'status' => 'failed',
				            	'data' => "Something went's wrong while proccessing request!"
				            ];
						}
					}
					else
					{
						return [
							'status' => 'failed',
							'data' => "Hosting account password doesn't match!"
						];
					}
				}
				else
				{
					return [
						'status' => 'failed',
						'data' => 'Hosting account is not currently active!'
					];
				}
			}
		}
	}

	public function setHostingAccountCallback(array $array, string $location = '')
	{
		$account_key = $array['username'];
		if(isset($array['comments']))
		{
			if(substr($array['status'], 0, 3) == 'sql')
			{
				$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_status` = '1',`hosting_sql` = '".$array['status']."' WHERE `hosting_username` = '".$account_key."'");
				if(!empty($location))
				{
					header("location: ".$location.'?username='.$array['username'].'&action=activate');
					exit;
				}
			}
			elseif($array['status'] == 'SUSPENDED')
			{
				$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_status` = '3' WHERE `hosting_username` = '".$account_key."'");
				if(!empty($location))
				{
					if($array['comments'] == 'AUTO_IDLE')
					{
						header("location: ".$location.'?username='.$array['username'].'&action=suspend');
						exit;
					}
					else
					{
						header("location: ".$location.'?username='.$array['username'].'&action=deactivated');
						exit;
					}
				}
			}
			elseif($array['status'] == 'REACTIVATE')
			{
				$sql = $this->db->query("UPDATE `".$this->table_prefix."hosting` SET `hosting_status` = '1' WHERE `hosting_username` = '".$account_key."'");
				if(!empty($location))
				{
					header("location: ".$location.'?username='.$array['username'].'&action=reactivated');
					exit;
				}
			}
			elseif($array['status'] == "DELETE")
			{
				$sql = $this->db->query("DELETE FROM `".$this->table_prefix."hosting` WHERE `hosting_username` = '".$account_key."'");
				if(!empty($location))
				{
					header("location: ".$location.'?username='.$array['username'].'&action=deleted');
					exit;
				}
			}
		}
	}
}
?>
