#calendar
{
	width: 220px;
	height: 165px;
	text-align: center;
	margin: 5px auto;
	border: 1px solid #000000;
	background-color: #f3f8ff;
	position: absolute;
	font: 11px Trebuchet MS;
}

ul
{
	list-style-type: none;
	margin:0;
	padding:0;
}
.months, .emptM, .headDay, .dayNormal, .dayBlank, .dayDisabled, .dayWeekend, .dayCurrent, .yearBrowse, .monthDisabled, .currMonth, #closeBtn  
{
	margin: 1px 0 0 1px;
	padding: 0;
	width: 35px;
	height: 14px;
	line-height: 14px;
	float: left;
	text-align: center;
	background-color: #feefe4;
	color: #000;
	display: inline;
}
.emptM, .dayDisabled, .monthDisabled
{
	color: #d7d6d5;
	background-color: #f2f2f2;
}
.headDay
{
	color: #fff;
	background-color: #48688f;
	width: 30px;
}
.dayNormal, .dayBlank, .dayWeekend, .dayCurrent
{
	color: #fff;
	background-color: #70b0ff;
	width: 30px;
}
.dayBlank{background-color: #f3f8ff}
.dayWeekend{background-color: #ff6161}
.dayCurrent, .currMonth{background-color: #71d45b}
.dayDisabled{width: 33px}
#days{margin-left: 1px; width: 220px;}
#elements{height: 150px;}
.months a, #days a, .currMonth a{color: #000; text-decoration: none; display: block;}
.currMonth a{color: #FFF}
#days a{color: #fff;}
.yearBrowse, #closeBtn {width: 218px; background-color: #FFF; line-height: 14px;}
.yearBrowse a, #closeBtn a{text-decoration: none; color: #f30; font-weight: bold;}
.yearBrowse b{margin: 0 5px}