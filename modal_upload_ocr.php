<?php
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/general.inc.php';
require 'requires/numeric.inc.php';
require 'requires/validate.inc.php';
require 'requires/timestamp.inc.php';
require 'requires/input.inc.php';
require 'requires/cek_error.inc.php';
require 'requires/module.inc.php';
require 'requires/db_utility.inc.php';
require 'classes/select.class.php';
require 'requires/file.inc.php';

//echo $upload_path;
//echo $_SERVER['HTTP_HOST'];
//echo $_SERVER['DOCUMENT_ROOT'];
$id_edit = $_REQUEST['id_edit'];
$id_menu=trim($_REQUEST["id_menu"]);
$kd_menu_button=trim($_REQUEST["kd_menu_button"]);
get_data_menu($id_menu);
$lrs=pg_query("select * from skeleton.tblmenu where kd_menu='".$kd_menu_button."'");	
$lrow=pg_fetch_array($lrs);
$nama_button=$lrow["nama_menu"];
?>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" title="Style CSS" href="js/cwcalendar.css.php" type="text/css" media="all" />
</head>
</html>
<html>
<body>
<!--<body onLoad="fLoad()"  bgcolor="#fafafa">-->
<table cellpadding="0" cellspacing="0" border="0" width="100%" align="center">
<!--<form action="<?=$_SERVER['PHP_SELF']?>" method="post" name="form1" enctype="multipart/form-data">-->
<input type="hidden" name="status" id="status" value="<?=$status?>">
<input type="hidden" name="id_menu" value="<?=$id_menu?>">
	<tr>
		<td class="border">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" bgcolor="#D0E4FF">
				<tr><td class="judul_menu" align="center"><?=strtoupper($nm_menu)?></td></tr>
			</table>		
		</td>
	</tr>
    <tr>
		<td class="border">
<!-- content begin -->
            <table cellpadding="0" cellspacing="1" border="0" width="100%">
                <tr style="padding:0 5 0 5" bgcolor='#efefef'>
					<td align="center"><video id="video"  width="540" height="480" autoplay></video></td>
                    <td width="20%" ><canvas id="canvas"></canvas>
                   
                    <input type="button" class="groove_button" name="btnsimpan" value="Take Picture" onClick="takePicture()">
                    <input type="button" class="groove_button" name="btnsimpan1" value="Save Picture" onClick="savePicture()">
                    <br>                  
                    <? if($nama_button=='Upload OCR'){?>
<!--                    	DATA<br>
                        <span id="dataNasabah"></span>     
-->                     <? }?> 
					 </td> 
				</tr>
                <tr>
                	<td colspan="2" height="25" align="center" bgcolor="#D0E4FF" class="border"></td>
                </tr>
                <!-- <tr style="padding:0 5 0 5" bgcolor='#efefef'>
                    <td><canvas id="canvas" width="640" height="480"></canvas></td> 
                 </tr>-->
                 <!--<video id="video" width="640" height="480" autoplay></video>
                 <button id="snap">Snap Photo</button>
                 <canvas id="canvas" width="640" height="480"></canvas>-->
            </table>
<!-- end content begin -->
          </td>
    </tr>
<!--</form>-->
</table>
</body>
</html>

<script language='javascript' src='js/alert.js.php'></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/openwindow.js.php"></script>
<script language='javascript' src='js/object_function.js.php'></script>
<script language='javascript' src="js/input_format_number.js.php"></script>
<script language='javascript' src="js/tab.js.php?kd_module=20140400000039&id_menu=20140200000411"></script>
<script language='javascript'>

// Grab elements, create settings, etc.
var video = document.getElementById('video');

// Get access to the camera!
if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
	// Not adding `{ audio: true }` since we only want video now
	navigator.mediaDevices.getUserMedia({ video: {
    facingMode: 'environment'
  } }).then(function(stream) {
		video.srcObject = stream;
		//video.facing = environment;
		//var constraints = {
		//  facingMode: { exact: "environment" }
		//};
		//video.facingMode =constraints;
		video.play();
	});
}

/* Legacy code below: getUserMedia 
else if(navigator.getUserMedia) { // Standard
    navigator.getUserMedia({ video: true }, function(stream) {
        video.src = stream;
        video.play();
    }, errBack);
} else if(navigator.webkitGetUserMedia) { // WebKit-prefixed
    navigator.webkitGetUserMedia({ video: true }, function(stream){
        video.src = window.webkitURL.createObjectURL(stream);
        video.play();
    }, errBack);
} else if(navigator.mozGetUserMedia) { // Mozilla-prefixed
    navigator.mozGetUserMedia({ video: true }, function(stream){
        video.src = window.URL.createObjectURL(stream);
        video.play();
    }, errBack);
}
*/

// Elements for taking the snapshot
var canvas = document.getElementById('canvas');
var context = canvas.getContext('2d');
var video = document.getElementById('video');

// Trigger photo take
//document.getElementById("snap").addEventListener("click", function() {
//	context.drawImage(video, 0, 0, 220, 150);
//});
function takePicture(){
	
	context.drawImage(video, 0, 0, 220, 150);
	document.getElementById('status').value='OK'
}
function savePicture(){
	//document.getElementById('dataNasabah').innerHTML=''
	if(document.getElementById('status').value=='OK'){
		var r=confirm("Apakah Anda Yakin Akan Menyimpan Foto Ini?")		
		if(r==true){
			saveImage();
		}
		var lAlerttxt="";
		lAlerttxt+='Simpan Foto Berhasil<br>';
		//alert(""+lAlerttxt);
		//alert(lAlerttxt,function(){lInputClose=getObjInputClose();lInputClose.close()});
	}else{
		var lAlerttxt="";
		lAlerttxt+='Ambil Foto Terlebih Dahulu<br>';
		alert("Error : <br>"+lAlerttxt);
	}
}

function saveImage() {
	var canvasData = canvas.toDataURL("image/png");
	//confirm(canvas)
	//var ajax = new XMLHttpRequest();
	lObjLoad = getHTTPObject()
	lObjLoad.onreadystatechange=fSaveImageState
	//ajax.onreadystatechange = function() {
	//console.log(ajax.responseText);
	//}
	lSentText="img=" + canvasData ;		
	
	lObjLoad.open("POST","ajax/saveimage_ocr.php",true);
	lObjLoad.setRequestHeader("Content-Type", "application/upload");
	//lObjLoad.setRequestHeader("Content-Type","application/x-www-form-urlencoded")
	lObjLoad.setRequestHeader("Content-Length",lSentText.length)
	lObjLoad.setRequestHeader("Connection","close")
	lObjLoad.send(lSentText);
	//confirm(lSentText)
}
function fSaveImageState(){	
	if (this.readyState == 4){
		//confirm(this.responseText)
		if (this.status==200 && this.responseText!="") {
			str=this.responseText;
			if(str.indexOf("Gagal") >= 0) {				
   				alert("Error : <br>"+str);
			}else{
				//document.getElementById('dataNasabah').innerHTML=str
				var data=str;
				//confirm(data)
				lAlerttxt='Data Berhasil di-proses<br>';
				//,function(){lInputClose=getObjInputClose();lInputClose.close()}
				alert(""+lAlerttxt,function(){window.location='modal_add.php?id_menu=20170800000001&kd_menu_button=10101010'+encodeURI(data)});
				
			}
		} else {
		}
	}
}

</script>

<?

?>

