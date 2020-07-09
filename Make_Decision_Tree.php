<?php

require_once('Listing_Combination.php');
require_once('Handling_Array.php');
require_once('CART.php');

//data structure of node
class DT_Data{
	var $number;
	var $match;
	var $unmatch;

	var $split_key;
	var $split_value;
}

//Data structure that constitutes node
class DT_Node{
	var $dtdata;
	var $left;
	var $right;
	var $terminal;
}

class Decision_Tree{
	var $bv_data;
	var $data;
	var $base_key;
	var $base_value;
	var $false_value;
	var $tree;

	function Decision_Tree($data)
	{
		$this->data       = $data;
	}
	public function classify($base_key,$base_value,$false_value){

		$this->base_key     = $base_key;
		$this->base_value 	= $base_value;
		$this->false_value 	= $false_value;

		// Convert to binary variable
		$this->bv_data = Decision_Tree::make_binary_variable_data($this->data,$base_key);


		// Generation of Decision_Tree
		$tree = Decision_Tree::make_decision_tree($this->bv_data,
												  $this->base_key,
												  $this->base_value,
												  $this->base_key,
												  $this->base_value);

		$this->tree = $tree;
		return $tree;
	} 
	public function prognosis($target){
		$result = Decision_Tree::exe_prognosis($this->tree,$target);	
		return $result;
	} 
	private function make_binary_variable_data($ori_data,$base_key)
	{
		$multiple_param = array();
		$continuous_param = array();

		$feat_array 	= array();
		$delta_I_array 	= array();


		// copy data
		$data = $ori_data;


		// Check the type of each variable (which variable has three or more types)
		$keys = array_keys($data[0]); 
		foreach ($keys as $k => $key) {
			// Excludes target variables
			if ($key == $base_key) {
				continue;
			}

			// Judgment of "continuous variable" and "multivalued variable"
			$feat_array = Handling_Array::make_feat_array($data,$key);
			if(count($feat_array)>=3){
				if(is_numeric($feat_array[0])){
					// Extracts the type of value, and if there are three or more values, the value is a "continuous variable"
					array_push($continuous_param,$key);
				}else {
					// If the value is not a numerical value, it is judged as ``multivalued variable''.
					array_push($multiple_param,$key);
				}
			}
		}



		// Compress to binary variable
		foreach ($multiple_param as $key => $pred) {
			// Compress specified multi-valued variable into binary variable
			$data = Decision_Tree::multiple_to_binary($data,$base_key,$pred);
		}
		foreach ($continuous_param as $key => $pred) {
			// Compress specified continuous variables into binary variables
			$data = Decision_Tree::continuous_to_binary($data,$base_key,$pred);
		}

		return $data;
	}
	private function continuous_to_binary($data,$base_key,$pred)
	{

		// Extract categorical variable type
		$feat_array = Handling_Array::make_feat_array($data,$pred);

		// Sort in ascending order
		asort($feat_array);


		// Group and calculate delta_I
		for ($i=1; $i < count($feat_array); $i++) { 

			// group Create 
			$combs[$i] = array_slice($feat_array,0,$i); 

			// Compress to binary variable
			$tmpdata = Decision_Tree::to_binary_data($data,$pred,$combs[$i],'type1','type2');

			// Calculate delta_I
			$delta_I_array[$i] = CART::calc_delta_I($tmpdata,$base_key,$pred);

		}

		// delta_I Find the key of the group that maximizes
		$maxdikeys = array_keys($delta_I_array,max($delta_I_array));
		$maxdikey  = $maxdikeys[0];


		// Set the name of type1 , type2
		$type1_name = "";
		$type2_name = "";
		$groupmax = max($combs[$maxdikey]);

		$type1_name = $groupmax . " <=x";
		$type2_name = $groupmax . " >x";



		// Create renamed data.
		foreach ($data as $num => $array) {
			$chk = $array[$pred];
			if(in_array($chk,$combs[$maxdikey])){
				$tmparray = $array;
				$tmparray[$pred] = $type1_name;  
				$tmpdata[$num] = $tmparray; 
			}else{
				$tmparray = $array;
				$tmparray[$pred] = $type2_name; 
				$tmpdata[$num] = $tmparray; 
			}
		}

		return $tmpdata;
	}
	private function multiple_to_binary($data,$base,$pred)
	{

		// Extract categorical variable type
		$feat_array = Handling_Array::make_feat_array($data,$pred);

		// Extract combinations
		//$combs = split_data($feat_array);
		$combs = Listing_Combination::list_comb($feat_array);


		// Divide into groups and calculate delta_I for each group
		foreach ($combs as $combkey => $comb) {
			// Create a compressed array into a binary variable
			$tmpdata = Decision_Tree::to_binary_data($data,$pred,$comb,'type1','type2');

			// Calculate delta_I
			$delta_I_array[$combkey] = CART::calc_delta_I($tmpdata,$base,$pred);
		}


		// Find the key of the group with the highest delta_I
		$maxdikeys = array_keys($delta_I_array,max($delta_I_array));
		$maxdikey  = $maxdikeys[0];


		// Set the name of type1 , type2
		$type1_name = "";
		$type2_name = "";
		foreach ($feat_array as $key => $value) {
			if(in_array($value,$combs[$maxdikey])){
				$type1_name .= $value;	
			}else {
				$type2_name .= $value; 
			}
		}

		// Create renamed data.
		$tmpdata = Decision_Tree::to_binary_data($data,$pred,$combs[$maxdikey],$type1_name,$type2_name);


		return $tmpdata;
	}

