function getCenterX(pWidth){
	lCenter=screen.width/2;
	return lCenter-(pWidth/2)
}

function getCenterY(pHeight){
	lCenter=screen.height/2;
	return lCenter-(pHeight/2)
}

function _getCenterXiFrame(pWidth){
	lCenter=top.window.innerWidth/2;
    lReturn=lCenter-(pWidth/2)
    if (lReturn<0)lReturn=0;
	return lReturn
}

function _getCenterYiFrame(pHeight){
	lCenter=(top.document.body.offsetHeight-20)/2;
    lReturn=lCenter-(pHeight/2)
    if (lReturn<0)lReturn=0;
	return lReturn
}

function _modalClose(){
    if (this.after_close) {
    	if (this.objectReturn) this.after_close(this.objectReturn.value);
        else this.after_close()
    }

	lColectionDiv=top.document.getElementsByTagName("div")
	top.document.body.removeChild(lColectionDiv[lColectionDiv.length-1])
	top.document.body.removeChild(lColectionDiv[lColectionDiv.length-1])

    if (window.onkeydown) window.onkeydown=null
    else top.window.onkeydown=null
}

function show_modal(pUrl,pOtion,pFunction){
	document.activeElement.blur()
    
	var l_arr_option=Array();
    while (pOtion.indexOf("px")>-1) {
	    pOtion=pOtion.replace("px","")
    }
	l_arr_temp=pOtion.split(";");
    for (i=0;i<l_arr_temp.length;i++) {
    	l_temp=l_arr_temp[i].split(":");
    	l_arr_option[l_temp[0].toLowerCase()]=l_temp[1]
   	}
    if (parseInt(l_arr_option["dialogheight"])>top.document.body.offsetHeight-20) {
    	l_arr_option["dialogheight"]=top.document.body.offsetHeight-20
    }

    var l_obj_argument = new Object();
    l_obj_argument.url = pUrl
    pTitle=".: <?=$_SESSION["application"]?> :.";

    this.overlay=top.document.createElement("div")
	this.overlay.setAttribute("style","background-color:#e6e6e6;opacity:0.5;position:absolute;top:0px;left:0px;width:100%;height:100%;")
    top.document.body.appendChild(this.overlay)

	this.modal_window=top.document.createElement("div")

    lHTML="<table cellpadding='0' cellspacing='0' width='600' bgcolor='#ffffff' style='border: solid 1px;-moz-border-radius:10px'>"
    if (pTitle) {
        lHTML+="<tr>"
        lHTML+="<td align='center' bgcolor='#b5b5b5' style='-moz-border-radius:10px 10px 0 0;border-bottom: solid 1px'>";
        lHTML+="<table cellpadding='0' cellspacing='0' width='100%'><tr><td width='95%' align='center'>"+pTitle+"</td><td><input type='button' id='btnClose' value='X' onclick=\"this.close()\"></td></tr></table>"
        lHTML+="</td>"
        lHTML+="</tr>"
    }
    lHTML+="<tr>"
    lHTML+="<td width='100%' align='center' valign='top' colspan='2'>"
    lHTML+="<iframe src="+l_obj_argument.url+" width='"+l_arr_option["dialogwidth"]+"' height='"+l_arr_option["dialogheight"]+"' scrolling='auto' frameborder='0'></iframe>"
    lHTML+="</td>"
    lHTML+="</tr>"
    lHTML+="</table>"
    this.modal_window.innerHTML=lHTML
    
    lInputs=this.modal_window.getElementsByTagName("input")
    lInputs[0].close=_modalClose
    lInputs[0].modal_window=this
    if (pFunction) lInputs[0].after_close=pFunction
    this.modal_window.setAttribute("style","position:absolute;top:"+_getCenterYiFrame(l_arr_option["dialogheight"])+";left:"+_getCenterXiFrame(l_arr_option["dialogwidth"]))
    top.document.body.appendChild(this.modal_window)

    lWindow=this.modal_window.getElementsByTagName("iframe")[0]
    lWindow.contentWindow.modal_window=this
    lWindow.contentWindow.close=_modalClose
    if (pFunction) lWindow.contentWindow.after_close=pFunction

    if (!top.window.onkeydown) {
        top.window.onkeydown=function (event){
                                if(event.keyCode==9) return false; 
                                else return true;
                              };
   	} else {
        window.onkeydown=function (event){
                                if(event.keyCode==9) return false; 
                                else return true;
                              };
	}
    return lWindow.contentWindow
}

function getObjInputClose(){
	lColectionDiv=top.document.getElementsByTagName("div")
	lInput=lColectionDiv[lColectionDiv.length-1].getElementsByTagName("input")
    return lInput[0];
}

function getRadio(){
	if (document.form1.r_id){
        if(document.form1.r_id.value==undefined){
            x = document.form1.r_id.length-1
            for(i=0; i <= x; i++){
                if(document.form1.p_id.value == document.form1.r_id[i].value)document.form1.r_id[i].checked = true
            }
        }else{
            if(document.form1.p_id.value == document.form1.r_id.value)document.form1.r_id.checked = true
        }
	}
}