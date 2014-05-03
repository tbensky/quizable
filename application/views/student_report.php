<?php
//$user_hash and $class_hash are defined.
//$sort_by might be defined

$desc = $this->Classes->get_class_desc($class_hash);
if (empty($sort_by))
	$sort_by = "points";
	
if ($sort_by == 'dropbox')
{
	echo $desc;
	echo "\n";
	date_default_timezone_set('America/Los_Angeles');
}
else
	{
	
		echo "<h1>Student Report for $desc</h1>";
		echo anchor("welcome/main_menu","Return",Array("id" => 'return_link'));
		echo " | ";
		echo anchor("welcome/student_report/$class_hash/points","Sort by points",Array("id" => 'return_link'));
		echo " | ";
		echo anchor("welcome/student_report/$class_hash/last","Sort by Last name",Array("id" => 'return_link'));
		echo " | ";
		$url = site_url("welcome/dropbox_student_report/$user_hash/$class_hash/dropbox");
		$name = str_replace(' ','-',Date("M-d-Y-") . "$desc.csv");
		$name = str_replace(':','-',$name);
		$name = str_replace('"','-',$name);
		echo<<<EOT
		<a href="$url" data-filename="$name" class="dropbox-saver"></a>
EOT;
		
		echo "<p/>";
	}

if ($sort_by != 'points' &&  $sort_by != 'last' && $sort_by != 'dropbox')
	$sort_by = "last";

switch($sort_by)
	{
			case 'last':
			case 'dropbox':
			$sql = "select last,first,user_hash,sum(answer.points) as points from enroll inner join answer on enroll.user_hash=answer.student_hash and answer.class_hash=" . $this->db->escape($class_hash) ." where enroll.class_hash= " . $this->db->escape($class_hash) . " and answer.correct='yes' group by answer.student_hash order by last asc";
			break;

			case 'points':
			default:
			$sql = "select last,first,user_hash,sum(answer.points) as points from enroll inner join answer on enroll.user_hash=answer.student_hash and answer.class_hash=" . $this->db->escape($class_hash) ." where enroll.class_hash= " . $this->db->escape($class_hash) . " and answer.correct='yes' group by answer.student_hash order by points desc";
			break;
	}

$q = $this->db->query($sql); 
$c = 1;
foreach($q->result_array() as $row)
	{
		if ($c == 1)
			{
				if ($sort_by != 'dropbox')
					echo "<b>Max. Points,</b>" . $this->Answer->get_max_points($class_hash) . "<br/>";
				else echo "Max. Points," . $this->Answer->get_max_points($class_hash) . "\n";
				$c++;
			}
		echo $row['last'] . "," . $row['first'] . "," . round($row['points'],1);
		if ($sort_by != 'dropbox')
			echo "<br/>";
		else echo "\n";
	}
?>
