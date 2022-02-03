## Table of Content
A list of supported functions is given below:
- [Get Started](#get-started)
- [Template System](#template-system)
- [Data Validation](#data-validation)
- [Client Account Management](#client-account-management)
- [Sending Emails](#sending-emails)
- [HTTP Response Message](#http-response-message)
- [Support Ticket Management](#support-ticket-management)

## Get Started

MOARC can installed by the following command.

```
composer require mahtab2003/moarc
```

You need to create a new class instance inorder to use MOARC Client class.

```
<?php
include 'vendor/autoload.php';
use Mahtab2003\MOARC\Client;

$moarc = new Client;
```
each function  in this class return's an array.
```
array(
	'status' => '' // success or failed,
	'data' => '' // string or array according to function.
)
```

## Template System

### loadDocPart($filename)

This function is used to load html include files from the specific folder defined in `src/Client.php`.
```
$moarc->loadDocPart('header');
```
- Parameter `$filename` is required in case of using this function.
- Parameter filename must not include file extension(eg. _header.php_).

### loadDocBody($filename)

This function is used to load html body files from the specific folder defined in `src/Client.php`.
```
$moarc->loadDocBody('login');
```
- Parameter `$filename` is required in case of using this function.
- Parameter filename must not include file extension(eg. _header.php_).

## Data Validation

### validateData($data, $type)

This function is used to validate given data and make it safe to procceed.
```
$moarc->validateData('This is a <s>data</s>', 'string');

/* response
array(
	'status' => 'success',
	'data' => 'This is a &lts&gtdata&lt/s&gt'
)
*/
```
- Parameter `$data` is required in order to use this function.
- Parameter `$type` is optional. There are some valid data types given below.
 - string: prevents html entites and sql injections.
 - name: check if there is any url or query exsist.
 - email: check if given data is in valid email format.
 - domain: check if the given data is a valid domain.
 - textarea: prevents sql injections.

### getProtectedRequest($field, $type)

This function is used to get secure post or get request.
```
$moarc->getProtectedRequest('get');

/* response
array(
	'status' => 'success',
	'data' => '1'
)
*/
```
- Parameter `$field` is required in order to use this function.
- Parameter `$type` is optional. There are some valid data types given below.
 - GET: returns protected get request.
 - POST: returns protected post request.

## Client Account Management

### registerClient($name, $email, $password)

This function is used to register new client account in database.
```
$moarc->registerClient('Jhon Doe', 'jhon@example.com', 'example123');

/* response
array(
	'status' => 'success',
	'data' => 'Client is registered successfully!'
)
*/
```
- Parameters `$name`, `$email` and `$password` are required in order to use this function.


### logClientIn($email, $password)

This function is used to login a client account.
```
$moarc->logClientIn('jhon@example.com', 'example123');

/* response
array(
	'status' => 'success',
	'data' => 'Client logged in successfully!'
)
*/
```
- Parameters `$email` and `$password` are required in order to use this function.

### logClientOut()

This function is used to logout a client account.
```
$moarc->logClientOut();

/* response
array(
	'status' => 'success',
	'data' => 'Client logged in successfully!'
)
*/
```
- There is no parameter required for this function.

### isLogged()

This function is used to check if account is logged or not.
```
$moarc->isLogged();

/* response
array(
	'status' => 'success',
	'data' => 'Client information verified successfully!'
)
*/
```
- There is no parameter required for this function.

### isVerifiedClient()

This function is used to check if logged account is verified or not.
```
$moarc->isVerifiedClient();

/* response
array(
	'status' => 'success',
	'data' => 'verified' or 'unverified'
)
*/
```
- There is no parameter required for this function.

### getLoggedClientInfo($filed)

This function is used to get logged client account information.
```
$moarc->getUnloggedClientInfo();

/* response
array(
	'status' => 'success',
	'data' => array(...)
)
*/
```
- Parameter `$field` is optional. There are valid fields given below.
 - name: returns client name.
 - email: returns cient email.
 - date: returns client registeration date.
 - client_key: returns client private key.
 - recovery_key: returns client account recovery key.
 - status: returns account status 'verified' or 'unverified'.

### resetClientPassword($email, $recovery_key, $password)

This function is used to reset client account password.
```
$moarc->resetClientPassword('jhon@example.com', 'Abvdvhg5647', 'example123');

/* response
array(
	'status' => 'success',
	'data' => 'Client password reset successfully!'
)
*/
```
- Parameters `$email`,`$recovery_key` and `$password` are required in order to use this function.

### changeClientPassword($old_password, $new_password)

This function is used to change client account password.
```
$moarc->changeClientPassword('example123', 'example456');

/* response
array(
	'status' => 'success',
	'data' => 'Client password changed successfully!'
)
*/
```
- Parameters `$old_password` and `$new_password` are required in order to use this function.

### changeClientName($name)

This function is used to change client account name.
```
$moarc->changeClientName('Shen Wei');

/* response
array(
	'status' => 'success',
	'data' => 'Client name changed successfully!'
)
*/
```
- Parameter `$name` are required in order to use this function.

### changeClientEmail($email)

This function is used to change client account email.
```
$moarc->changeClientEmail('shen@example.com');

/* response
array(
	'status' => 'success',
	'data' => 'Client email changed successfully!'
)
*/
```
- Parameter `$email` are required in order to use this function.

### resetClientRecoveryKey($email)

This function is used to reset client account recovery key.
```
$moarc->resetClientRecoveryKey('shen@example.com');

/* response
array(
	'status' => 'success',
	'data' => 'Client recovery key changed successfully!'
)
*/
```
- Parameter `$email` are required in order to use this function.

## Sending Emails

### sendEmail($receipent, $subject, $body)

This function is used to send emails to a specific email address.
```
$moarc->sendEmail('shen@example.com', 'Test Email', 'This is email body');

/* response
array(
	'status' => 'success',
	'data' => "Email sent successfully!"
)
*/
```
- Parameter `$receipent`, `$subject` and `$body` are required in order to use this function.

## HTTP Response Message

### setResponseMessage($status, $message, $location, $param)

This function is used to set http response message.
```
$moarc->setResponseMessage(1, 'Message set successfully', 'send.php', 'to=email@123.xyz&status=true');
```
- Parameters `$status`, `$message` and `$location` are required in order to use this function.
- Parameter `$param` is optional.

### getResponseMessage()

This function is used to get http response message if set.
```
$moarc->getResponseMessage();

/* response
array(
	'status' => 'success',
	'data' => "Message received successfully!"
)
*/
```
- There is no parameter required for this function.

## Support Ticket Management

### createSupportTicket($subject, $content)

This function is used to create support ticket.
```
$moarc->createSupportTicket('Test Ticket', 'This is a test support ticket');

/* response
array(
	'status' => 'success',
	'data' => "Support ticket created successfully!"
)
*/
```
- Parameters  `$subject` and `$content` are required in order to use this function.

### getSupportTicketInfo($ticketID, $field)

This function is used to get support ticket information.
```
$moarc->getSupportTicketInfo('id1234');

/* response
array(
	'status' => 'success',
	'data' => array(...)
)
*/
```
- Parameters  `$ticketID` is required in order to use this function.
- Parameter `$field` is optional. There are valid fields given below.
 - subject: returns ticket subject.
 - content: returns ticket content.
 - status: returns ticket status.
 - date: returns ticket creation date.

### createSupportTicketReply($ticketID, $content)

This function is used to create support ticket reply.
```
$moarc->createSupportTicketReply('id1234', 'This is a test support ticket');

/* response
array(
	'status' => 'success',
	'data' => "Support ticket created successfully!"
)
*/
```
- Parameters  `$ticketID` and `$content` are required in order to use this function.

### getSupportTicketInfo($ticketID, $field)

This function is used to get support ticket reply information.
```
$moarc->getSupportTicketReply('id1234');

/* response
array(
	'status' => 'success',
	'data' => array(...)
)
*/
```
- Parameters  `$ticketID` is required in order to use this function.
- Parameter `$field` is optional. There are valid fields given below.
 - from: returns ticket from.
 - content: returns ticket content.
 - date: returns ticket creation date.
