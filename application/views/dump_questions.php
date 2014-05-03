<?php
//$class_hash is defined

echo "<div id=\"raw_text\">";
$this->Question->dump_all_questions($class_hash);
echo "</div>";
?>