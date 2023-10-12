function _getCenterXAlert(pWidth){
	lCenter=window.innerWidth/2;
	return lCenter-(pWidth/2)
}

function _getCenterYAlert(pHeight){
	lCenter=window.innerHeight/2;
	return lCenter-(pHeight/2)
}

function _alertClose(){
	lColectionDiv=document.getElementsByTagName("div")
	document.body.removeChild(lColectionDiv[lColectionDiv.length-1])
	document.body.removeChild(lColectionDiv[lColectionDiv.length-1])
	if (this.after_close) this.after_close();
    window.onkeydown=null
}

function alert(pMsg,pFunction,pAlertType,pTitle){
	if (!pTitle) pTitle=".: <?=$_SESSION["application"]?> :.";
    
    if (pAlertType=="") pAlertType="ok";
	else if (pMsg!="") {
    	if (pMsg.search("Error:")>-1 || pMsg.search("Error :")>-1) pAlertType="error"
        else if (pMsg.search("Warning")>-1) pAlertType="warning"
        else pAlertType="ok";
	}
    this.overlay=document.createElement("div")
    	this.overlay.setAttribute("style","background-color:#e6e6e6;opacity:0.5;position:absolute;top:0px;left:0px;width:100%;height:100%")
    
    this.overlay.style.height=parent.document.body.scrollHeight+((parent.document.body.scrollHeight-parent.document.body.offsetHeight-1)*5);
	document.body.appendChild(this.overlay)
    this.alert_window=document.createElement("div")
	lHTML="<table cellpadding='0' cellspacing='0' width='350' bgcolor='#ffffff' style='border: solid 1px;-moz-border-radius:10px'>"
	if (pTitle) {
		lHTML+="<tr>"
		lHTML+="<td colspan='2' align='center' bgcolor='#b5b5b5' style='-moz-border-radius:10px 10px 0 0;border-bottom: solid 1px'>"+pTitle+"</td>"
		lHTML+="</tr>"
	}
	lHTML+="<tr>"
	lHTML+="<td width='40' align='center' valign='top' style='padding-top:10'><img src='images/icon_"+pAlertType+".gif'></td>"
	lHTML+="<td style='padding:10 5 5 5'>"+pMsg+"<br><br></td>"
	lHTML+="</tr>"
	lHTML+="<tr>"
	lHTML+="<td colspan='2' align='center' style='padding: 5 0 5 0'><input type='button' value='OK' onclick=\"this.close()\"></td>"
	lHTML+="</tr>"
	lHTML+="</table>"
	this.alert_window.innerHTML=lHTML
	
    if (!document.getElementById("divAlert")) document.body.appendChild(this.alert_window)

this.alert_window.setAttribute("style","position:absolute;top:"+_getCenterYAlert(this.alert_window.offsetHeight)+";left:"+_getCenterXAlert(350))

    lInputs=this.alert_window.getElementsByTagName("input")
    lInputs[lInputs.length-1].close=_alertClose
    lInputs[lInputs.length-1].alert_window=this
    lInputs[lInputs.length-1].focus();
    if (pFunction) lInputs[lInputs.length-1].after_close=pFunction    
    
    window.onkeydown=function (event){
                        if(event.keyCode==9) return false; 
                        else return true;
                      };
}
