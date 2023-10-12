// JavaScript Document
var gHuruf=/[a-zA-Z]/g;
var gAngka=/[0-9]/g;
var gMail=/[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/g;
var gTanggal=/^\d{1,2}\/\d{1,2}\/\d{4}$/g;
var gWaktu=/^\d{2}\.\d{2}$/g;
var gDisc=/^\d{1,2,3}$/g;
//var pattern=/[0-3][0-9]\/(0|1)[0-9]\/(19|20)[0-9]{2}/g;
var gTgl_normal = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var gTgl_leap = new Array(31,29,31,30,31,30,31,31,30,31,30,31);
var gTgl = new Array;

function add_cekError(){
var lAlerttxt="";
var lFocusbox=false;
var lFocuscursor="";
	if ( document.form1.type_add.selectedIndex == 0 ){
		lAlerttxt+="Pilih Type<br>";
		if(lFocuscursor==""){lFocuscursor="document.form1.type_add";}
		}
	if(document.form1.NIK_add.value==""){
		lAlerttxt+='NIK Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.NIK_add";}
		}
	if(document.form1.tgl_pindah.value!=""){
		if(document.form1.tgl_pindah.value.match(gTanggal)==null){
			lAlerttxt+='Tanggal Pindah Salah<br>';
			if(lFocuscursor==""){lFocuscursor="document.form1.tgl_pindah";}
			}
		else {
			var str=document.form1.tgl_pindah.value;
			gTgl=str.split("/");
			if(gTgl[2]%4==0){
				if(gTgl[0]>12){
					lAlerttxt+='Tanggal Pindah Salah Tanggal<br>';
					if(lFocuscursor==""){lFocuscursor="document.form1.tgl_pindah";}
					}
					if(gTgl[1]>gTgl_leap[gTgl[0]-1]){
					lAlerttxt+='Tanggal Pindah Salah Tanggal<br>';
					if(lFocuscursor==""){lFocuscursor="document.form1.tgl_pindah";}
					}
				}
			else{
				if(gTgl[0]>12){
					lAlerttxt+='Tanggal Pindah Salah Tanggal<br>';
					if(lFocuscursor==""){lFocuscursor="document.form1.tgl_pindah";}
					}
					if(gTgl[1]>gTgl_normal[gTgl[0]-1]){
					lAlerttxt+='Tanggal Pindah Salah Tanggal<br>';
					if(lFocuscursor==""){lFocuscursor="document.form1.tgl_pindah";}
					}
				}
			}
		}
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false;
	} else return true;
	lAlerttxt="";
	lFocusbox=false;
	lFocuscursor="";
}

function cekError(){
var lAlerttxt="";
var lFocuscursor="";
	if(document.form1.nm_group_karyawan.value==""){
		lAlerttxt+='Nama Group Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.nm_group_karyawan";}
    }
	if(document.form1.nik.value==""){
		lAlerttxt+='Kode Kepala Group Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.nik";}
    }
	if(document.form1.fk_cabang.value==""){
		lAlerttxt+='Kode Cabang Kosong<br>';
		if(lFocuscursor==""){lFocuscursor="document.form1.fk_cabang";}
    }
	if(lAlerttxt!=""){
		alert("Error : <br>"+lAlerttxt,function(){eval(lFocuscursor+'.focus()')});
		return false;
	} else return true;
	lAlerttxt="";
	lFocuscursor="";
}
