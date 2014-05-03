<?php
//$user_hash nad $class_hash are defined

if (!$this->Classes->verify_class_joined($user_hash,$class_hash))
	{
		echo "You have not joined this class. Please join it first.  ";
		echo anchor("welcome/main_menu","Return");
		echo " | ";
		echo anchor("welcome/join","Join");
		return;
	}
	
$desc = $this->Classes->get_class_desc($class_hash);

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
	
echo "<h1>$desc</h1>";

if ($this->Classes->get_class_status($class_hash) != 'open' && !$this->Classes->verify_class_owner($user_hash,$class_hash))
	{
		echo "<span id=\"message_red\">Your teacher has closed this class for maintenance.  It will open shortly.</span>";
		echo "<p/>";
		echo anchor("welcome/main_menu","Return");
		return;
	}

//$ret = $this->Answer->get_grade($user_hash,$class_hash);
$ret = $this->Answer->get_grade_non_pr_probs($user_hash,$class_hash);
$ret = $this->Answer-> get_grade_non_pr_probs_past_deadline($user_hash,$class_hash);
echo "<h2>";
echo "You've earned ";
echo $ret['earned'];
if ($ret['possible'] == 0)
	echo " points. ";
else
	{
		echo " of ";
		echo $ret['possible'] . " points or ";
		echo $ret['per'] . "% of the possible credit.";
	}
echo "<br/>";


$ret = $this->Pr->get_grade_pr_probs($user_hash,$class_hash);
if ($ret['earned'] > 0)
	{
		echo "On peer-reviewed work, you have earned ";
		echo $ret['earned'];
		if ($ret['possible'] == 0)
			echo " points. ";
		else
			{
				echo " of ";
				echo $ret['possible'] . " points or ";
				echo $ret['per'] . "% of the possible credit.";
			}
	}

echo "</h2>";

echo "<p/>";
echo $this->Question->dump_question_list_for_student($user_hash,$class_hash);
$view_url = site_url("welcome/update_view");
echo <<<EOT

<script>
function update_view(link,question_hash)
{
	$.ajax({
		  type: "POST",
		  url: '$view_url/' + question_hash,
		  success: function() {window.location=link;}
		  });
	return(true);
}
</script>

EOT;
		
?>