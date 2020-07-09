<?php

require_once("Handling_Array.php");

class CART{

	function calc_delta_I($data,$base,$pred){
		$split_array = array();
		$gini_array  = array();

		// data Split the array by predictor type.
		$split_array = Handling_Array::split_by_pred($data,$pred);
		//var_dump($split_array);


		// Compute the Gini coefficient for each array	
		foreach ($split_array as $key => $value) {
			//var_dump($value);
			$gini_array[$key] = CART::calc_gini_index($value,$base);
		}
		//var_dump($gini_array);
		$gini_root = CART::calc_gini_index($data,$base);

		// Calculate delta-I
		$delta_I = $gini_root; 

		foreach ($split_array as $key => $value) {
			$odd = CART::probability_calculation($data,$pred,$key);
			$gini = $gini_array[$key];
			$delta_I -= $odd * $gini;
		}
		return $delta_I;

	}

	function calc_gini_index($data,$base)
	{
		$base_array 	= array();
		$feat_array 	= array();

		$tmp_sum = 0;
		$odds = array();


		// Extract the number of individuals for each predictor value
		$feat_array = Handling_Array::make_feat_array($data,$base);


		// Calculate probability of occurrence for each value of predictor
		foreach ($feat_array as $key => $value) {
			$odd = CART::probability_calculation($data,$base,$value);
			array_push($odds , $odd);
		}	


		// Calculate Gini coefficient
		$gini = 1;
		foreach ($odds as $key => $value) {
			$gini -= pow($value,2); 
		}
		
		return $gini;
	}

	function probability_calculation($data,$index,$value)
	{
			$tmp_sum = 0;

			foreach ($data as $dvalue) {
				if($dvalue[$index] == $value){$tmp_sum++;}
			}

			$odd = $tmp_sum / count($data);

			return $odd;
	}

}

?>
