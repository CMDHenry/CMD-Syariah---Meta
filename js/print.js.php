function _printClose(){
	lDiv=document.getElementById("divPrintOverlay")
	if (lDiv) document.body.removeChild(lDiv)
	lDiv=document.getElementById("divPrint")
	if (lDiv) document.body.removeChild(lDiv)
}

function fPrint(pCommand){
    //confirm("masuk")
	lDivOverlay=document.createElement("div")
	lDivOverlay.setAttribute("id","divPrintOverlay")
	lDivOverlay.setAttribute("style","background-color:#e6e6e6;opacity:0.5;position:absolute;top:0px;left:0px;width:100%;height:100%")
	document.body.appendChild(lDivOverlay)

	lDiv=document.createElement("div");
	lDiv.setAttribute("id","divPrint")
	lHTML="<table cellpadding='0' cellspacing='0' width='250' bgcolor='#FFFFFF' style='border: solid 1px;-moz-border-radius:10px'>"
    lHTML+="<tr>"
    lHTML+="<td colspan='2' align='center' bgcolor='#b5b5b5' style='-moz-border-radius:10px 10px 0 0;border-bottom: solid 1px'>Print Stiker</td>"
    lHTML+="</tr>"
	lHTML+="<tr>"
	lHTML+="<td width='40' align='center' valign='top' style='padding-top:25'><img src='images/icon_warning.gif'></td>"
	lHTML+="<td style='padding:10 5 5 5'><APPLET codebase='java' code='PrintBarcode.class' width='150' height='50'><PARAM name='command'  value=\""+pCommand+"\"></APPLET><br><br></td>"
	lHTML+="</tr>"
	lHTML+="<tr>"
	lHTML+="<td colspan='2' align='center' style='padding: 5 0 5 0'><input type='button' value='OK' onclick=\"_printClose(function(){})\"></td>"
	lHTML+="</tr>"
	lHTML+="</table>"
    lDiv.innerHTML=lHTML;
  	document.body.appendChild(lDiv)
	lDiv.setAttribute("style","position:absolute;top:"+_getCenterYPrint(lDiv.offsetHeight)+";left:"+_getCenterXPrint(350))
}

function _getCenterXPrint(pWidth){
	lCenter=window.innerWidth/2;
	return lCenter-(pWidth/2)
}

function _getCenterYPrint(pHeight){
	lCenter=window.innerHeight/2;
	return lCenter-(pHeight/2)
}