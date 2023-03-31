<?php

class Taxes_m extends CI_Model {    
	function __construct() {}

	function getVatPercent(){
		return 7;
	}

	function getVat(){
		return 1.07;
	}
}
