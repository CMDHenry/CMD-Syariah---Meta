function check_type(pType,pObj){
	var huruf = /\d/g
    var float = /\D\.\d{1,2}/g
	var angka = /\D/g
	var time = /^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/g
    var ktp = /\d{2}\.\d{4}\.\d{6}\.\d{4}/g
	var alphanumeric=/^[a-z0-9]+$/i

    switch(pType){
		case 'angka':
            if(pObj.value.match(angka)!=null){
                return true
            }else return false
        break;
		case 'float':
        	//confirm(pObj.value.match(float))
            if(pObj.value.match(float)==pObj.value){
                return true
            }else return false
        break;
        case 'huruf':
            if(pObj.value.match(huruf)!=null){
                return true
            }else return false
        break;
        case 'time':
            if(pObj.value.match(time)==null){
                return true
            }else return false
        break;
		case 'ktp' :
        	if(pObj.value.match(ktp)==null){
            	return true
            }else return false
        break;
		case 'alphaNum' :
        	if(pObj.value.match(alphanumeric)!=pObj.value){
            	return true
            }else return false
        break;
	}
}

function check_date(pObj){
	return check_date_value(pObj.value)
}


function check_date_value(pValue){
    var tgl_normal = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
    var tgl_leap = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
    var tanggal=/^\d{1,2}\/\d{1,2}\/\d{4}$/g;
    var tgl = new Array;
    var flag = false
    
	if(pValue.match(tanggal)==null){
		flag = true
	}else {
		var str=pValue;
		tgl=str.split("/");
		if(tgl[2]%4==0){
			if(tgl[1]>12){
                flag = true
			}
			if(tgl[0]>tgl_leap[tgl[1]-1]){
                flag = true
			}
		}else{
			if(tgl[1]>12){
                flag = true
			}
			if(tgl[0]>tgl_normal[tgl[1]-1]){
                flag = true
			}
		}
	}
    return flag
}