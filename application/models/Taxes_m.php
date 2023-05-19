<?php

class Taxes_m extends CI_Model {    
	function __construct() {}

	function getVatPercent(){
		return 7;
	}

	function getWhtPercent(){
		return 3;
	}

	function getVat(){
		return 1.07;
	}
}
