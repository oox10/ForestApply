<!DOCTYPE html>
<html lang="zh-Hant-TW">
	<head>
		<title>林務局自然保護區域進入申請系統</title>
		<meta charset="UTF-8" />
		<meta name="author" content="" />
		<meta http-equiv="X-UA-Compatible" content="IE=100" /> <!-- IE mode -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<style>
			*{-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box}
			html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{border:0;font-size:100%;font:inherit;vertical-align:baseline;margin:0;padding:0}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:none}table{border-collapse:collapse;border-spacing:0}
		</style>	
		<style>
			html,body{font-family:"新細明體","Microsoft JhengHei";}html{overflow-x:hidden}
			div.page-container{
			  padding:3% 8%;
			  size:portrait;/*ㄧ頁大小，以紙張短邊為寬，沒有用。*/ 
			}
			
			header{
			  font-size:2em;
              font-weight:bold;	
              text-align:center;	
              position:relative;			  
			}
			  div.title_mark{
				position:absolute;
                top:0;
                left:10px;  				
			  }
			table.check_table{
			  margin:20px 0;	
			  width:100%;
			}
			table.check_table td{border:1px #000000 solid; padding:8px 5px ;}
			table.check_table td:nth-child(1){width:25%;font-weight:bold;}
		    td.:nth-child(1){width:25%;font-weight:bold;}
			td.edit_block{height:70px;}
			
		    /* license preview */
			.license_page{
			  font-size:1.1em;
			  color:#333333;
			}
			
			.license_page > h1{
			  font-size:1.5em;
			  font-weight:bold;
			  text-align:center;
			}
			  div.note{
				text-align:center; 	
				margin:5px 0;
				font-size:0.8em;
				line-height:1.5em;
			  }
			
			.license_page > h2{
			  padding-left:6px;
			  font-weight:bold;
			  line-height:1.5em; 	  
			}
			
			.license_page > table{
			  width:100%;
			  border-collapse: collapse; 
			}
			
			.license_page > table th,.license_page > table td{
			  padding:10px 5px;
			  border:1px #434343 solid;	
			  text-align:left;
			}
			
			.license_page > table.application th{
			  font-weight:bold;	
			  width:25%;
			}
			.license_page > table.application td{
			  width:25%;	
			}
			
			.license_page > table.application td.handwriting{
			  font-size:0.8em;	
			  height:6em;
			  vertical-align:bottom; 
			  text-align:center;
			  color:#666666;
			}
			
			.license_page > hr{
			  margin:35px 0;
			  border:1px #666666 dashed;	  
			}
			
			.license_page > hr.break{
			  page-break-before:always	
			}
			
			.license_page > table.application tbody.additional_fields th.field_set{
			  text-align:center;	
			}
			.additional_fields td{
			  font-size:0.9em;
			  line-height:1.3em;	  
			}
			
			table.joing_member{
			  font-size:0.8em;	
			} 
			table.joing_member th{
			  font-weight:bold;	
			}  
			  tr.member_detail td:nth-child(1){ width:20px; text-align:center; }
			  tr.member_detail td:nth-child(2){ width:50px; text-align:center; }
			  tr.member_detail td:nth-child(3){ width:190px; }
			  tr.member_detail td:nth-child(4){ min-width:200px; }
			  tr.member_detail td:nth-child(5){ width:120px; }
				
				td.member_info > div{
				  display:flex;
				  align-items:center;
				  margin-bottom:3px; 		  
				}
				td.member_info label{font-weight:bold;}
				td.member_info label:after{
				  display:inline-block;
				  content:'：';
				  padding:0 2px;
				}
				td.member_info span{padding-right:10px;}
				
			th.afield{ width:130px; font-weight:bold;}
			td.acheck{ width:100px ;}
		  
		  
			div.regulation{
			  padding:5px;
			  border-top:1px #666666 solid; 	  
			}
			
			div.regulation > p{
			  margin-left:2em;	
			  text-indent:-2em;
			  margin-bottom:10px;
			  line-height:1.5em;	  
			}
			div.regulation > ul{
			  margin-left:2em;
			  margin-bottom:10px;	  
			}
		  
		  
			table.contect{
				
			}
			
			table.contect th{
			  font-weight:bold;
			  position:relative;
			}
			
			table.contect th:after{
			  content: '';
			  display: block;
			  position: absolute;
			  left: 0;
			  right: 0;
			  bottom: 1px;
			  height: 1px;
			  background-color: #cdcdcd;
			}
		  
			table.contect + h1{
			  margin:50px 0;	
			}		
		</style>	
		<style>		
			@media print {
				.page-container{
					page-break-after:always;
					page-break-inside : avoid; /*在列印中，page-container中不會換頁?*/
				}
				.table-unbreak-container td{ page-break-inside : avoid } /*在列印中，table.table-unbreak-container中 td 不會換頁*/
				.table-unbreak-container tr{ page-break-inside:avoid; page-break-after:auto }
                .page-break{
				  page-break-after: always;	
				}			    
			}
			@media screen{
				body{
					width:21cm;/*限制為A4寬*/
					margin:0 auto;
				}
				.page-container{
					width:90%;
					margin:20px auto;
				}
				.table-unbreak-container tr{ page-break-inside:avoid; page-break-after:auto }
			}
			@page{
				size:portrait;/*ㄧ頁大小，以紙張短邊為寬，沒有用。*/
				margin:10%;
			}
		</style>
	</head>
	<body>
		<div class="page-container">
			<header>
			  <div >林務局自然保護區域進入申請陳核單 </div>
			  <div class='title_mark'><img src='theme/image/mark_forest.png'></div>
			</header>
			<table class='check_table' border=1>
			    <tr><td >製表資訊</td><td>#{EXPORT_INFO}</td></tr>
				<tr><td >申請日期</td><td>#{BOOKED_DATE}</td></tr>
			    <tr><td >審查註記</td><td>#{REVIEW_NOTE}</td></tr>
			    <tr><td class='edit_block' colspan=2></td></tr> 
			</table> 
			<div >
			  #{PAGE_CONTENT}
			</div>
		</div>
	</body>
</html>