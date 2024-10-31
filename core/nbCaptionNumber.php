<?php

/**
 * A package of public static helper methods working on caption number, 
 * e.g. incrementation, conversion between number formats (roman, latin, integer etc)
 *
 * @version 1.0
 *
 */

class nbCaptionNumber { 
	 /**
	 * Convert an alpha-string to a decimal.
	 *
	 * @param string $alpha An alpha-string
	 * @return int Decimal of converted alpha-characters; 0 on failure.
	 */
	static public function Alpha2dec( $alpha )
	{
		$t = array('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5,'f'=>6,'g'=>7,'h'=>8,'i'=>9,'j'=>10,'k'=>11,'l'=>12,'m'=>13,'n'=>14,'o'=>15,
				'p'=>16,'q'=>17,'r'=>18,'s'=>19,'t'=>20,'u'=>21,'v'=>22,'w'=>23,'x'=>24,'y'=>25,'z'=>26);
		
		if( !ctype_alpha($alpha) ) return 0;
			
		if( strlen($alpha) == 1 ) {
			return isset($t[$alpha]) ? $t[$alpha] : 0;
		} else {
			$a = str_split($alpha);
			$a = array_reverse($a);
			$num = 0; $b = sizeof($t);
			for($i=0; $i<sizeof($a); $i++ )
				$num += (int) ( pow($b,$i) * $t[$a[$i]] );
			return $num;
		}
	}
	
	
	/**
	 * Convert a decimal to an alpha-string.
	 *
	 * @param uint $dec An integer to be converted; required $dec>0.
	 * @return string An alpha-string from a decimal.
	 */
	static public function Dec2alpha( $dec )
	{
		$t = 'a';
		for( $i=1; $i<$dec; $i++ )
			$t++;
		return $t;	
	}
		
	/**
	 * Convert a decimal to a roman-number.
	 *
	 * @param int $dec A decimal to be converted; requires $dec>0.
	 * @return string A roman number for given decimal.
	 */
	static public function Dec2roman( $dec )
	{
	    $n = intval($dec);
	    $res = '';
	 
	    $roman_numerals = array(
	                'M'  => 1000,
	                'CM' => 900,
	                'D'  => 500,
	                'CD' => 400,
	                'C'  => 100,
	                'XC' => 90,
	                'L'  => 50,
	                'XL' => 40,
	                'X'  => 10,
	                'IX' => 9,
	                'V'  => 5,
	                'IV' => 4,
	                'I'  => 1);
	 
	    foreach ($roman_numerals as $roman => $number) 
	    {
	        // DEVIDE TO GET MATCHES
	        $matches = intval($n / $number);
	 
	        // ASSIGN ROMAN CHAR * $matches
	        $res .= str_repeat($roman, $matches);
	 
	        // SUBSTRACT FROM NUMBER
	        $n = $n % $number;
	    } 
	    return $res;	
	}
	
	/**
	 * Split an integer into an array of integers, with each 0 <= value <= base
	 * E.g.: base = 24; 24 => 0.24, 25 => 1.1, 49 => 2.1, 96 => 3.24
	 *
	 * @param int $int An integer with int > 0
	 * @param int $base An integer with base > 0
	 * @return array An array of integer, wich each 0 <= value <= base
	 */
	static public function SplitInt2Base( $int, $base )
	{
		$t = array();
		$i = $int;
		$b = 24; $l = 0; $p = 1;
		while($i>0) {
			$t[] = $i%$b;
			$i = (int) ($i/$b);
		}
		for($i=0; $i<sizeof($t)-1; $i++ )
			if( $t[$i] == 0 ) { 
				$t[$i] += $b; 
				$t[$i+1]--; 
			}
		if( $t[sizeof($t)-1] == 0 ) unset($t[sizeof($t)-1]);
		return array_reverse($t);
	}
	
	/**
	 * Increment a given caption number.
	 *
	 * @param mixed $num UINT caption number; ASTRING alpha-string, like abcw. 
	 * @param string $format A human-readable number type; see nbdb::cptg_numberCode().
	 * @param int $step Number of steps to increment or decrement.
	 * @return mixed Integer number or character string.
	 */ 
	static public function Increment( $num, $format = 'decimal', $step = 1 )
	{
		// PREPARE, VERIFY
		if( ($format == 'alpha' || $format == 'lower-alpha' || $format == 'upper-alpha') && is_numeric($num) ) {
			if( $num < 1 ) $step = 0;
			$num = self::Dec2alpha($num);		
			if( $format == 'upper-alpha' ) $num = strtoupper($num);	
		}
		
		// DECIMAL, ALPHA
		if( $format == 'alpha' || $format == 'decimal' || $format == 'lower-alpha' || $format == 'upper-alpha' ) {
			$t = explode('.',$num);
			if( $step == 1 )
				$t[sizeof($t)-1]++;
			else if( $step == -1 )
				$t[sizeof($t)-1]--;
			return implode('.',$t);
		}
		
		// GREEK
		if( $format == 'lower-greek' || $format == 'upper-greek' || $format == 'greek' ) {
			if( $step == 1 ) $num++;
			else if( $step == -1 ) $num--;
	
			$greek = array(1=>'alpha',2=>'beta',3=>'gamma',4=>'delta',5=>'epsilon',6=>'zeta',7=>'eta',
					8=>'theta',9=>'iota',10=>'kappa',11=>'lambda',12=>'mu',13=>'nu',14=>'xi',15=>'omicron',
					16=>'pi',17=>'rho',18=>'sigma',19=>'tau',20=>'upsilon',21=>'phi',22=>'chi',23=>'psi',24=>'omega');
					
			if( !is_numeric($num) && ctype_alpha($num) )	
				$num = self::Alpha2dec($num);
			else if( !is_numeric($num) ) return;
			
			$alphaint = self::SplitInt2Base( $num, sizeof($greek) );
			
			$str = '';	
			foreach( $alphaint as $i ) {
				if( $format == 'upper-greek' ) $greek[$i] = ucfirst($greek[$i]);			
				$str .= '&'. $greek[$i] .';';
			}
			return $str;
		}
		
		// ROMAN
		if( $format == 'lower-roman' || $format == 'upper-roman' || $format == 'roman' ) {
			if( !is_numeric($num) && ctype_alpha($num) ) {
				$n = self::Alpha2dec($num);
			} else if( !is_numeric($num) ) return 0;
			
			if( $step == 1 ) $num++;
			if( $step == -1 ) $num--;
			$r = self::Dec2roman( $num );
			if( $format == 'lower-roman' ) $r = strtolower($r);
			return $r;
		}
		return '';
	}
}