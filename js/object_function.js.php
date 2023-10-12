function alphaNumOnly(e,pType,pUpperCaseOnly){ // onKeyPress="return alphaNumOnly(event,'alphanum')"
    var unicode=e.charCode? e.charCode : e.keyCode
    var flag = 1 //allow
    if (unicode!=8 && unicode!=13 && unicode!=9){ //if the key isn't the backspace,enter,tab key (which we should allow)
    	switch(pType){
        	//alert (pType)
            case 'numeric' :
            	if(unicode < 48 || unicode > 57) flag = 0
            break;
            case 'numeric_comma' :
             if(unicode < 48 || unicode > 57) flag = 0
                if(unicode == 46)flag =1
            break;
            case 'alphabet' :
                if(pUpperCaseOnly == true){
                    if(unicode < 65 || unicode > 90) flag = 0
                }else{
                    if(unicode < 65) flag = 0
                    else if(unicode > 90){
                        if(unicode < 97 || unicode > 122) flag = 0
                    }
                }
            break;
        	case 'alphanum' :
                if(unicode < 48) flag = 0
                else if(unicode > 57){
                    if(pUpperCaseOnly == true){
                        if(unicode < 65 || unicode > 90) flag = 0
                    }else{
                        if(unicode < 65) flag = 0
                        else if(unicode > 90){
                            if(unicode < 97 || unicode > 122) flag = 0
                        }
                    }
                }
            break;
            case 'alphanumkey' :
                if(unicode < 33) flag = 0
                else if(unicode > 57){
                    if(pUpperCaseOnly == true){
                        //if(unicode < 65 || unicode > 90) flag = 0
                    }else{
                        if(unicode < 65) flag = 0
                        else if(unicode > 90){
                            //if(unicode < 97 || unicode > 122) flag = 0
                        }
                    }
                }
            break;
        }
    }
    if(flag==0)return false //disable key press
}

function fNextFocus(ev,j_next,j_function){
	if(j_next){
		if(ev.keyCode==13){
			if(j_next.disabled!=true && j_next.style.visibility!='hidden' && j_next.style.display!='none') {
                if (j_function)j_function();
            	j_next.focus();
            }
		}
		/*if(j_next.disabled==true || j_next.style.visibility=='hidden'){
			alert()
			j_next.fireEvent('onkeyup');
		}*/
	}
}

function fBeforeNextFocus(evt,pObj){
	return true
}

/* function untuk input */
g_keep_old_data_status=2;
g_old_data="";

function fKeepOldData(p_obj){
	if (g_keep_old_data_status==2) {
		g_old_data=p_obj.value
	}
}

function fRestoreOldData(p_obj){
	if (g_keep_old_data_status==0) {
		p_obj.value=g_old_data;
		g_keep_old_data_status=2;
	}
}

