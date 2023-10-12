<?
require 'requires/config.inc.php';
require 'requires/authorization.inc.php';
require 'requires/compress.inc.php';
//style="background-color:#C8C8C8;width:30%;height:20px;font-size:16px;font:Tahoma, Geneva, sans-serif;color:#C00"
$parent_root=$_REQUEST["parent_root"];
?>
<style>

.todolist {
    background-color: tomato;
    padding: 5px;
	width:40%;
} 
.link {
    color: white;	
	font-size:16px;
	font-weight:400;
	
}

#flowchart td:not(:empty){
	font-size:12px;
	letter-spacing:1px;
	/*line-height:14px;*/
	/*border:solid 1px;*/
	width:120px;
	height:55px;
	text-align:center;
	padding:5px;
	cursor:pointer;
	background-color:#CCC;
}

#flowchart td:hover:not(:empty){
	background-color:#AAA;
}

#flowchart td table tr td:not(:empty){
	background-color:#CCC;
	border:none;
	height:0px;
}

#flowchart td table tr td:hover:not(:empty){
	background-color:#EEE;
}

#flowchart td.no-access:hover:not(:empty){
	background-color:#FF5333;
	cursor:default;
}

#flowchart td div {
	position:relative;
}

#flowchart td div div.bawah{ /* tanda panah :not(:empty)*/
	position:relative;
	height:0px;
	top:28px;
}

#flowchart td div div.kanan{ /* tanda panah :not(:empty)*/
	position:relative;
	height:0px;
	left:68px;
	top:-3px
}

#flowchart td div div.atas{ /* tanda panah :not(:empty)*/
	position:relative;
	height:0px;
	top: -21px;
}

#flowchart td div div.kiri{ /* tanda panah :not(:empty)*/
	position:relative;
	height:0px;
	left:-64px;
}


</style>
<html>
<head>
	<title>.: <?=$_SESSION["application"]?> :.</title>
    <link href="text.css.php" rel="stylesheet" type="text/css">
	<link href="menu.css.php" rel="stylesheet" type="text/css">
</head>
<script language='javascript' src="js/ajax.js.php"></script>
<script language='javascript' src="js/dd_menu.js.php"></script>
<script language='javascript' src='js/calendar.js.php'></script>
<script language='javascript' src='js/openwindow.js.php'></script>
<script language="javascript">

function setImgPosition(pType){
	if(pType == "" || pType == null)return false;
	var arrayClass = document.getElementsByClassName(pType);
	length = arrayClass.length
	for(i=0; i < length; i++){
		parentDiv = arrayClass[i].parentNode
		lObj = document.createElement('img');
		lObj.setAttribute("style","height:20px");
		lObj.setAttribute("src","images/panah"+pType+".png");
		arrayClass[i].appendChild(lObj)
	}
}
function fLoad(){
	setImgPosition('bawah')
	setImgPosition('atas')
	setImgPosition('kanan')
	setImgPosition('kiri')
}

