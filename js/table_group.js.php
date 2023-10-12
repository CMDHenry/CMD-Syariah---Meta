	function _fTableCekAjax(pObj,pValue){
		lCell=pObj.parentNode
		eval("lArrTable=arrField"+lCell.parentNode.parentNode.parentNode.id)
		lColumn=lArrTable[lCell.cellIndex]
		if (lColumn.table_db!="" && lColumn.field_get!="") {
        	if(lCell.cellIndex == 0){
            	lTableDB = "(select nik,nm_karyawan,nm_jabatan from tblkaryawan_dealer left join tbljabatan on fk_jabatan = kd_jabatan)as tbldata"
                lFieldGet = "(nm_karyawan,nm_jabatan)";
            }
			lSentText='table='+lTableDB+'&field='+lFieldGet+'&key='+lColumn.field_key+'&value='+pValue+'&is_date='+lColumn.is_date
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
		if (this.readyState == 4){
			if (this.status==200 && this.responseText!="") {
				if (this.responseText=="-") {
					if (this.targetReferer) this.targetCell.parentNode.cells[this.targetReferer].innerHTML=""
					this.targetCell.errorAjax=true;
				} else {
					if (this.targetReferer){
                    	lRow = this.targetCell.parentNode
                        lArr = convert_data(this.responseText)
	                    lRow.cells[1].innerHTML = stripquote(lArr[0])
	                    lRow.cells[2].innerHTML = stripquote(lArr[1])
                    }
					this.targetCell.errorAjax=false;
				}
			} else {
				if (this.targetReferer) this.targetCell.parentNode.cells[this.targetReferer].innerHTML=""
				 this.targetCell.errorAjax=false;
			}
		}
	}

	function _fTableCreateField(pCell,pColumn,pValue){
		switch (pColumn.type) {
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
			case "checkbox":
				if (pCell.innerHTML=="") {
					lObj=document.createElement("input");
					lObj.setAttribute("checked","checked")
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
				lObjImg.setAttribute("onclick", "fGetNC(false, '"+pColumn.source+"', 'kd_"+pColumn.source+"' ,'', _fTableGetInput(this), _fTableGetNext(this));_fTableCekAjax(this,this.parentNode.children[0].value)")
				pCell.appendChild(lObjImg)
				break;
			case "get_other":
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
				lObjImg.setAttribute("onclick", "_fGetOther('kd_"+pColumn.source+"' , _fTableGetInput(this), _fTableGetNext(this))")
				pCell.appendChild(lObjImg)
				break;
			case "list_manual":
				pCell.innerHTML=""
				lObjList=document.createElement("select");
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
					if (lArrValue[lIndex]==pValue) lObjOption.selected=true;					
					lObjList.appendChild(lObjOption)
				}
				break;
			case "date":
				pCell.innerHTML=""
				lObjInput=document.createElement("input");
				lObjInput.setAttribute("type","text")
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
//			if (lArrTable[lIndex].type=="get" || lArrTable[lIndex].type=="get_other") _fTableCekAjax(lInput,lInput.value)
//			lCell.setAttribute("align","center")
		}
		lCell=lRow.cells[lRow.cells.length-1]
		lCell.innerHTML=""
		lObj=document.createElement("input");
		lObj.setAttribute("type","button")
		lObj.setAttribute("value","Add")
		lObj.onclick=_fTableAddEdit;
		lCell.appendChild(lObj)
	}

	function table (){
    	this.lArrFieldTable = new Array()

		this.init = function (pName,pColumns,pRoot,pOptions){
			this.name=pName;
			this.columns=pColumns;
			this.root=pRoot;
			this.table=document.createElement("table")
			this.table.setAttribute("id",pName)
			for(option in pOptions) {
				this.table.setAttribute(option,pOptions[option])
			}
			this.root.appendChild(this.table)
			this.title();
			this.addRow();
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
			for (lIndexRow=1;lIndexRow<this.table.rows.length-1;lIndexRow++){
				if (this.table.rows[lIndexRow].isEdit) lAlerttxt+=this.name.replace("_"," ")+" baris ke "+lIndexRow+" masih dalam posisi edit.<br>"
			}
			if (lAlerttxt=="") {
				lStrIsi="";
				for (lIndexRow=1;lIndexRow<this.table.rows.length-1;lIndexRow++){
					lStrRow="";
					for (lIndexCell=0;lIndexCell<this.table.rows[lIndexRow].cells.length-1;lIndexCell++){
						if (this.columns[lIndexCell].type=="checkbox" || this.columns[lIndexCell].type=="radio" ){
							lInput=this.table.rows[lIndexRow].cells[lIndexCell].children[0]
							lStrRow+=((lInput.checked)?"checked":"")+"»"
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
                    }else{
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
			for (lIndex=lRows.length-2;lIndex>0;lIndex--) {
				this.table.deleteRow(1)
			}
		}
	}
	document.addEventListener("mouseup",_fTableMouseUp,false)