function fGetNC(pIsSubmit,pKodeModule,pField,pStatus,pObj,pObjNext,pOption,pFunction,pIdField,pIdDetailField,pOptionName){
	//pOptionName buat where nama kolom db
    //pOption buat where data dari pOptionName
    //confirm(pOptionName)
	lWindow=show_modal('modal_get.php?p_id='+pObj.value+'&'+pField+'='+escape((pOption==undefined || pOption=="")?pObj.value:pOption.value)+'&p_status=edit&module='+pKodeModule+'&id_field='+pIdField+'&id_detail_field='+pIdDetailField+'&option_name='+pOptionName+'&kode='+pField,'dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fGetCustomNC(pIsSubmit,pModule,pField,pStatus,pObj,pObjNext,pOption,pFunction){
//confirm(pOption.innerHTML)
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField+'='+((pOption==undefined || pOption=="")?pObj.value:pOption.value)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fGetCustomNC2Var(pIsSubmit,pModule,pField1,pField2,pStatus,pObj,pObjNext,pOption1,pOption2,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField1+'='+((pOption1==undefined || pOption1=="")?pObj.value:pOption1.value)+'&'+pField2+'='+((pOption2==undefined || pOption2=="")?pObj.value:pOption2.value)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fGetCustomNC3Var(pIsSubmit,pModule,pField1,pField2,pField3,pStatus,pObj,pObjNext,pOption1,pOption2,pOption3,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField1+'='+((pOption1==undefined || pOption1=="")?pObj.value:pOption1.value)+'&'+pField2+'='+((pOption2==undefined || pOption2=="")?pObj.value:pOption2.value)+'&'+pField3+'='+((pOption3==undefined || pOption3=="")?pObj.value:pOption3.value)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}


function fGetCustomNC4Var(pIsSubmit,pModule,pField1,pField2,pField3,pField4,pStatus,pObj,pObjNext,pOption1,pOption2,pOption3,pOption4,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField1+'='+((pOption1==undefined || pOption1=="")?pObj.value:pOption1.value)+'&'+pField2+'='+((pOption2==undefined || pOption2=="")?pObj.value:pOption2.value)+'&'+pField3+'='+((pOption3==undefined || pOption3=="")?pObj.value:pOption3.value)+'&'+pField4+'='+((pOption4==undefined || pOption4=="")?pObj.value:pOption4.value)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fGetCustomNC5Var(pIsSubmit,pModule,pField1,pField2,pField3,pField4,pField5,pStatus,pObj,pObjNext,pOption1,pOption2,pOption3,pOption4,pOption5,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField1+'='+((pOption1==undefined || pOption1=="")?pObj.value:pOption1.value)+'&'+pField2+'='+((pOption2==undefined || pOption2=="")?pObj.value:pOption2.value)+'&'+pField3+'='+((pOption3==undefined || pOption3=="")?pObj.value:pOption3.value)+'&'+pField4+'='+((pOption4==undefined || pOption4=="")?pObj.value:pOption4.value)+'&'+pField5+'='+((pOption5==undefined || pOption5=="")?pObj.value:pOption5.value)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}


function fGetCustomNCInnerHtml(pIsSubmit,pModule,pField,pStatus,pObj,pObjNext,pOption,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField+'='+((pOption==undefined || pOption=="")?pObj.value:pOption.innerHTML)+'&p_status=edit','dialogwidth:800px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fGetNCLB(pIsSubmit,pModule,pField,pStatus,pObj,pObjNext,pOption,pFunction){
	lWindow=show_modal('modal_'+pModule+'_get.php?p_id='+pObj.value+'&'+pField+'='+((pOption==undefined || pOption=="")?pObj.value:pOption.value)+'&p_status=edit','dialogwidth:825px;dialogheight:600px;dialogleft:'+getCenterX(800)+';dialogtop:'+getCenterY(600)+';',function (pValue) {
        if (pIsSubmit){
            if (pObjNext!=null && document.form1.hidden_focus) {
                document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
            }
            if (document.form1.status){
                document.form1.status.value=pStatus;
            }
            document.form1.submit();
        } else {
            pObjNext.focus();
        }
        if (pFunction) pFunction(pValue);
    });
    lWindow.objectReturn=pObj
}

function fChangeText(pStatus,pObjNext){
	document.form1.hidden_focus.value="document.form1."+pObjNext.name+".focus();";
	document.form1.status.value=pStatus
	document.form1.submit();
}


function stripquote(pText){
	if(!pText)return ''
	if(pText.charAt(0)=="\"")return pText.substring(1,pText.length-1)
	else return pText
}

function dateFormat(pDate,param){
	if(!pDate) return ''
	if(param=="" || param==undefined){
        lDate = pDate.substr(0,10)
        lDate = lDate.split('-')
        return lDate[2]+'/'+lDate[1]+'/'+lDate[0]
	}else if(param=="/"){
    	lDate = pDate.split('/')
        return lDate[2]+'/'+lDate[1]+'/'+lDate[0]
   	}
}

function number_format(num,dec,negative){
	num = num.toString().replace(/\$|\,/g,'');
	if(isNaN(num))
	num = "0";
	sign = (num == (num = Math.abs(num)));
	num = Math.floor(num*100+0.50000000001);
	cents = num%100;
	num = Math.floor(num/100).toString();
	if(cents<10)
	cents = "0" + cents;
	for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
	num = num.substring(0,num.length-(4*i+3))+','+
	num.substring(num.length-(4*i+3));
	cents = (dec)? '.' + cents : ""
	//return (((sign)?'Rp ' + num + cents : 'Rp('+ num + cents +')'));
    return (negative)?'-'+(num + cents):(num + cents)
    //return (num + cents)
}

function convert_data(pText){
	var txt = new Array
	var c = 0
	var start = 0
	var end = 0
	var flag = 0
	var temp = ""
	txt[c]=""
    if(pText.charAt(0)=="("){
        var str = pText.substring(1,(pText.length)-1)
    }else{
		var str = pText
	}
	for(i=0;i<=str.length;i++){
		if(str.charAt(i)==','){
			if(str.charAt(i-1)=='\"' && str.charAt(i+1)=='\"'){ // klo char 1 adalah tnda petik
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)!='\"'){
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)=='\"' && str.charAt(i-1)=='\"'){
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)=='\"' && str.charAt(i-1)!='\"'){
				txt[c]+=str.charAt(i)
			}
		}else{
			txt[c]+=str.charAt(i)
		}
	}
	return txt
}

function convert_data_garis(pText){
	var txt = new Array
	var c = 0
	var start = 0
	var end = 0
	var flag = 0
	var temp = ""
	txt[c]=""
    if(pText.charAt(0)=="("){
        var str = pText.substring(1,(pText.length)-1)
    }else{
		var str = pText
	}
	for(i=0;i<=str.length;i++){
		if(str.charAt(i)=='|'){
			if(str.charAt(i-1)=='\"' && str.charAt(i+1)=='\"'){ // klo char 1 adalah tnda petik
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)!='\"'){
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)=='\"' && str.charAt(i-1)=='\"'){
				c++
				txt[c]=""
			}else if(txt[c].charAt(0)=='\"' && str.charAt(i-1)!='\"'){
				txt[c]+=str.charAt(i)
			}
		}else{
			txt[c]+=str.charAt(i)
		}
	}
	return txt
}

