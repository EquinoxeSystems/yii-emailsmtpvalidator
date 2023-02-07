<?php
/**
 * EEmailSmtpValidator class file.
 *
 * @author Rodolfo González González
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Rodolfo González González
 * @version 1.0
 * @license The 3-Clause BSD License
 *
 * Copyright © 2008 by Rodolfo González González
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this 
 * list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice, 
 * this list of conditions and the following disclaimer in the documentation and/or 
 * other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors 
 * may be used to endorse or promote products derived from this software without 
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, 
 * OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY 
 * OF SUCH DAMAGE.
 *
 * --------------------------------------------------------------------------------------
 * This work was inspired by the SMTP_validateEmail class written by Gabe.
 * @link http://code.google.com/p/php-smtp-email-validation/
 * The copyright for that class follows:
 * Validate Email Addresses Via SMTP
 * This queries the SMTP server to see if the email address is accepted.
 * @copyright http://creativecommons.org/licenses/by/3.0/ - Please keep this comment intact
 * @author gabe@fijiwebdesign.com
 * @contributers adnan@barakatdesigns.net
 * @version 0.1a
 * --------------------------------------------------------------------------------------
 */

/**
 * EEmailSmtpValidator validates that the attribute value is a valid e-mail address,
 * using SMTP.
 *
 * @author Rodolfo González González
 * @package application.extensions.emailsmtpvalidator
 * @since 1.0
 * @uses Pear's Net/DNS on Windows(tm)
 */
class EEmailSmtpValidator extends CValidator
{
   // *************************
   // Properties:
   // *************************

   /**
    * SMTP Port
    */
   private $port = 25;
   /**
    * Nameservers to use when make DNS query for MX entries
    * @var Array $nameservers
    */
   private $nameServers = array('localhost');
   /**
    * How many seconds to wait before each attempt to connect to the
    * destination e-mail server
    *
    * @var integer
    */
   private $timeOut = 10;
   /**
    * How many seconds to wait for data exchanged with the server.
    * Set to a non zero value if the data timeout will be different
    * than the connection timeout.
    *
    * @var integer
    */
   private $dataTimeOut = 5;
   /**
    * The address of the sending user
    *
    * @var string
    */
   private $sender = 'postmaster@localhost';
   /**
    * If it is not possible to verify if the e-mail address is valid,
    * and this flag is set to true, then the validation will fail.
    *
    * @var boolean
    */
   private $strictValidation = false;

   // *************************
   // Private properties:
   // *************************

   /**
    * PHP Socket resource to remote MTA. This is just a private property.
    * @var resource $sock
    */
   private $sock;

   // *************************
   // Setters and getters:
   // *************************

   /**
    * Set the SMTP port
    *
    * @param integer $value
    */
   public function setPort($value)
   {
      if (!is_integer($port))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->port = $value;
   }

   /**
    * Returns the SMTP port
    *
    * @return integer
    */
   public function getPort()
   {
      return $this->port;
   }

   /**
    * Sets the array of nameservers
    *
    * @param array $value
    */
   public function setNameServers($value)
   {
      if (!is_array($value) || empty($value))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->nameServers = $value;
   }

   /**
    * Returns the array of nameservers
    *
    * @return array
    */
   public function getNameServers()
   {
      return $this->nameServers;
   }

   /**
    * Sets the socket timeout
    *
    * @param integer $value
    */
   public function setTimeOut($value)
   {
      if (!is_integer($value))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->timeOut = abs($value);
   }

   /**
    * Returns the socket timeout
    *
    * @return integer
    */
   public function getTimeOut()
   {
      return $this->timeOut;
   }

   /**
    * Sets the data timeout
    *
    * @param integer $value
    */
   public function setDataTimeOut($value)
   {
      if (!is_integer($value))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->dataTimeOut = abs($value);
   }

   /**
    * Sets the sender
    *
    * @param string $value
    */
   public function setSender($value)
   {
      if (!is_string($value))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->sender = $value;
   }

