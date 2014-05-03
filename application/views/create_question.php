<?php
//$class_hash is defined.
//if $question_hash is defined, we're in edit question mode.

$user_hash = $this->session->userdata('user_hash');

if (!empty($question_hash))
	{
		$data = $this->Question->get_question_data($question_hash);
		$raw_input = $data['raw_input'];
		$deadline_date = $data['deadline_date'];
		$deadline_time = $data['deadline_time'];
		$share_list = $this->Question->get_share_list($question_hash);
	}
else 
	{
		$question_hash = "";
		$share_list = "";
	}

if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
	{
		echo "You don't seem to be the owner of this class.  ";
		echo anchor("welcome/main_menu","Return");
		return;
	}

$desc = $this->Classes->get_class_desc($class_hash);

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
	
echo "<h1>Create a question for $desc</h1>";

if (empty($raw_input))
	$raw_input = "";
if (!empty($question))
	$raw_input = $question;
	
if (empty($question_hash))
	{
		$deadline_date = $this->Sticky->get($user_hash,'deadline_date');
		$deadline_time = $this->Sticky->get($user_hash,'deadline_time');
	}
else
	{
		$deadline = $this->Question->get_deadline($question_hash);
		$deadline_date = $deadline['date'];
		$deadline_time = $deadline['time'];
	}
	
$share_list = $this->Sticky->get($user_hash,'share_list');

	
if (empty($deadline_date))
	$deadline_date = date("m/d/Y");
	
if (empty($deadline_time))
	$deadline_time = date("H:i");


	

echo "<h2>The question</h2>";
if (empty($question_hash))
	echo form_open_multipart("welcome/question_incoming/new/$class_hash");
else echo form_open_multipart("welcome/question_incoming/edit/$question_hash");
echo "<textarea rows=5 cols=60 name=question id=question_input>";
echo $raw_input;
//echo "mc//this is my question//1//*2//3//#end//1//3//0.1";
echo "</textarea>";
echo "<p/>";
echo "<h2>Deadline</h2>";
echo "Date: ";
echo "<input type=text name=deadline_date id=deadline_date size=11 value=\"$deadline_date\">";
echo "  Time: ";
echo "<input type=text name=deadline_time id=deadline_time size=5 value=\"$deadline_time\">";
echo "<p/>";

echo "<h2>Attachment</h2>";
echo "<input type=file name=userfile>";
echo "<br/>";
if (!empty($question_hash))
	{
		echo "<div id=\"note\">";
		echo "Attachments: ";
		echo $this->Question->get_attachment_list($question_hash);
		echo "</div>";
	}

echo "<p/>";

echo "<h2>Share question</h2>";
if (!empty($question_hash))
	{
		$ret = $this->Question->get_share_code_list($question_hash);
		if ($ret !== false)
			{
				echo "<div id=\"share_list\">This question is currently shared with: ";
				echo str_replace(",",", ",$ret);
				echo "</div>";
				echo "<p/>";
			}
	}
echo "<input type=text name=share_list size=30 id=question_input value=\"$share_list\">";
echo "<br/>";
echo "<div id=\"note\">(Comma separated list of class codes to which question should be shared.)</div>";
echo "<p/>";


echo "<input type=submit value=\"Save\">";
echo " | ";
echo "<a href=# onclick=\"preview()\" id=\"return_link\">Preview</a>";
echo " | ";
echo anchor("welcome/class_menu/$class_hash","Return",Array('id' => 'return_link'));
echo form_close();
echo "<hr/>";
?>
<div id=rt_help>
<b>Multiple choice:</b> mc//question-text//choice1//choice2//..//choiceN//#end//points-worth//allowed-attempts//deductions-per-attempt//answer-on-expire(yes/no)
<br/>
<b>Numerical:</b> num//question-text//correct-answer//units//allowable-margin-%//absolute-value-(yes/no)//points-worth//allowed-attempts//deductions-per-attempt//answer-on-expire-(yes/no)
<br/>
<b>Short answer:</b> sa//question-text//points-worth
<br/>
<b>Drawing:</b> dr//describe-what-should-be-drawn//use-attachment-as-canvas-background-(yes/no)//points-worth
<br/>
<b>Attachment:</b> at//describe-what-should-be-attached//points-worth
<br/>
<b>Link:</b> ln//describe-what-the-link-should-point-to//points-worth
<br/>
<b>Peer-reviewed:</b> pr//problem1//problem2//..//problemN//#end//points-earned-for-an-up-vote//points-lost-for-a-down-vote//points-for-participating-in-voting//minimum-time-between-votes//assign-owner-problems
</div>

<div id=simulate></div>

<script>
$(function() {
    $( "#deadline_date" ).datepicker();
      $('#deadline_time').timepicker();
  });
  
  
function preview()
{
	var question = $('#question_input').val();
	
	$.ajax({
		  type: "POST",
		  url: '<?php echo site_url('welcome/simulate'); ?>',
		  data: {qdata: question, question_hash: '<?php echo $question_hash; ?>'},
		  success: function(msg) 
		  				{ 
		  					$('#simulate').html('<div id=\'close_click\'>[<a href=# onclick=\'close_preview()\'>x</a>]</div>' +msg); 
		  					$('#simulate').css('display','inline-block'); 
		  					MathJax.Hub.Queue(["Typeset",MathJax.Hub]);
		  				},
		});

}

function close_preview()
{
	$('#simulate').html('');
	$('#simulate').css('display','none'); 

}

</script>