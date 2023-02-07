<?php

require_once '/var/www/framework/test/CTestCase.php';
require_once '/var/www/testdrive/protected/tests/unit/emailsmtpvalidator/EEmailSmtpValidator.php';

	class EEmailSmtpValidatorTest extends CTestCase {
		
		protected $port = 25;
		protected $nameServers = array( 'localhost' );
		protected $timeOut = 10;
		protected $dataTimeOut = 5;
		protected $sender = 'postmaster@localhost';
		protected $strictValidation = false;
		protected $sock;
		
		//assertion tests 
		public function testGetPort() {
			$objeto = new EEmailSmtpValidator;
			$objeto->setPort($this->port);
			$this->assertEquals( $this->port, $objeto->getPort() );		
		}		
		
		public function testGetNameServers() {
			$objeto = new EEmailSmtpValidator;
			$objeto->setNameServers( $this->nameServers );
			$this->assertEquals( $this->nameServers, $objeto->getNameServers() );		
		}
		
		public function testGetTimeOut() {
			$objeto = new EEmailSmtpValidator;
			$objeto->setTimeOut( $this->timeOut );
			$this->assertEquals( $this->timeOut, $objeto->getTimeOut() );
		}
		
		public function testGetSender() {
			$objeto = new EEmailSmtpValidator;
			$objeto->setSender( $this->sender );
			$this->assertEquals( $this->sender, $objeto->getSender() );		
		}
			
		public function testGetStrictValidation() {
			$objeto = new EEmailSmtpValidator;
			$objeto->setStrictValidation( $this->strictValidation );
			$this->assertEquals( $this->strictValidation, $objeto->getStrictValidation() );
		}
		
		//Exceptions tests
		public function testExceptionSetPort() {
			$objeto = new EEmailSmtpValidator;
			$port = 'NANA';
			$objeto->setPort( $port );
		}

		public function testExceptionSetNameServers() {
			$objeto = new EEmailSmtpValidator;
			$nameServers = 3;
			$objeto->setNameServers( $nameServers );
		}
		
		public function testExceptionSetTimeOut() {
			$objeto = new EEmailSmtpValidator;
			$timeOut = 'NANA';
			$objeto->setTimeOut( $timeOut );
		}
		
		public function testExceptionSetDataTimeOut() {
			$objeto = new EEmailSmtpValidator;
			$dataTimeOut = 'NANA'; 
			$objeto->setDataTimeOut( $dataTimeOut );			
		}
		
		public function testExceptionSetSender() {
			$objeto = new EEmailSmtpValidator;
			$sender = 'NANA';
			$objeto->setSender( $sender );			
		}
		
		public function testExceptionSetStrictValidation() {
			$objeto = new EEmailSmtpValidator;
			$strictValidation = 'NANA';
			$objeto->setStrictValidation( $strictValidation );
		} 	
	}
?>