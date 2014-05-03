<?php
//$question_hash and $mode (preview, real) are defined, $class_hash if mode==preview
//$attempt_count is defined too
//$user_hash is defined

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
	
if (!empty($msg_red))
	echo "<div id=\"message_red\">$msg_red</div>";

echo "<div id=\"info_box_full\">";

if (empty($type))
	$type = "";


if ($mode == 'first_done' && $type != 'sa' && $type != 'dr' && $type != 'at')
	{
		echo "You are done with this question.  You've been credited $points points for your answer.  ";
		echo anchor("welcome/question_menu/$class_hash","Return");
		echo $this->Answer->get_submitted_answers($user_hash,$question_hash);
		return;
	}
	
if ($mode == 'waiting' || ($mode == 'first_done' && ($type == 'sa' || $type == 'dr' || $type == 'at' || $type == 'pr')))
	{
		if ($type != 'pr')
			echo "You are done with this question.  It will be graded by your teacher.  ";
		else echo "You are done with this question.  It will up and down voted by your peers.  ";
		echo "<p/>";
		echo "<p/>";
		$this->Question->get_html($question_hash,$mode);
		echo $this->Answer->get_submitted_answers($user_hash,$question_hash);
		return;
	}

if ($mode != 'preview' && $this->Question->expired($question_hash) && !$this->Pr->ignore_expire($question_hash,$user_hash))
	{
		echo "<div id=\"expired_message\">This question has expired.</div>";
		$mode = 'expired';
	}
else if ($mode != 'preview')
	{
		echo "<div id=\"try_note\">";
		if ($mode == 'done')
			echo "<div id=\"expired_message\">You already submitted a correct answer to this question.</div>";
		else
			{
				$max_attempts = $this->Question->get_max_attempts($question_hash);
				$num = $this->Question->get_question_numerics($question_hash);
				$type = $this->Question->get_type($question_hash);
				$max_possible = $num['points'] - $attempt_count * $num['deduct_per_attempt'];
				$more = $max_attempts - $attempt_count;
				$attempt_count++;
				if ($attempt_count == 1 && $max_attempts == 1)
					echo "This is the only attempt you may make to answer this question.";
				else  if ($attempt_count == 1)
					echo "This is your first of $max_attempts allowable attempts on this question.";
				else 
					{
						if ($more > 1)
							echo "This is attempt #$attempt_count for you.  You are allowed a maximum of $max_attempts attempts.  You have $more tries left.";
						if ($more == 1)
							echo "This is attempt #$attempt_count for you.  You are allowed a maximum of $max_attempts attempts.  This is your last try.";
						if ($more <= 0)
							{
								echo "You have used all of your tries for this problem.";
								$mode = 'no_more_tries';
							}
					}			
				echo "<br/>";
				if ($more > 0 && $type != 'pr')
					echo "You can earn $max_possible of " . $num['points'] . " possible points on this question.";	
			}
		echo "<hr/>";
		echo "</div>";
	}
echo "<p/>";

$this->Question->get_html($question_hash,$mode);

echo $this->Answer->get_submitted_answers($user_hash,$question_hash);
echo "</div>";

echo "<p/>";
echo "<a href=# id=\"post_comment\" onClick=\"view_comment_box();\">Post comment</a>";

echo "<div id=\"comment_action\">";
echo "<textarea cols=80 rows=5 id=\"comment_box\"></textarea>";
echo "<br/>";
echo "<button onclick=\"post_comment('$user_hash','$question_hash')\">Post</button>";
echo "<button onclick=\"cancel_comment();\">Cancel</button>";
echo "<p/>";
echo "<div id=\"note\">Notes:<ul>";
echo "<li> Comments ARE NOT anonymous (they are logged and displayed with your name).";
echo "<li> For math/equations, enclose LaTex commands between <span class=\"tex2jax_ignore\">$ and $.</span>";
echo '<li> Example: <span class="tex2jax_ignore">$x^2+\gamma$</span> would render as $x^2+\gamma$.';
echo "</ul></div>";
echo "</div>";

echo "<p/>";
echo "<div id=\"comment_section\"></div>";
echo "<p/>";




?>

<script>
$( document ).ready(function() {
		  load_comments();
});

function load_comments()
{
$.ajax({
		  type: "POST",
		  url: '<?php echo site_url("welcome/dump_comments/$question_hash"); ?>',
		  success: function(ret) { $('#comment_section').html(ret); MathJax.Hub.Queue(["Typeset",MathJax.Hub]); },
		});

}

function view_comment_box()
{
	$('#comment_action').css('display','inline');
}

function cancel_comment()
{
	$('#comment_box').val('');
	$('#comment_action').css('display','none');
}

function post_comment(user_hash,question_hash)
{
	var text = $('#comment_box').val();
	$.ajax({
		  type: "POST",
		  data: {comment: text,user_hash: user_hash},
		  url: '<?php echo site_url("welcome/post_comment/$user_hash/$question_hash"); ?>',
		  success: function(ret) { console.log(ret); cancel_comment(); load_comments(); },
		});

}

function update_view()
{
	$.ajax({
		  type: "POST",
		  url: '<?php echo site_url("welcome/update_view/$question_hash"); ?>',
		  });
	return(true);
}

</script>
