<?php

date_default_timezone_set('America/Los_Angeles');
$base_url = trim(base_url(),"/");
$home = anchor("welcome","Quizable.org",Array('id' => 'logo'));
$home = "<img src=$base_url/logo.png>";
$user_name = $this->session->userdata('user_name');
if (empty($user_name))
	$login = anchor("welcome/login","Login","id=\"plain_link\"") . " | " . anchor("welcome/about","About","id=\"plain_link\"") . " | <a href=http://www.quizable.org/wiki/doku.php?id=wiki:welcome id=\"plain_link\">Tutorial</a>";
else $login = $user_name . ": " . anchor("welcome/main_menu","Main Menu","id=\"plain_link\"") . " | " . anchor("welcome/logout","Logout","id=\"plain_link\"") . " | " . anchor("welcome/about","About","id=\"plain_link\"") . " | <a href=http://www.quizable.org/wiki/doku.php?id=wiki:welcome id=\"plain_link\">Tutorial</a>";
PRINT<<<END
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Quizable: Ask your students questions</title>
	<link rel="stylesheet" type="text/css" href="$base_url/quizable.css"/>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="6gg7xxq57f20ksg"></script>	
	
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="$base_url/humanity/jquery-ui-1.10.3.custom.min.css">
	<script src="$base_url/humanity/jquery-ui-1.10.3.custom.min.js"></script>
	<script src="$base_url/timepicker.js"></script>
	<script type="text/javascript" src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
	
	
	<script type="text/x-mathjax-config">
  MathJax.Hub.Config({
    extensions: ["tex2jax.js"],
    jax: ["input/TeX", "output/HTML-CSS"],
    tex2jax: {
      inlineMath: [ ['$','$'] ],
      displayMath: [ ['$$','$$']],
      processEscapes: true
    },
    "HTML-CSS": { availableFonts: ["TeX"] }
  });
</script>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-43670903-1', 'quizable.org');
  ga('send', 'pageview');

</script>


</head>

<body>
 <div id="header">
 <span id="logo">$home</span>
 <span id="nav">$login</span>
 </div>

END;
?>
<p/>
<div id="page_contents">

