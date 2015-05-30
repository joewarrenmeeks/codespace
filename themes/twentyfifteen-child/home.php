<?php
/*
Template Name: Static Home Page
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8" />
<meta http-equiv="refresh" content="33;URL='http://www.thecode.space/?page_id=10'"/>
<title>THE CODE</title>

<style>

@import url(http://fonts.googleapis.com/css?family=Droid+Sans:400,700);

* { padding: 0; margin: 0; }


body, html
{
	width: 100%;
	height: 100%;
	font-family: "Droid Sans", arial, verdana, sans-serif;
	font-weight: 700;
	color: #8b2;
	background-color: #000;
	overflow: hidden;
}



p#start
{
	position: relative;
	width: 16em;
	font-size: 200%;
	font-weight: 400;
	margin: 20% auto;
	color: #8b2;
	opacity: 0;
	z-index: 1;
	-webkit-animation: intro 2s ease-out;
	-moz-animation: intro 2s ease-out;
	-ms-animation: intro 2s ease-out;
	-o-animation: intro 2s ease-out;
	animation: intro 2s ease-out;
}

@-webkit-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-moz-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-ms-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@-o-keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

@keyframes intro {
	0% { opacity: 1; }
	90% { opacity: 1; }
	100% { opacity: 0; }
}

h1
{
	position: absolute;
	width: 2.6em;
	left: 50%;
	top: 25%;
	font-size: 10em;
	text-align: center;
	margin-left: -1.3em;
	line-height: 0.8em;
	letter-spacing: -0.05em;
	color: #000;
	text-shadow: -2px -2px 0 #ff6, 2px -2px 0 #ff6, -2px 2px 0 #ff6, 2px 2px 0 #ff6;
	opacity: 0;
	z-index: 1;
	-webkit-animation: logo 5s ease-out 2.5s;
	-moz-animation: logo 5s ease-out 2.5s;
	-ms-animation: logo 5s ease-out 2.5s;
	-o-animation: logo 5s ease-out 2.5s;
	animation: logo 5s ease-out 2.5s;
}

h1 sub
{
	display: block;
	font-size: 0.3em;
	letter-spacing: 0;
	line-height: 0.8em;
}

@-webkit-keyframes logo {
	0% { -webkit-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -webkit-transform: scale(0.1); opacity: 0; }
}

@-moz-keyframes logo {
	0% { -moz-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -moz-transform: scale(0.1); opacity: 0; }
}

@-ms-keyframes logo {
	0% { -ms-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -ms-transform: scale(0.1); opacity: 0; }
}

@-o-keyframes logo {
	0% { -o-transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { -o-transform: scale(0.1); opacity: 0; }
}

@keyframes logo {
	0% { transform: scale(1); opacity: 1; }
	50% { opacity: 1; }
	100% { transform: scale(0.1); opacity: 0; }
}

/* the interesting 3D scrolling stuff */
#titles
{
	position: absolute;
	width: 18em;
	height: 50em;
	bottom: 0;
	left: 50%;
	margin-left: -9em;
	font-size: 350%;
	text-align: center;
	overflow: hidden;
	-webkit-transform-origin: 50% 100%;
	-moz-transform-origin: 50% 100%;
	-ms-transform-origin: 50% 100%;
	-o-transform-origin: 50% 100%;
	transform-origin: 50% 100%;
	-webkit-transform: perspective(300px) rotateX(5deg);
	-moz-transform: perspective(300px) rotateX(5deg);
	-ms-transform: perspective(300px) rotateX(5deg);
	-o-transform: perspective(300px) rotateX(5deg);
	transform: perspective(300px) rotateX(5deg);
}

#titles:after
{
	position: absolute;
	content: ' ';
	left: 0;
	right: 0;
	top: 0;
	bottom: 60%;
	background-image: -webkit-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -moz-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -ms-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: -o-linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	background-image: linear-gradient(top, rgba(0,0,0,1) 0%, transparent 100%);
	pointer-events: none;
}

#titles p
{
	text-align: center;
	margin: 0.8em 0;
}

#titles p.center
{
	text-align: center;
}

#titles a
{
	color: #8b2;
	text-decoration: underline;
}

#titlecontent
{
	position: absolute;
	top: 100%;
	-webkit-animation: scroll 100s linear 4s infinite;
	-moz-animation: scroll 100s linear 4s infinite;
	-ms-animation: scroll 100s linear 4s infinite;
	-o-animation: scroll 100s linear 4s infinite;
	animation: scroll 100s linear 4s infinite;
}

/* animation */

@-webkit-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-moz-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-ms-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@-o-keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

@keyframes scroll {
	0% { top: 100%; }
	100% { top: -170%; }
}

img.top {
	vertical-align: text-top;
}
</style>



</head>

<body>

<a href="http://www.thecode.space/?page_id=10">
<img class="top" src="http://www.thecode.space/wp-content/uploads/2015/05/avatar.png">
<center>
  <h1>
    THE CODE<p>
    <img class="top" src="http://www.thecode.space/wp-content/uploads/2015/05/avatar.png">
  </h1>
</center>
</a>

<div id="titles"><div id="titlecontent">

	<p>We are Powerful.</p>
	<p>We are Fair.</p>
	<p>We believe in a world where YOU, the INDIVIDUAL is king.</p>
	<p>The Alliance LIES to you.</p>
	<p>The Empire OPPRESSES  you.</p>
	<p>The Federation DOESN'T CARE about you.</p>
	<p>WE DO.</p>
	<p>Join US and become a better person.</p>
	<p>Own YOUR future.</p>
	<p>Make YOUR OWN decisions.</p>
  <p> </p>
  <p>We are THE CODE.</p>
   <p><img class="top" src="http://www.thecode.space/wp-content/uploads/2015/05/avatar.png"></p>

</div></div>
</body>

</html>
