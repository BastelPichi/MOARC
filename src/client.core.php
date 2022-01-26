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

class MOARC
{
	private $table_prefix = 'is_'; 
	private $cookie_name = 'client';
	private $config_file = __DIR__.'/../config.php';
	private $include_dir = __DIR__.'/../';
	private $template_dir = __DIR__.'/../';
	private $autoload_file = __DIR__.'/../vendor/autoload.php';

	function __construct()
	{
		ob_start();
		session_start();
		$this->initDB();
		$this->Autoload();
	}

	public function setTitle(string $title)
	{
		$this->title = $title;
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

	private function initDB()
	{
		if(file_exists($this->config_file))
		{
			include $this->config_file;
			if(isset($Config['db']))
			{
				$this->db = new \mysqli;
				$this->db->connect(
					$Config['db']['hostname'],
					$Config['db']['username'],
					$Config['db']['password'],
					$Config['db']['database']
				);
			}
			else
			{
				throw new \Exception('Database configuration not found!');
			}
		}
		else
		{
			throw new \Exception('Configuration file not found!');
		}
	}

	private function Autoload()
	{
		if(file_exists($this->autoload_file))
		{
			include $this->autoload_file;
		}
		else
		{
			throw new \Exception('Autoload file not found!');
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
		elseif($type == 'name')
		{
			if(preg_match('^[@!&%*$#~\/]^', $Data))
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

	private function getUnloggedClientInfo(string $email, string $field = ''){
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
			return [
				'status' => 'failed', 
				'data' => "Client with this email address doesn't exsists!"
			];
		}
	}

	private function isRegistered(string $email){
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

	private function createRecoveryKey()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 16);
		return $string;
	}

	private function createClientKey()
	{
		$string = 'QWERTYUIOPLKJHGFDSAZXCVBNMqwertyuiopasdfghjklzxcvbnm1234567890';
		$string = str_shuffle($string);
		$string = substr($string, 0, 10);
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

	public function RegisterClient(string $name, string $email, string $password)
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

	public function LoginClient(string $email, string $password, int $days = 1)
	{
		$isRegistered = $this->isRegistered($email);
		if($isRegistered['status'] == 'failed')
		{
			return $isRegistered;
		}
		else
		{
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
				'data' => 'no client currently logged in!'
			];
		}
	}

	public function LogClientOut()
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
				$client_status = $this->getUnloggedClientInfo($email, 'client_status');
				return [
					'status' => 'success',
					'data' => $client_status
				];
			}
		}
	}

	public function ResetClientPassword(string $email, string $recovery_key, string $password)
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

	public function ResetClientRecoveryKey(string $email)
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
		if(file_exists($this->config_file))
		{
			include $this->config_file;
			try
			{
				$mail = new \PHPMailer;
				$mail->SMTPDebug = true;
				$mail->isSMTP();
				$mail->Host = $Config['smtp']['hostname'];
				$mail->SMTPAuth = true;
				$mail->Username = $Config['smtp']['username'];
				$mail->Password = $Config['smtp']['password'];
				$mail->SMTPSecure = 'tls';
				$mail->Port = $Config['smtp']['port'];
				$mail->From = $Config['smtp']['from'];
				$mail->FromName = $Config['smtp']['name'];
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
		else
		{
			throw new \Exception("Configuration file not found!");
		}
	}
}
?>
