<?
require '../requires/config.inc.php';
require '../requires/session.inc.php';
require '../requires/compress.inc.php';
require '../requires/referer_check.inc.php';

if (mRefererCheck($__url) && isset($_SESSION['id'])){
?>
	var _TableRowDrag=null;
    var _RegExpEraseNumFormat=/(\,|\.00)/gi
	
	function _fTableCekAjax(pObj,pValue){
		lCell=pObj.parentNode
        //confirm(lCell.parentNode.parentNode.parentNode.id)
		eval("lArrTable=arrField"+lCell.parentNode.parentNode.parentNode.id)
		lColumn=lArrTable[lCell.cellIndex]
            	//alert(lColumn.field_get)
		if (lColumn.table_db!="" && lColumn.field_get!="") {
			lSentText='table='+lColumn.table_db+' '+lColumn.table_db_inner_join+'&field='+lColumn.field_get+'&key='+lColumn.field_key+'&value='+pValue+'&is_date='+lColumn.is_date+'&type='+lColumn.type_get_data+'&field_active='+lColumn.field_active
            //alert(lSentText)
            lObjLoad = getHTTPObject()
			lObjLoad.onreadystatechange=_fTableCekAjaxState
			lObjLoad.targetCell=lCell
			lObjLoad.targetReferer=lColumn.referer
			lObjLoad.open("POST","ajax/get_data.php",true);
			lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
			lObjLoad.setRequestHeader("Content-Length",lSentText.length)
			lObjLoad.setRequestHeader("Connection","close")
			lObjLoad.send(lSentText);
		}
	}
	
	function _fTableCekAjaxState(){
    	//alert(this.responseText);
		if (this.readyState == 4){
			if (this.status==200 && this.responseText!="") {
            
				if (this.responseText=="-") {
					if (this.targetReferer) this.targetCell.parentNode.cells[this.targetReferer].innerHTML=""
					this.targetCell.errorAjax=true;
                    //confirm("aaa");
				} else {
                	//confirm(this.targetReferer);
                    //confirm(this.responseText);
                	lArrResponseText=this.responseText.split("|")
                    if (this.targetReferer){
                        lArrTargetReferer=this.targetReferer.split(",")
                        for (i=0;i<lArrResponseText.length;i++){
                            if (this.targetReferer) this.targetCell.parentNode.cells[lArrTargetReferer[i]].innerHTML=lArrResponseText[i]
                        }
                    }
					this.targetCell.errorAjax=false;
				}
			} else {
            
				if (this.targetReferer) this.targetCell.parentNode.cells[this.targetReferer].innerHTML=""
           		this.targetCell.errorAjax=true;              
			}
		}
	}
	
	function _fTableGetNext(pObj){
		lObjCell=pObj.parentNode
		lObjRow=lObjCell.parentNode
		lCellIndex=lObjCell.cellIndex+1;
		while (lObjCellNext=lObjRow.cells[lCellIndex]){
			for (lChild in lObjCellNext.childNodes){
				if (lObjCellNext.childNodes[lChild].tagName=="INPUT" && lObjCellNext.childNodes[lChild].type!="hidden") {
					return lObjCellNext.childNodes[lChild];
				}
			}
			lCellIndex=lCellIndex+1;
		}
	}
	
	function _fTableGetInput(pObj){
    	//confirm(pObj.tagName)
    	if (pObj.tagName=="TR") {
        	lObjCell=pObj.cells[0];
        } else {
			lObjCell=pObj.parentNode
        }
		for (lChild in lObjCell.childNodes){
			if (lObjCell.childNodes[lChild].tagName=="INPUT" || lObjCellNext.childNodes[lChild].type!="hidden") {
				return lObjCell.childNodes[lChild];
			}
		}
	}
    
    //function _fTableGetInput(pObj){
//		lObjCell=pObj.parentNode
//		for (lChild in lObjCell.childNodes){
//			if (lObjCell.childNodes[lChild].tagName=="INPUT") {
//				return lObjCell.childNodes[lChild];
//			}
//		}
//	}

	function _fTableCreateField(pCell,pColumn,pValue){
    	if (pColumn.is_readonly!='t') {
            switch (pColumn.type) {
                case "numeric":
                    pCell.innerHTML=""
                    pCell.align="right"
                    lObjHidden=document.createElement("input");
                    lObjHidden.setAttribute("type","hidden")
                    lObjHidden.setAttribute("value",pValue.replace(_RegExpEraseNumFormat,""))
                    pCell.appendChild(lObjHidden)
					
                    lObj=document.createElement("input");
                    lObj.setAttribute("type","text")
                    lObj.setAttribute("size",pColumn.size)
                    lObj.setAttribute("class","groove_text_numeric")
                    lObj.setAttribute("maxlength",pColumn.maxlength)
                    lObj.setAttribute("value",pValue)
                    lObj.setAttribute("onkeydown","return fFormatNumberKeyDown(event,this,this.parentNode.children[0])")
                    lObj.setAttribute("onkeyup","fFormatNumberKeyUp(event,this,this.parentNode.children[0])")
                    pCell.appendChild(lObj)
                    break;
                case "text":
                    pCell.innerHTML=""
                    lObj=document.createElement("input");
                    lObj.setAttribute("type",pColumn.type)
                    lObj.setAttribute("size",pColumn.size)
                    lObj.setAttribute("maxlength",pColumn.maxlength)
                    lObj.setAttribute("value",pValue)
                    lObj.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObj)
                    break;
                case "text_area":
                    pCell.innerHTML=""
                    lObj=document.createElement("textarea");
                    lObj.setAttribute("type",pColumn.type)
                    lObj.setAttribute("cols",pColumn.size)
                    lObj.setAttribute("maxlength",pColumn.maxlength)
                    lObj.setAttribute("value",pValue)
                    pCell.setAttribute("align","center")
                    lObj.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    lObj.appendChild(document.createTextNode(pValue))
                    pCell.appendChild(lObj)
                    break;
                case "checkbox":
                case "radio":
                    if (pCell.innerHTML=="") {
                        lObj=document.createElement("input");
                        lObj.setAttribute("name",pColumn.name)
                        lObj.setAttribute("type",pColumn.type)
                        lObj.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                        pCell.appendChild(lObj)
                    } else {
                        pCell.children[0].disabled="";
                    }
                    break;
                case "get":
                    pCell.innerHTML=""
                    lObjInput=document.createElement("input");
                    lObjInput.setAttribute("type",pColumn.type)
                    lObjInput.setAttribute("size",pColumn.size)
                    lObjInput.setAttribute("value",pValue)
                    lObjInput.setAttribute("onchange","_fTableCekAjax(this,this.value)")
                    lObjInput.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObjInput)
                    lObj=document.createElement("text");
                    lObj.innerHTML="&nbsp;"
                    pCell.appendChild(lObj)
                    lObjImg=document.createElement("img");
                    lObjImg.src="images/search.gif"
                    lObjImg.align="absmiddle"	
                    lObjImg.setAttribute("onclick", "fGetNC(false, '"+pColumn.source+"', 'kd_"+pColumn.source+"' ,'', _fTableGetInput(this), _fTableGetNext(this),document.form1.fk_jenis_barang,'','','"+pColumn.id_detail_field+"','fk_jenis_barang');")
                    pCell.appendChild(lObjImg)
                    break;
                case "get_custom":
                    pCell.innerHTML=""
                    lObjInput=document.createElement("input");
                    lObjInput.setAttribute("type",pColumn.type)
                    lObjInput.setAttribute("size",pColumn.size)
                    lObjInput.setAttribute("value",pValue)
                    lObjInput.setAttribute("onchange","_fTableCekAjax(this,this.value)")
                    lObjInput.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObjInput)
                    lObj=document.createElement("text");
                    lObj.innerHTML="&nbsp;"
                    pCell.appendChild(lObj)
                    lObjImg=document.createElement("img");
                    lObjImg.src="images/search.gif"
                    lObjImg.align="absmiddle"	
                    lObjImg.setAttribute("onclick", "fGetCustomNC(false, '"+pColumn.source+"', 'kd_"+pColumn.source+"' ,'', _fTableGetInput(this), _fTableGetNext(this));")
                    pCell.appendChild(lObjImg)
                    break; 
                case "get_custom_param":
                	pRow=pCell.parentNode;
                    pObjCell=pRow.cells[1];                    
                	//confirm(pObjCell.innerHTML+'aaa')
                    pCell.innerHTML=""
                    lObjInput=document.createElement("input");
                    lObjInput.setAttribute("type",pColumn.type)
                    lObjInput.setAttribute("size",pColumn.size)
                    lObjInput.setAttribute("value",pValue)
                    lObjInput.setAttribute("onchange","_fTableCekAjax(this,this.value)")
                    lObjInput.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObjInput)
                    lObj=document.createElement("text");
                    lObj.innerHTML="&nbsp;"
                    pCell.appendChild(lObj)
                    lObjImg=document.createElement("img");
                    lObjImg.src="images/search.gif"
                    lObjImg.align="absmiddle"	
                    lObjImg.setAttribute("onclick", "fGetCustomNCInnerHtml(false, '"+pColumn.source+"', '"+pColumn.param+"' ,'', _fTableGetInput(this), _fTableGetNext(this),pObjCell);")
                    pCell.appendChild(lObjImg)
                    break;        
                case "list_manual":
                    pCell.innerHTML=""
                    lObjList=document.createElement("select");
                    lObjList.setAttribute("class","groove_text")
                    pCell.appendChild(lObjList)
                    lArrValue=pColumn.field_key.split(",")
                    lArrText=pColumn.field_get.split(",")
                    lObjOption=document.createElement("option");
                    lObjOption.text="-- Pilih --"
                    lObjList.appendChild(lObjOption)
                    for(lIndex=0;lIndex<lArrValue.length;lIndex++){
                        lObjOption=document.createElement("option");
                        lObjOption.value=lArrValue[lIndex]
                        lObjOption.text=lArrText[lIndex]
                        //alert(lArrValue[lIndex]+"=="+pValue)
                        if (lArrValue[lIndex]==pValue) lObjOption.selected=true;
                        lObjList.appendChild(lObjOption)
                    }
                    break;
                 
                case "list_db":
				pCell.innerHTML=""
				lObjList=document.createElement("select");
                pCell.setAttribute("align","center")
				pCell.appendChild(lObjList)
				lArrValue=pColumn.field_key.split(",")
				lArrText=pColumn.field_get.split(",")
				lObjOption=document.createElement("option");
				lObjOption.text="-- Pilih --"
				lObjList.appendChild(lObjOption)
				for(lIndex=0;lIndex<lArrValue.length-1;lIndex++){
					lObjOption=document.createElement("option");
					lObjOption.value=lArrValue[lIndex]
					lObjOption.text=lArrText[lIndex]
					//alert(lArrValue[lIndex]+"=="+pValue)
					if (lArrValue[lIndex]==pValue) lObjOption.selected=true;
					lObjList.appendChild(lObjOption)
				}
				break;    
                    
                case "date":
                    pCell.innerHTML=""
                    lObjInput=document.createElement("input");
                    lObjInput.setAttribute("type",pColumn.type)
                    lObjInput.setAttribute("size",pColumn.size)
                    lObjInput.setAttribute("value",pValue)
                    lObjInput.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObjInput)
                    lObj=document.createElement("text");
                    lObj.innerHTML="&nbsp;"
                    pCell.appendChild(lObj)
                    lObjImg=document.createElement("img");
                    lObjImg.src="images/btn_extend.gif"
                    lObjImg.align="absmiddle"
                    lObjImg.setAttribute("onclick", "fPopCalendar(_fTableGetInput(this))")
                    pCell.appendChild(lObjImg)
                    break; 
                    
                 case "file" :
                 	pCell.innerHTML=""
                    lObj=document.createElement("input");
                    lObj.setAttribute("type", "file");
                    lObj.setAttribute("value",pValue)
                    lObj.setAttribute("onkeyup","fNextFocus(event,_fTableGetNext(this))")
                    pCell.appendChild(lObj)
                    break;
            }
        }
	}
	
	function _fTableEdit(){
		lTable=this.parentNode.parentNode.parentNode.parentNode
		lRow=this.parentNode.parentNode
		lRow.isEdit=true
		eval("lArrTable=arrField"+lTable.id)
		for (var lIndex=0;lIndex<lRow.cells.length-1;lIndex++){
			lCell=lRow.cells[lIndex]
			_fTableCreateField(lCell,lArrTable[lIndex],lCell.innerHTML)
			lInput=lCell.children[0]
//			if (lArrTable[lIndex].type=="get") _fTableCekAjax(lInput,lInput.value)
//			lCell.setAttribute("align","center")
		}
		lCell=lRow.cells[lRow.cells.length-1]
		lCell.innerHTML=""
		lObj=document.createElement("input");
		lObj.setAttribute("type","button")
		lObj.setAttribute("value","Add")
		lObj.onclick=_fTableAddEdit;
		lCell.appendChild(lObj)
        _fTableGetInput(lRow).focus()
	}
	
	function _fTableWriteValue(pCell,pCellData){
		lTable=pCell.parentNode.parentNode.parentNode
		pCell.align="left"
		eval("lArrTable=arrField"+lTable.id)
		if (lArrTable[pCell.cellIndex].type=="readonly"){
              if (pCell!=pCellData) {
                
                    if (lArrTable[pCell.cellIndex].is_numeric=="t"){
                        pCell.innerHTML= number_format(pCellData.innerHTML,2)          
                        pCell.align="right"
                    }
                    else pCell.innerHTML=pCellData.innerHTML
                    //pCell.innerHTML=pCellData.innerHTML
                    pCellData.innerHTML=""
                }
		} else {
			lInput=pCellData.children[0]
			if (lInput) {
				switch (lArrTable[pCellData.cellIndex].type) {
                	case "list_db":
						pCell.innerHTML=lInput.options[lInput.selectedIndex].value
						pCell.setAttribute("align","center")
                        lInput.selectedIndex=0
						break;
                	case "numeric":
                    	var val = lInput.value
                    
                        // kalo ada yang iseng masukin selain angka
                        if(isNaN(val)){
                            val = 0
                            pCell.innerHTML=val
                        }else{
                            if(val < 0)flag = true
                            else flag = false
                            pCell.innerHTML=number_format(val,true,flag);                   
                        }
    
                        pCell.align="right"
                        lInput.value="";
                        lInput2=pCellData.children[1];
                        if(lInput2)lInput2.value="";
                        break;
					case "list_manual":
						pCell.innerHTML=lInput.options[lInput.selectedIndex].value
						lInput.selectedIndex=0
						break;
					case "checkbox":
					case "radio":
						if (pCell!=pCellData){
							pCell.innerHTML=pCellData.innerHTML
							pCell.children[0].disabled="disabled"
							pCell.children[0].checked=lInput.checked
							lInput.checked=false
						} else {
							lInput.disabled="disabled"
						}
						pCell.align="center"
						break;
					default:
						pCell.innerHTML=lInput.value;
						lInput.value="";
						break;
				}
			}
		}
        if(pCell.cellIndex=="6")grandTotal(pCell)
	}
	
	function _fTableCekError(pRow,pTable){
		var lAlerttxt="";
		var lFocusbox=false;
		var lFocuscursor="";

		eval("lArrTable=arrField"+pTable.id)
		for (var lIndex=0;lIndex<pRow.cells.length-1;lIndex++){
			lInput=pRow.cells[lIndex].children[0]
			if (lArrTable[lIndex].is_required=="t") {
				if (lInput) {
					if (lInput.value=="") {
						lAlerttxt+=lArrTable[lIndex].caption+' Kosong<br>';
						if(lFocusbox==false){lFocuscursor=lInput;lFocusbox=true;}
					}else if (lArrTable[lIndex].OtherErrorCheck) {
						if (lMsg=lArrTable[lIndex].OtherErrorCheck(lInput)) {
							lAlerttxt+=lArrTable[lIndex].caption+' '+lMsg+'<br>';
							if(lFocusbox==false){lFocuscursor=lInput;lFocusbox=true;}
						}
					}
				}
			} else {
				if (lInput) {
					if (lArrTable[lIndex].OtherErrorCheck) {
						if (lMsg=lArrTable[lIndex].OtherErrorCheck(lInput)) {
							lAlerttxt+=lArrTable[lIndex].caption+' '+lMsg+'<br>';
							if(lFocusbox==false){lFocuscursor=lInput;lFocusbox=true;}
						}
					}
				}
			}
//            if(lArrTable[lIndex].type == 'numeric'){
//            	confirm(check_type('angka',lInput))
//            	if(check_type('angka',lInput)){
//                    lAlerttxt+=lArrTable[lIndex].caption+' '+lMsg+'<br>';
//                    if(lFocusbox==false){lFocuscursor=lInput;lFocusbox=true;}
//                }
//			}
			if (pRow.cells[lIndex].errorAjax && lInput.value!="") {
				lAlerttxt+=lArrTable[lIndex].caption+' Salah<br>';
				if(lFocusbox==false){lFocuscursor=lInput;lFocusbox=true;}						
			}			
		}
		if(lAlerttxt!=""){
			alert("Error : <br>"+lAlerttxt,function (){lFocuscursor.focus()});
			return false
		} else return true;
	}

	function _fTableAddNew(){
    	lTable=this.parentNode.parentNode.parentNode.parentNode
		lRowData=this.parentNode.parentNode
        //confirm (lRowData)
        
        _fTableGetInput(lRowData).focus()
		if (_fTableCekError(lRowData,lTable)){
			lTable=this.parentNode.parentNode.parentNode.parentNode
			lRow=lTable.insertRow(lTable.rows.length-1)
			lRow.onmousedown=_fTableRowMouseDown
			lRow.onmouseover=_fTableRowMouseOver
			lRow.setAttribute("bgcolor","#e0e0e0")
			for (var lIndex=0;lIndex<lRowData.cells.length-1;lIndex++){
				lCell=lRow.insertCell(-1);
				lCell.style["padding"]="0 5 0 5";
				lCellData=lRowData.cells[lIndex]
                _fTableWriteValue(lCell,lCellData)
			}
			lCell=lRow.insertCell(-1);
			lCell.setAttribute("width","100")
			lCell.setAttribute("align","center")
			lObj=document.createElement("input");
			lObj.setAttribute("type","button")
			lObj.setAttribute("value","Edit")
			lObj.onclick=_fTableEdit;
			lCell.innerHTML+="&nbsp;"
			lCell.appendChild(lObj)
			lObj=document.createElement("input");
			lObj.setAttribute("type","button")
			lObj.setAttribute("value","Del")
			lObj.onclick=_fTableDel
			lCell.appendChild(lObj)
		}
	}
	
	function _fTableAddEdit(){
		lTable=this.parentNode.parentNode.parentNode.parentNode
		lRow=this.parentNode.parentNode
        lLastRow=lTable.rows[lTable.rows.length-1];
        //_fTableGetInput(lLastRow).focus()
		if (_fTableCekError(lRow,lTable)){
			for (var lIndex=0;lIndex<(lRow.cells.length-1);lIndex++){
				lCell=lRow.cells[lIndex]
				lInputs=lCell.children
				_fTableWriteValue(lCell,lCell)
			}
			lRow.isEdit=false;
			lCell=lRow.cells[lRow.cells.length-1]
			lCell.innerHTML=""
			lCell.setAttribute("width","100")
			lCell.setAttribute("align","center")
			lObj=document.createElement("input");
			lObj.setAttribute("type","button")
			lObj.setAttribute("value","Edit")
			lObj.onclick=_fTableEdit;
			lCell.innerHTML+="&nbsp;"
			lCell.appendChild(lObj)
			lObj=document.createElement("input");
			lObj.setAttribute("type","button")
			lObj.setAttribute("value","Del")
			lObj.onclick=_fTableDel
			lCell.appendChild(lObj)
		}
	}
	
	function _fTableDel(){
		if (confirm('Apakah Anda Yakin ?')){
			lTable=this.parentNode.parentNode.parentNode
			lTable.removeChild(this.parentNode.parentNode)
            grandTotal(lTable,1)
		}
	}

	function _fTableRowMouseDown(ev){
		if (ev.target.tagName=="TD"){
			this.style.MozUserSelect="none"
			this.style.opacity=0.5;
			this.style.MozBoxShadow="inset #182b4f 0px 2px 4px"
			_TableRowDrag=this;
			//alert(_TableRowDrag)
		}
	}

	function _fTableRowMouseOver(ev){
		if (_TableRowDrag) {
			_TableRowDrag.parentNode.insertBefore(_TableRowDrag,this)
		}
	}

	function _fTableMouseUp(ev){
		if (_TableRowDrag) {
			_TableRowDrag.style.opacity=1;
			_TableRowDrag.style.MozBoxShadow="none"
			_TableRowDrag=null;
		}
	}

	function table (){
		this.init = function (pHeaderTitle,pName,pColumns,pRoot,pOptions){
        	//if (!pObjParent) pObjParent=document
        	this.headerTitle=pHeaderTitle;
			this.name=pName;
			this.columns=pColumns;
            this.columnsLength=0
			for(column in pColumns) {
            	this.columnsLength++;
           	}
			this.root=pRoot;
			this.table=document.createElement("table")
			this.table.setAttribute("id",pName)
			for(option in pOptions) {
				this.table.setAttribute(option,pOptions[option])
			}
			this.root.appendChild(this.table)
            this.headerTitleColumn();
			this.title();
			this.addRow();
		}

		this.headerTitleColumn=function(){
			lRow=this.table.insertRow(-1);
			lRow.setAttribute("bgcolor","#c8c8c8")
            lCell=lRow.insertCell(-1);
            lCell.setAttribute("align","center")
            lCell.setAttribute("colspan",(this.columnsLength+1))
            lCell.style["padding"]="0 5 0 5"
            lCell.innerHTML=this.headerTitle;
		}
		
		this.title=function(){
			lRow=this.table.insertRow(-1);
			lRow.setAttribute("bgcolor","#c8c8c8")
			for(column in this.columns){
				lCell=lRow.insertCell(-1);
				lCell.setAttribute("align","center")
				lCell.style["padding"]="0 5 0 5"
				lCell.innerHTML=this.columns[column]["caption"];
			}
			lCell=lRow.insertCell(-1);
		}
		
		this.addRow=function(){
			lRow=this.table.insertRow(-1);
			lRow.setAttribute("bgcolor","#e0e0e0")
			lRow.onmouseover=_fTableRowMouseOver
			for(column in this.columns){
				lCell=lRow.insertCell(-1);
				_fTableCreateField(lCell,this.columns[column],"")
				lCell.setAttribute("align","center")
			}
			lCell=lRow.insertCell(-1);
			lCell.setAttribute("width","100")
			lCell.setAttribute("align","center")
			lObj=document.createElement("input");
			lObj.setAttribute("type","button")
			lObj.setAttribute("value","Add")
			lObj.onclick=_fTableAddNew;
			lCell.appendChild(lObj)
		}
		
		this.newColumn=function(){
			var lObj=document.createElement("td")
			return lObj
		}
		
		this.getIsi=function(){
        	var lAlerttxt="";
			lArrIsi=Array();
            
            for (lIndexRow=2;lIndexRow<this.table.rows.length-1;lIndexRow++){
            	temp=lIndexRow-1
            	if (this.table.rows[lIndexRow].isEdit) lAlerttxt="Table baris ke "+temp+" masih dalam posisi edit.<br>"
			}
			if (lAlerttxt=="") {
				lStrIsi="";
				for (lIndexRow=2;lIndexRow<this.table.rows.length-1;lIndexRow++){
					lStrRow="";
					for (lIndexCell=0;lIndexCell<this.table.rows[lIndexRow].cells.length-1;lIndexCell++){
						if (this.columns[lIndexCell].type=="checkbox" || this.columns[lIndexCell].type=="radio" ){
							lInput=this.table.rows[lIndexRow].cells[lIndexCell].children[0]
							lStrRow+=((lInput.checked)?'t':'f')+"»"
                        } else if(this.columns[lIndexCell].type=="numeric") {
							lStrRow+=this.table.rows[lIndexRow].cells[lIndexCell].innerHTML.replace(_RegExpEraseNumFormat,"").replace(',','.')
                            lStrRow+="»"
						} else if(this.columns[lIndexCell].type == 'date'){
                        	ltmp = this.table.rows[lIndexRow].cells[lIndexCell].innerHTML
                            if(ltmp.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)){
                                ltemp = ltmp.split('/')
                                lDate = ltemp[1]+"/"+ltemp[0]+"/"+ltemp[2]
                                lStrRow+=lDate+"»"
                            }else{
                            	lStrRow+="»"
                            }
                        } else {
							lStrRow+=this.table.rows[lIndexRow].cells[lIndexCell].innerHTML+"»"
						}
					}
					lStrIsi+=lStrRow.substring(0,lStrRow.length-1)+"¿"
				}
				return lStrIsi
			} else {
				alert("Error : <br>"+lAlerttxt);
				return false
			}
		}
		this.setIsi=function(pIsi){
			lRowData=pIsi.split("¿")
            //confirm(lRowData.length);
			for (lIndex=0;lIndex<(lRowData.length-1);lIndex++){
				lRow=this.table.insertRow(this.table.rows.length-1)
				lRow.onmousedown=_fTableRowMouseDown
				lRow.onmouseover=_fTableRowMouseOver
				lRow.setAttribute("bgcolor","#e0e0e0")
				lCellData=lRowData[lIndex].split("»")
				for (var lIndex2=0;lIndex2<lCellData.length;lIndex2++){
					lCell=lRow.insertCell(-1);
					lCell.style["padding"]="0 5 0 5";
					if (this.columns[lIndex2].type=="checkbox") {
						lCell.innerHTML="<input type='checkbox' name='"+this.columns[lIndex2].name+"' "+lCellData[lIndex2]+" disabled='disabled'>"
						lCell.align="center"
					} else if (this.columns[lIndex2].type=="numeric") {
                    	if( lCellData[lIndex2] < 0)flag = true
                        else flag = false
						lCell.innerHTML=number_format(lCellData[lIndex2],true,flag)
						lCell.align="right"
					} else if (this.columns[lIndex2].is_numeric=="t") {
                    	if( lCellData[lIndex2] < 0)flag = true
                        else flag = false
						lCell.innerHTML=number_format(lCellData[lIndex2],true,flag)
						lCell.align="right"
					} else if (this.columns[lIndex2].type=="radio") {
						lCell.innerHTML="<input type='radio' name='"+this.columns[lIndex2].name+"' disabled='disabled' "+lCellData[lIndex2]+">"
						lCell.align="center"
					} else if(this.columns[lIndex2].type == 'date'){
                        ltmp = lCellData[lIndex2]
                        if(ltmp.match(/^\d{1,2}\/\d{1,2}\/\d{4}$/)){
                            ltemp = ltmp.split('/')
                            lDate = ltemp[1]+"/"+ltemp[0]+"/"+ltemp[2]
                            lCell.innerHTML=lDate
                        }else{
                            lCell.innerHTML=lCellData[lIndex2]
                        }
                    } else {
						lCell.innerHTML=lCellData[lIndex2]
					}
				}
				lCell=lRow.insertCell(-1);
				lCell.setAttribute("width","100")
				lCell.setAttribute("align","center")
				lObj=document.createElement("input");
				lObj.setAttribute("type","button")
				lObj.setAttribute("value","Edit")
				lObj.onclick=_fTableEdit;
				lCell.innerHTML+="&nbsp;"
				lCell.appendChild(lObj)
				lObj=document.createElement("input");
				lObj.setAttribute("type","button")
				lObj.setAttribute("value","Del")
				lObj.onclick=_fTableDel
				lCell.appendChild(lObj)
			}
		}
		this.clearIsi=function(){
			lRows=this.table.rows
			for (lIndex=lRows.length-3;lIndex>0;lIndex--) {
				this.table.deleteRow(2)
			}
		}
	}
	document.addEventListener("mouseup",_fTableMouseUp,false)
    
    function grandTotal(pObj,pParam){
    	if(pParam==null || pParam==undefined)lTable=pObj.parentNode.parentNode
        else if(pParam=='1')lTable=pObj
        total=0
        jumlah_barang=0
        for (x=2;(x<lTable.rows.length-1);x++){
            total += parseFloat(lTable.rows[x].cells[6].innerHTML.replace(_RegExpEraseNumFormat,"").replace(",","."))
            jumlah_barang += 1
        }
        if(parseFloat(total) < 0)flag = true
        else flag = false
        
        document.getElementById("divtotal_taksir").innerHTML=number_format(total,true,flag)
        document.form1.total_taksir.value=total
        
        document.getElementById("divjumlah_barang").innerHTML=number_format(jumlah_barang,true,flag)
        document.form1.jumlah_barang.value=jumlah_barang
         fGetPenaksirData()
    }
    function fCalc(){

	}
<?
}
?>