function fFormatNumberKeyDown(ev,pObj,pObjNonFormat){
	lReturn=false
	if ((ev.keyCode>=48 && ev.keyCode<=57) || (ev.keyCode>=96 && ev.keyCode<=105) || ev.keyCode==173) {
		lReturn=true
	} else if ( ev.keyCode==8){
		lStart=pObj.selectionStart
		lEnd=pObj.selectionEnd
		lLength=pObj.value.length

		lRangeBefore=document.selection.createRange()
		lRangeBefore.moveStart('character',-pObj.value.length)
		lArr=lRangeBefore.text.split(',')
		lTextBefore=lArr.join('')
		lRangeAfter=document.selection.createRange()
		lRangeAfter.moveEnd('character',pObj.value.length)
		lArr=lRangeAfter.text.split(',')
		lTextAfter=lArr.join('')
		pObj.value=lTextBefore.substr(0,lTextBefore.length-1)+lTextAfter
		fFormat(pObj,pObjNonFormat)
	} else if (ev.keyCode==188){
		if (pObj.value.lastIndexOf(',')>-1) lReturn=false
		else lReturn=true
    } else if (ev.keyCode==190){
		if (pObj.value.lastIndexOf('.')>-1) lReturn=false
		else lReturn=true
    } else if (ev.keyCode==109) {
    	if (pObj.value.substr(0,1)!="-"){
        	if (pObj.selectionStart==0) lReturn=true
        }
	} else if (ev.keyCode==13 || ev.keyCode==9 || (ev.keyCode>=35 && ev.keyCode<=40) || ev.keyCode==46){
		lReturn=true
	}
	return lReturn;
}

function fFormatNumberKeyUp(ev,pObj,pObjNonFormat){
	lStart=pObj.selectionStart
	lEnd=pObj.selectionEnd
	lLength=pObj.value.length

	lDirection=pObj.selectionDirection
	fFormat(pObj,pObjNonFormat)
	
	if (pObj.value.length>lLength) {
		lStart++;
		lEnd++;
	} else if (pObj.value.length<lLength) {
		lStart--;
		lEnd--;
	}
	pObj.setSelectionRange(lStart,lEnd,lDirection)
}

function fFormat(pObj,pObjNonFormat){
	lAdaMinus=false
	if (pObj.value.substr(0,1)=="-") {
    	lAdaMinus=true
        pObj.value=pObj.value.substr(1,pObj.value.length)
    }
    
	lArr=pObj.value.split(',')
	pObj.value=lArr.join("")
	
	if (pObj.value.lastIndexOf('.')>-1) lIsTitik=true
	else lIsTitik=false    

	if  (pObj.value.length>1){  
        if (pObj.value.substr(0,1)=="0") {
            pObj.value=pObj.value.substr(1,pObj.value.length)
        }
	}
    
	lArrValue=pObj.value.split(".")
	pObj.value="";

	lIndex=3;
	lHasil=""
	if (lArrValue[0].length<3) pObj.value=lArrValue[0]
	else {
		while (lIndex<lArrValue[0].length) {
			lHasil=','+lArrValue[0].substr((lArrValue[0].length-lIndex),3)+lHasil
			lIndex+=3;
		}
		pObj.value=lArrValue[0].substr(0,lArrValue[0].length-(lIndex-3))+lHasil
	}

//	if (pObj.value=="") {
//    	pObj.value=0;
//       	pObjNonFormat.value=0;
//    }
    lArr=pObj.value.split(',')
    pObjNonFormat.value=lArr.join("")
    if (lIsTitik) {
        pObj.value+='.'+lArrValue[1]
        pObjNonFormat.value+='.'+lArrValue[1]
    }
    if (lAdaMinus) {
        pObj.value="-"+pObj.value
        pObjNonFormat.value="-"+pObjNonFormat.value
    }
    if (pObj.value=="-0") {
        pObj.value=0
        pObjNonFormat.value=0
    } else if (pObj.value==",") {
        pObj.value=0
        pObjNonFormat.value=0
    }
//	confirm(pObj.value)
//  confirm(pObjNonFormat.value)
}