	private function to_binary_data($data,$pred,$comb,$name1,$name2) 
	{
		// Create a compressed array into a binary variable
		foreach ($data as $num => $array) {
			$chk = $array[$pred];
			if(in_array($chk,$comb)){
				$tmparray = $array;
				$tmparray[$pred] = $name1;
				$tmpdata[$num] = $tmparray; 
			}else {
				$tmparray = $array;
				$tmparray[$pred] = $name2;
				$tmpdata[$num] = $tmparray; 
			}
		}
		return $tmpdata;	
	}

	public function exe_prognosis($tree,$target){

		// If it is a terminal node, returns the value of the objective variable at that node
		if($tree->terminal){
			// Check the number of target variable values for each type
			$true_num  = $tree->dtdata->match;
			$false_num = $tree->dtdata->unmatch;

			//   
			if($true_num > $false_num){
				$pars = $true_num / ($true_num + $false_num);
				echo $pars."\n";
				return $this->base_value;
			}else {
				$pars = $false_num / ($true_num + $false_num);
				echo $pars."\n";
				return $this->false_value;
			}
		// If it is not the end node, check the branch condition
		}else {
			$split_key = $tree->left->dtdata->split_key;
			$lval 	   = $tree->left->dtdata->split_value;
			$rval 	   = $tree->right->dtdata->split_value;
		}

		
		// Check if continuous variable
		$feat_array = Handling_Array::make_feat_array($this->data,$split_key);
		if(count($feat_array)>3  && is_numeric($feat_array[0])){
			if(!(strstr('<=x',$lval)==false)){
				$flg = 1;
				$border = trim($lval,'<=x');			
			}else {
				$flg = 2;
				$border = trim($rval,'<=x');			
			}
		}else {
			$flg = 0;
		}

		// The next node is calculated according to the branch condition.
		switch ($flg) {
		case 0:
			// Branching method when categorical variables are not original continuous variables
			if(!(strstr($lval,$target[$split_key])==false))
			{
				return Decision_Tree::exe_prognosis($tree->left,$target);
			}
			else if (!(strstr($rval,$target[$split_key])==false))
			{
				return Decision_Tree::exe_prognosis($tree->right,$target);			
			}
			break;
		case 1:
			// Branching method when categorical variable is original continuous variable
			if($border >= $target[$split_key]){
				return Decision_Tree::exe_prognosis($tree->right,$target);
			}else{
				return Decision_Tree::exe_prognosis($tree->left,$target);			
			}
			break;
		case 2:
			// Branching method 2 when categorical variable is original continuous variable
			if($border >= $target[$split_key]){
				return Decision_Tree::exe_prognosis($tree->left,$target);
			}else{
				return Decision_Tree::exe_prognosis($tree->right,$target);			
			}
			break;
		default:
			break;
		}	
	} 

	private function make_decision_tree($data,$base,$base_value,$split_key,$split_value){

		$delta_I_array = array();	
		$dtnode = new DT_Node();
		$dtdata = new DT_Data();

		// set dtdata
		$dtdata = Decision_Tree::set_DtData($data,$base,$base_value);
		$dtdata->split_key   = $split_key;
		$dtdata->split_value = $split_value;
		$dtnode->dtdata 	 = $dtdata; 
		$dtnode->terminal    = false;



		// Calculate delta_I for each categorical variable
		$keys = array_keys($data[0]); 
		foreach ($keys as $k => $key) {
			if ($key == $base) {
				continue;
			}
			// delta_I Calculate
			$delta_I_array[$key] = CART::calc_delta_I($data,$base,$key);
		}
		

		// End if delta_I is all zero
		// The termination conditions around here are quite appropriate
		$flg =0;
		foreach ($delta_I_array as $key => $value) {
			if($value != 0.0){$flg=1;}
		}
		if($flg==0){
			$dtnode->terminal= true;
			return $dtnode;
		}


		// Extract maximum delta_I
		$split_key = array_keys($delta_I_array,max($delta_I_array));

		// Divide data by the categorical variable that maximizes delta_I
		$split_array = Handling_Array::split_by_pred($data,$split_key[0]);
		$i = 0;
		foreach ($split_array as $key => $value) {
			switch ($i) {
			case 0:
				$dtnode->left = Decision_Tree::make_decision_tree($value,$base,$base_value,$split_key[0],$key);
				break;
			case 1:
				$dtnode->right = Decision_Tree::make_decision_tree($value,$base,$base_value,$split_key[0],$key);
				break;
			default:
				break;
			}
			$i++;
		}


		// When decided, return DT_Node
		return $dtnode;

	}
	private function set_DtData($data,$base,$value)
	{
		// Categorical variable extraction
		$split_array = Handling_Array::split_by_pred($data,$base);


		// Create DT_Data
		$dtdata = new DT_Data();
		$dtdata->number = count($data);
		if(isset($split_array[$value])){
			$dtdata->match   = count($split_array[$value]);
		}else {
			$dtdata->match   = 0;
		}
		$dtdata->unmatch   = $dtdata->number - $dtdata->match; 

		return $dtdata;
	}

}

?>
	
	
