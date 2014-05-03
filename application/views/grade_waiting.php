<?php
//$question_hash is defined

$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
$desc = $this->Classes->get_class_desc($class_hash);
$grader_share_hash = $this->Question->get_grader_share_link($class_hash,$question_hash);
if (empty($grader))
	$grader = false;
	
echo "<h1>Manual grading for $desc</h1>";

$grader_link = site_url("welcome/grader/$grader_share_hash");
if ($grader === false)
	{
		echo "<div id=\"grader_share_link\">Grader share link: <input id=\"grader_share_link\" type=text size=80 value=\"$grader_link\"></div>";
		echo "<p/>";
		echo "[";
		echo anchor("welcome/class_menu/$class_hash","Return",Array('id' => 'return_link'));
		echo "]";

		echo "<p/>";
	}

echo "<h2>Question</h2>";
$raw = $this->Question->get_raw_question_data($question_hash);

echo "<span id=\"hl\">For " . $raw['points'] . " points</span>: " . $raw['qtext'];
echo "<p/>";

echo "<div id=\"manual_grade\">";
echo "</div>";

?>

<script>

update_grading();

function update_grading()
{
	$('#manual_grade').html("<div id=\"loading\">Loading...</div>");
	$.get('<?php echo site_url("welcome/dump_waiting_answers/$question_hash"); ?>', function(data) { $('#manual_grade').html(data); });
}

function accept_input(answer_hash)
{
	var comment_field = '#comment_' + answer_hash;
	var answer_div = '#answer_' + answer_hash;
	var comment = $(comment_field).val();
	
	$.ajax({
	  type: "POST",
	  url: '<?php echo site_url("welcome/incoming_comment/$question_hash"); ?>',
	  data: {answer_hash: answer_hash, comment: comment, <?php if ($grader === true) echo "grader_share_hash: '$grader_share_hash'"; ?>},
	  //success: function() { update_grading(); }
	  //success: function() { $(answer_div).html(''); var gc = $('#grade_count').text(); gc--; $('#grade_count').text(gc); }
		success: function() { $(answer_div).hide("explode",{pieces: 25},500); var gc = $('#grade_count').text(); gc--; $('#grade_count').text(gc); }

});
}


function increase(answer_hash)
{
	var comment_field = '#comment_' + answer_hash;
	var comment = $(comment_field).val();
	comment++;
	$(comment_field).val(comment);
}

function decrease(answer_hash)
{
	var comment_field = '#comment_' + answer_hash;
	var comment = $(comment_field).val();
	comment--;
	$(comment_field).val(comment);
}

</script>
