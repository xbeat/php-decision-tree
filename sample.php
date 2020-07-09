<?php

	require_once('Make_Decision_Tree.php');

	$data = array();
	$data[0] = array("Life and Death"=>"Life Survival","Yan Zhu"=>"35","Gender"=>"Male","Level"=>"1 Class");
	$data[1] = array("Life and Death"=>"Survival","Yan Zhu"=>"12","Gender"=>"Female","Level"=>"1 Class");
	$data[2] = array("Life and Death"=>"Life Survival","Yan Zhu"=>"35","Gender"=>"Female","Level"=>"1 Class");
	$data[3] = array("Life and Death"=>"Death","Yan Zhu"=>"26","Gender"=>"Male","Level"=>"1st Class");
	$data[4] = array("Life and Death"=>"Life Survival","Yan Zhu"=>"23","Gender"=>"Female","Level"=>"1 Class");
	$data[5] = array("Life and Death"=>"Death","Yan Zhu"=>"31","Gender"=>"Male","Level"=>"2 etc");
	$data[6] = array("Life and Death"=>"Survival","Yan Zhu"=>"32","Gender"=>"Male","Level"=>"2 etc");
	$data[7] = array("Life and Death"=>"Survival","Yan Zhu"=>"23","Gender"=>"Male","Level"=>"2 etc");
	$data[8] = array("Life and Death"=>"Death","Yan Zhu"=>"25","Gender"=>"Male","Level"=>"2 etc.");
	$data[9] = array("Life and Death"=>"Death","Yan Zhu"=>"29","Gender"=>"Female","Level"=>"2 etc");
	$data[10] = array("Life and Death"=>"Survival","Yan Zhu"=>"40","Gender"=>"Female","Level"=>"2 etc");
	$data[11] = array("Life and Death"=>"Survival","Yan Zhu"=>"12","Gender"=>"Female","Level"=>"2 etc.");
	$data[12] = array("Life and Death"=>"Survival","Yan Zhu"=>"35","Gender"=>"Female","Level"=>"2 etc");
	$data[13] = array("Life and Death"=>"Death","Yan Zhu"=>"34","Gender"=>"Male","Level"=>"3 etc");
	$data[14] = array("Life and Death"=>"Survival","Yan Zhu"=>"23","Gender"=>"Female","Level"=>"3 etc");
	$data[15] = array("Life and Death"=>"Death","Yan Zhu"=>"31","Gender"=>"Male","Level"=>"3 etc");
	$data[16] = array("Life and Death"=>"Death","Yan Zhu"=>"32","Gender"=>"Male","Level"=>"3 etc");
	$data[17] = array("Life and Death"=>"Death","Yan Zhu"=>"23","Gender"=>"Male","Level"=>"Crew Member");
	$data[18] = array("Life and Death"=>"Death","Yan Zhu"=>"25","Gender"=>"Male","Level"=>"Crew Member");
	$data[19] = array("Life and Death"=>"Death","Yan Zhu"=>"29","Gender"=>"Female","Level"=>"Crew Member");
	$data[20] = array("Life and Death"=>"Life Survival","Yan Zhu"=>"40","Gender"=>"Female","Level"=>"Crew Member");


	// Passing data
	$dt = new Decision_Tree($data);

	//Tree generation (classification tree)
	$tree = $dt->classify('Life and Death','Survival','Death');
	echo "-------- Decision_Tree\n";
	var_dump($tree);

	//How to use rules
	$target = array("age"=>"40","Gender"=>"Male","Level"=>"2 etc");
	$res = $dt->prognosis($target);
	$res = $dt->exe_prognosis($tree,$target);

	echo "-------- estimate\n";
	var_dump($res);
?>

