<?php
//$class_hash is defined.

$user_hash = $this->session->userdata('user_hash');

if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
	{
		echo "You don't seem to be the owner of this class.  ";
		echo anchor("welcome/main_menu","Return");
		return;
	}

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";

	
$desc = $this->Classes->get_class_desc($class_hash);
echo "<h1>$desc</h1>";

echo $this->Question->dump_question_list_for_owner($class_hash);
?>

<script>
function confirm_delete(question_hash)
{
	$( "#confirm_question_delete" ).dialog();
	$('#confirm_question_delete').html(
					'This will delete the question and all answers associated with it.<p/>'+
					'<a href="<?php echo base_url(); ?>' + 'index.php/welcome/delete_question/' + question_hash + '">Confirm</a>' +
					' | <a href=# onclick=\"close_confirm();\">Cancel</a>');

}

function confirm_delete_answers(question_hash)
{
	$( "#confirm_question_delete_answers" ).dialog();
	$('#confirm_question_delete_answers').html(
					'This will delete any <u>answers</u> that may have arrived for this question.<p/>'+
					'<a href="<?php echo base_url(); ?>' + 'index.php/welcome/delete_question_answers/' + question_hash + '">Confirm</a>' +
					' | <a href=# onclick=\"close_confirm_answers();\">Cancel</a>');

}

function close_confirm()
{
	$( "#confirm_question_delete" ).dialog("close");
}

function close_confirm_answers()
{
	$( "#confirm_question_delete_answers" ).dialog("close");
}

function open_status_window()
{
	$( "#status_window").dialog();
}

function close_status_window()
{
	$( "#status_window").dialog("close");
}

</script>

<div id="confirm_question_delete" title="Confirm Delete Question"></div>
<div id="confirm_question_delete_answers" title="Confirm Delete Answers"></div>