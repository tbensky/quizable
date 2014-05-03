<?php

class Comments extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get_comments($question_hash)
    {
    	$q = $this->db->query("select * from comment where question_hash=" . $this->db->escape($question_hash) . " order by ts desc");
    	foreach($q->result_array() as $row)
    	{
    		if ($row['user_hash'] == 'downvote')
    			{
    				echo "<div id=\"downvote_comment\">";
					echo "<b>Down voted because:</b> " . $row['comment'];
					echo "</div>";
    			}
    		else
    			{
					echo "<div id=\"comment\">";
					echo "<span id=\"comment_user\">By: " . $row['user_name'] . " on " . $row['date'] . ":</span>";
					echo "<br/>";
					echo $row['comment'];
					echo "</div>";
    			}
    		echo "<p/>";
    	}
    }
    
    function post_downvote_comment($question_hash,$text)
    {
    	$user_hash = $name = "downvote";
    	$comment_hash = md5($question_hash . time() . $text);
    	$sql = "insert into comment values(NULL," . $this->db->escape($comment_hash) . "," .
															$this->db->escape($question_hash) . "," .
															$this->db->escape($user_hash) . "," .
															$this->db->escape($name) . "," .
															$this->db->escape($text) . "," .
															"now(),unix_timestamp())";
		$this->db->query($sql);
    }

}    
    
?>