function getDateOfNextDay(datestring, separator, nozero,jump)    {  
    if(!separator)    {  
        separator="/";//="yyyy-dd-mm" format  
    }  
    var a_date = datestring.split(separator);  
    var myday = new Date(a_date[1]+'/'+a_date[0]+'/'+a_date[2]);  
    myday.setDate(myday.getDate()+parseInt(jump));  
    var next_day_year = myday.getFullYear();  
    var next_day_month = myday.getMonth()+1;  
    if(!nozero)   {  
        next_day_month = (parseInt(next_day_month)<10)?"0"+next_day_month:next_day_month;  
    }  
    var next_day_day = myday.getDate();  
    next_day_day = (parseInt(next_day_day)<10)?"0"+next_day_day:next_day_day;  
    return next_day_day+"/"+next_day_month+"/"+next_day_year;  
}  

function dateDiffer(a,b,c,d,e){//a start date, b end date, c where to show the result by innerHTML, d show the result by value
	lDate = a.value.split('/')
	startD = lDate[2]+'/'+lDate[1]+'/'+lDate[0]
    lDate = b.value.split('/')
	endD = lDate[2]+'/'+lDate[1]+'/'+lDate[0]

	var start = new Date(startD) //Month is 0-11 in JavaScript
	var end = new Date(endD) //Month is 0-11 in JavaScript
	//Get 1 day in milliseconds
	var one_day=1000*60*60*24
	
	//Calculate difference btw the two dates, and convert to days
	diff = (Math.ceil((start.getTime()-end.getTime())/(one_day)))
	if(b.value!=""){
		if(diff>=0){text = "&nbsp;Hari Lagi ch. 1k"}
		else {diff=diff*(-1); text = "&nbsp;Hari Terlambat"}
	}
	if(c){
    	if(e!=""){
        	c.innerHTML = diff
        }else{
    		c.innerHTML = diff+text
        }
    }
    if(d){
    	if(e!=""){
        	d.value = diff
        }else{
    		d.value = diff+text
        }
    }
	else return diff
}

function timeDiffer(pStart,pEnd){
	start = new Date( pStart )
    end = new Date( pEnd )
	temp = Date.parse(end) - Date.parse(start)
    var day = 1000*60*60*24
    var hour = 1000*60*60
    var min = 1000*60
    var sec = 1000
    result = new Array()
    total_day=0
    total_hour=0
    total_min=0
    total_sec=0
	while(temp >= 1000){
        if(temp >= hour){
            total_hour = temp / hour
            total_hour = Math.floor(total_hour)
            temp = temp - (total_hour * hour)
        }else if(temp >= min){
            total_min = temp / min
            total_min = Math.floor(total_min)
            temp = temp - (total_min * min)
        }else if(temp >= sec){
            total_sec = temp / sec
            total_sec = Math.floor(total_sec)
            temp = temp - (total_sec * sec)
        }
	}
    result[0] = total_hour
    result[1] = total_min
    result[2] = total_sec
    return result
}

function hanyaAngka(e, decimal) {
    var key;
    var keychar;
     if (window.event) {
         key = window.event.keyCode;
     } else
     if (e) {
         key = e.which;
     } else return true;
   
    keychar = String.fromCharCode(key);
    if ((key==null) || (key==0) || (key==8) ||  (key==9) || (key==13) || (key==27) ) {
        return true;
    } else
    if ((("0123456789").indexOf(keychar) > -1)) {
        return true;
    } else
    if (decimal && (keychar == ".")) {
        return true;
    } else return false;
 }
 
 
function fWait(){
 lDivOverlay=document.createElement("div")
 lDivOverlay.setAttribute("id","divAlertOverlay")
 lDivOverlay.style.backgroundColor='#e6e6e6';
    lDivOverlay.style.position='absolute';
    lDivOverlay.style.filter='alpha(opacity=50)'
    lDivOverlay.style.top='0px';
    lDivOverlay.style.left='0px';
    lDivOverlay.style.width='100%';
    lDivOverlay.style.height='100%'
 document.body.appendChild(lDivOverlay)
 lDiv=document.createElement("div")
 lDiv.setAttribute("id","divAlert")
 lHTML="<table cellpadding='0' cellspacing='0'>"
 lHTML+="<tr>"
 lHTML+="<td align='center' style='padding: 5 0 5 0'><img src='images/ajax-loader.gif' border='0'></img><br><strong>Please Wait ...</strong></td>"
 lHTML+="</tr>"
 lHTML+="</table>"
 lDiv.innerHTML=lHTML
 lDiv.style.position='absolute';
 document.body.appendChild(lDiv)
    lDiv.style.top=_getCenterYAlert(lDiv.offsetHeight)
    lDiv.style.left=_getCenterXAlert(16)
}

function getDateToday(){
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    
    var yyyy = today.getFullYear();
    if(dd<10){
        dd='0'+dd;
    } 
    if(mm<10){
        mm='0'+mm;
    } 
    var today = yyyy+'/'+dd+'/'+mm;
    return today
}


function getDateFormat(date){
	return (('0'+date.getDate())).slice(-2) + '/' + ('0'+(date.getMonth()+1)).slice(-2)+ '/' +  date.getFullYear()
}