</script>
<body onLoad="fLoad()" bgcolor="#f3f3f3">
<?
include_once("includes/menu.inc.php");
?>
<form name="form1" style="margin:0 0 0 0">
<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr background="images/submenu_background.jpg" height="37">
		<td width="20"></td>
		<td class="selectMenu" colspan="2"></td>
	</tr>
	<tr>
		<td width="20"></td>
		<td valign="top" align="center">
			<table id="flowchart">
            	<tr>
                <td <?=(check_right('111216',false)?"onClick=\"location='list.php?module=20180200000002&flag=true'\"":"class='no-access'")?>>
                    	<div>
                        <div class="kanan"></div>
							TARGET
						</div>
					</td>
                	<td <?=(check_right('101010',false)?"onClick=\"location='list.php?module=20170800000001'\"":"class='no-access'")?> >
                    	<div><!--bercabang, set sendiri left nya -->
                        	<div class="bawah" ></div>
                        	<div class="bawah" ></div>
							CUSTOMER
						</div>
					</td>
                	<td></td>
                	<td></td>
               </tr>
            	<tr>
                	<td></td>
                	<td <?=(check_right('11101011',false)?"onClick=\"location='list.php?module=20170900000052'\"":"class='no-access'")?>>
                    	<div>
                        	<div class="bawah"></div>
							PERMOHONAN KREDIT
						</div>
					</td>
                    <td>
                    	<div>
                            PEMBAYARAN
                        </div>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td <?=(check_right('12101011',false)?"onClick=\"location='list.php?module=20171000000007&flag=true'\"":"class='no-access'")?>>DEALER</td>
                            	<td <?=(check_right('12101310',false)?"onClick=\"location='list.php?module=20211100000018&flag=true'\"":"class='no-access'")?>>DATUN</td>
                            </tr>
                       </table>
                    </td>
                </tr>
            	<tr>
                	<td <?=(check_right('111214',false)?"onClick=\"location='list.php?module=20170900000073&flag=true'\"":"class='no-access'")?>>
                    	<div>
                        	<div class="kanan" style="top:-10px"></div>
                            REVISI
                        </div>
					</td>
                	<td <?=(check_right('111214',false)?"onClick=\"location='list.php?module=20170900000073&flag=true'\"":"class='no-access'")?> rowspan="2">

					<div>
                        	<div class="kanan"></div>
                        	<div class="kiri" style="top:-20px"></div>
                        	<div class="kiri" style="top:28px"></div>
                        	<div class="bawah" style="top:73px"></div>
                            APPROVAL
                        </div>
					</td>
          
                	<td <?=(check_right('12131511',false)?"onClick=\"location='list.php?module=20171000000015&flag=true'\"":"class='no-access'")?> rowspan="4" colspan="1">
                    	<div>
                        	<div class="atas" style="top:-135px"></div>
                            <div class="kanan" style="top:-90px"></div>
                            <div class="kanan" style="top:-25px"></div>                            
                            <div class="kanan" style="top:45px"></div>    
                            <div class="kanan" style="top:95px"></div>                                 
                            BATCH AR
                        </div>
					</td>
                    <td>
                    	<div>
                            PENGELUARAN
                        </div>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td <?=(check_right('121121',false)?"onClick=\"location='list.php?module=20180200000034&flag=true'\"":"class='no-access'")?>>FUNDING</td>
                            	<td <?=(check_right('121111',false)?"onClick=\"location='list.php?module=20171100000014&flag=true'\"":"class='no-access'")?>>OPEX</td>
                            	<td <?=(check_right('12101011',false)?"onClick=\"location='list.php?module=20171000000007&flag=true'\"":"class='no-access'")?>>TAC</td>
                            </tr>
                       </table>
                    </td>                   
                </tr>
            	<tr>
                	<td <?=(check_right('111214',false)?"onClick=\"location='list.php?module=20170900000073&flag=true'\"":"class='no-access'")?>>
                    	<div>
							REJECT
						</div>
					</td>
                    <td>
                    	<div>
                            PENERIMAAN
                        </div>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td <?=(check_right('12101112',false)?"onClick=\"location='list.php?module=20170800000056&flag=true'\"":"class='no-access'")?>>ANGSURAN</td>                            	<td <?=(check_right('12101112',false)?"onClick=\"location='list.php?module=20170800000056&flag=true'\"":"class='no-access'")?>>DENDA</td>
                            	<td <?=(check_right('12101111',false)?"onClick=\"location='list.php?module=20170800000054&flag=true'\"":"class='no-access'")?>>PELUNASAN NORMAL</td>
                            	<td <?=(check_right('121015',false)?"onClick=\"location='list.php?module=20171200000023&flag=true'\"":"class='no-access'")?>>PELUNASAN LAIN</td>
                                
                            </tr>
                       </table>
                    </td>

                    
                </tr>
            	<tr>
                    <td></td>
                	<td <?=(check_right('111111',false)?"onClick=\"location='list.php?module=20170800000032&flag=true'\"":"class='no-access'")?> rowspan="2">
                    	<div>
                            KONTRAK
                        </div>
					</td>
                    <td>
                    	<div>
                            BPKB
                        </div>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td <?=(check_right('121012',false)?"onClick=\"location='list.php?module=20211100000012&flag=true'\"":"class='no-access'")?>>TERIMA & SERAHKAN</td>
                            	<td <?=(check_right('121014',false)?"onClick=\"location='list.php?module=20171000000046&flag=true'\"":"class='no-access'")?>>MUTASI</td>
                            	<td <?=(check_right('12101114',false)?"onClick=\"location='list.php?module=20211100000021&flag=true'\"":"class='no-access'")?>>BIAYA ADMIN PENITIPAN</td>
                                
                            </tr>
                       </table>
                    </td>                    

                </tr>
            	<tr>
                    <td></td>
                    <td>
                    	<div>
                            TUNGGAKAN
                        </div>
                    	<table cellpadding="0" cellspacing="0">
                        	<tr>
                            	<td <?=(check_right('111316',false)?"onClick=\"location='list.php?module=20171200000036&flag=true'\"":"class='no-access'")?>>TARIK</td>
                            	<td <?=(check_right('12101115',false)?"onClick=\"location='list.php?module=20211200000003&flag=true'\"":"class='no-access'")?>>TEBUS</td>
                            	<td <?=(check_right('12101111',false)?"onClick=\"location='list.php?module=20170800000056&flag=true'\"":"class='no-access'")?>>BLOKIR</td>
                            </tr>
                       </table>
                    </td>
                 </tr>                

            </table>
		</td>
		<td width="20"></td>
	</tr>
</table>
</form>
</body>
</html>