<?php
class Handling_Array{

	// Returns the value contained in a categorical variable
	public function make_feat_array($data,$pred)
	{
		$feat_array = array();
		$result = array();

		foreach ($data as $key => $value) {
			$base_array[$key] = $value[$pred]; 
		}
		$feat_array = array_unique($base_array);

		$i = 0;
		foreach ($feat_array as $key => $value) {
			$result[$i] = $value; 	
			$i++;
		}
		return $result;
		
	}

	// Split $data by the specified categorical variable.
	//public function fbv($data,$pred)
	public function split_by_pred($data,$pred)
	{
		$split_array = array();

		// data Extract the array for each predictor type.
		$feat_array = Handling_Array::make_feat_array($data,$pred);
		
		// Calculate probability of occurrence for each value of predictor
		foreach ($feat_array as $value) {

			$i = 0;
			$newarray = array();

			if(is_array($data) && count($data)>0){
				foreach(array_keys($data) as $key2){
					$temp[$i] = $data[$key2][$pred];
					if ($temp[$i] == $value) {
						$newarray[$i] = $data[$key2];
						$i++;
					}	
				}
			}
			$split_array[$value] = $newarray;
		}
		return $split_array;

	}

}
?>
