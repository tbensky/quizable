<?php
function is_blank($value) 
{
	return empty($value) && !is_numeric($value);
}
?>