   /**
    * Returns the sender
    *
    * @return string
    */
   public function getSender()
   {
      return $this->sender;
   }

   /**
    * Sets if the validation should be strict
    *
    * @param boolean $value
    */
   public function setStrictValidation($value)
   {
      if (!is_bool($value))
         throw new CException('EEmailSmtpValidator', 'Invalid value.');
      $this->strictValidation = $value;
   }

   /**
    * Returns if the validation should be strict
    *
    * @return boolean
    */
   public function getStrictValidation()
   {
      return $this->strictValidation;
   }

   // *************************
   // Private methods:
   // *************************

   private function parseEmail($email)
   {
      $parts = explode('@', $email);
      $domain = array_pop($parts);
      $user= implode('@', $parts);
      return array($user, $domain);
   }

   private function send($msg)
   {
      fwrite($this->sock, $msg."\r\n");
      $reply = fread($this->sock, 2082);
      return $reply;
   }

   /**
    * Query DNS server for MX entries
    * @return
    */
   private function queryMX($domain)
   {
      $hosts = array();
      $mxweights = array();
      if (function_exists('getmxrr')) {
         getmxrr($domain, $hosts, $mxweights);
      }
      else {
         set_include_path(get_include_path().PATH_SEPARATOR.dirname(__FILE__));
         require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'Net'.DIRECTORY_SEPARATOR.'DNS.php');
         $resolver = new Net_DNS_Resolver();
         $resolver->nameservers = $this->nameServers;
         $resp = $resolver->query($domain, 'MX');
         if ($resp) {
            foreach ($resp->answer as $answer) {
               $hosts[] = $answer->exchange;
               $mxweights[] = $answer->preference;
            }
         }
      }
      return array($hosts, $mxweights);
   }

   // *************************
   // Validator method:
   // *************************

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
	   $valid = false;

      if (is_object($object) && isset($object->$attribute)) {
         $email = $object->$attribute;
      }

      if (isset($email) && strcmp($email, '')) {
         list($localUser, $localHost) = $this->parseEmail($this->sender);
         list($user, $domain) = $this->parseEmail($email);

         $mxs = array();
         list($hosts, $mxweights) = $this->queryMX($domain);

         $c = count($hosts);
         for ($i=0; $i<$c; $i++) {
            $mxs[$hosts[$i]] = $mxweights[$i];
         }
         asort($mxs);

         $to = $this->timeOut;
         while (list($host) = each($mxs)) {
            if (($this->sock = fsockopen($host, $this->port, $errno, $errstr, (float)$to)) !== false) {
               stream_set_timeout($this->sock, $this->dataTimeOut);
               break;
            }
         }

         if ($this->sock) {
            $reply = fread($this->sock, 2082);

            preg_match('/^([0-9]{3}) /ims', $reply, $matches);
            $code = isset($matches[1]) ? $matches[1] : '';

            if ($code != '220') {
               $valid = false;
            }
            else {
               $this->send("HELO ".$localHost);
               $this->send("MAIL FROM: <".$localUser.'@'.$localHost.">");
               $reply = $this->send("RCPT TO: <".$user.'@'.$domain.">");
               preg_match('/^([0-9]{3}) /ims', $reply, $matches);
               $code = isset($matches[1]) ? $matches[1] : '';
               if ($code == '250') {
                  $valid = true;
               }
               elseif ($code == '451' || $code == '452') {
                  $valid = $this->strictValidation ? false : true;
               }
               else {
                  $valid = false;
               }
               $this->send("RSET");
               $this->send("quit");
               fclose($this->sock);
            }
         }
         else {
   		   $valid = false;
         }
      }

		if (!$valid) {
		   $message = $this->message !== null ? $this->message : Yii::t('EEmailValidator', 'The e-mail address is invalid.');
			$this->addError($object, $attribute, $message);
		}
	}
}